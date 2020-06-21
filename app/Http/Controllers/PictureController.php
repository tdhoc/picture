<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Validator;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\MessageBag;

use App\Http\Model\Users;
use App\Http\Model\PictureTemp;
use App\Http\Model\Category;
use App\Http\Model\Subcategory;
use App\Http\Model\Tag;
use App\Http\Model\PictureTag;
use App\Http\Model\MainColor;
use App\Http\Model\Picture;
use Croppa;
use File;
use FileUpload;
use DB;
use Illuminate\Support\Carbon;
use League\ColorExtractor\Palette;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;

class PictureController extends Controller
{

    /**
    * Convert to byte
    *
    * @return String
    */
    public function returnByte($byte){
        if ($byte >= 1073741824)
        {
            $byte = number_format($byte / 1073741824, 2) . ' GB';
        }
        elseif ($byte >= 1048576)
        {
            $byte = number_format($byte / 1048576, 2) . ' MB';
        }
        elseif ($byte >= 1024)
        {
            $byte = number_format($byte / 1024, 2) . ' KB';
        }
        elseif ($byte > 1)
        {
            $byte = $byte . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $byte = $byte . ' byte';
        }
        else
        {
            $byte = '0 bytes';
        }
        return $byte;
    }
    /*public function postSubmit(Request $request){
        $user = Auth::user()['username'];
        return json_encode($user);
    }*/
    /**
    * Post submit
    *
    * @return json
    */
    public function postSubmit(Request $request){
        $picture_temp = PictureTemp::where('id', $request->picture)->first();
        if($picture_temp == null){
            return response() -> json([
                    'error' => true,
                    'message' => 'Something wrong'
                ], 200);
        }
        if (!file_exists(".".$picture_temp->url)) {
            $picture_temp->delete();
            return redirect('/picture/submit');
        }
        $subcategory_id = null;
        if(!empty($request->subcategory)){
            $sc = trim($request->subcategory, " ");
            $subcategory = Subcategory::where('category_id', $request->category)->where('name', $sc)->first();
            //create subcategory if not exist
            if(empty($subcategory)){
                $subcategory = new Subcategory;
                $subcategory->name = $sc;
                $subcategory->category_id = $request->category;
                $subcategory->save();
                $subcategory_id = $subcategory->id;
            }else{
                $subcategory_id = $subcategory->id;
            }
        }else{
            return  response() -> json([
                    'error' => true,
                    'message' => 'Subcategory cannot be null'
                ], 200);
        }
        $statement = DB::select("SHOW TABLE STATUS LIKE 'picture'");
        $picture_id = $statement[0]->Auto_increment;
        $path_parts = pathinfo($picture_temp->url);
        //save picture to db
        $picture = new Picture;
        $picture->link = '/picture/'.$picture_id.".".$path_parts['extension'];
        if($request->is_author == 'true'){
            $picture->author = Auth::user()['username'];
        }else{
            $auth = trim($request->author, " ");
            if(!empty($auth)){
                $picture->author = $auth;
            }
        }
        $caption =  trim($request->description, " ");
        if(!empty($caption)){
            $picture->description = $caption;
        }
        $resolution = getimagesize(".".$picture_temp->url);
        $picture->resolution = $resolution[0].'x'.$resolution[1];
        $size = filesize(".".$picture_temp->url);
        $picture->size = $this->returnByte($size);
        $picture->view = 0;
        $picture->download = 0;
        $picture->users_id = $picture_temp->users_id;
        $picture->subcategory_id = $subcategory_id;
        //set thumbnail
        $picture->thumb = '/picture/'.$picture_id.'-350x219.'.$path_parts['extension'];
        $picture->save();
        
        //tag
        if($request->tag != null){
            $tagarr = explode(",",$request->tag);
            foreach ($tagarr as $tag){
                $tag = trim($tag, " ");
                if(!empty($tag)){
                    $picture_tag = new PictureTag;
                    $picture_tag->picture_id = $picture->id;
                    $check_tag = Tag::where('name', $tag)->first();
                    //create tag if not exist
                    if(empty($check_tag)){
                        $check_tag = new Tag;
                        $check_tag->name = $tag;
                        $check_tag->save();
                        $picture_tag->tag_id = $check_tag->id;
                        $picture_tag->save();
                    }else{
                        $picture_tag->tag_id = $check_tag->id;
                        $picture_tag->save();
                    }
                }
            }
        }
        //color
        $palette = Palette::fromFilename('.'.$picture_temp->url);
        $extractor = new ColorExtractor($palette);
        $colors = $extractor->extract(4);
        foreach ($colors as $color) {
            $maincolor = new MainColor;
            $maincolor->picture_id = $picture->id;
            $maincolor->color = Color::fromIntToHex($color);
            $maincolor->save();
        }
        // check path
        $path = public_path('/picture/');
        if(!File::exists($path)) {//create if not exist
            File::makeDirectory($path);
        };
        //create file with name = id in folder picture_temp
        copy(".".$picture_temp->url, ".".'/picture_temp/'.$picture_id.'.'.$path_parts['extension']);
        Croppa::delete($picture_temp->url); // delete temp file and thumbnail(s)
        $picture_temp->delete(); // delete db record
        $temp_name = $picture_id.'-350x219.'.$path_parts['extension'];// thumb image name
        Croppa::render('/picture_temp/'.$temp_name);//create thumb image
        copy('./picture_temp/'.$temp_name, './picture/'.$temp_name);// copy thumb image to folder picture
        copy('./picture_temp/'.$picture_id.'.'.$path_parts['extension'], ".".$picture->link);// copy picture to folder picture
        Croppa::delete('/picture_temp/'.$picture_id.'.'.$path_parts['extension']);//delete picture and thumbnail in picture_temp
        return  response() -> json([
                    'error' => false,
                ], 200);
    }
    /**
    * Get tag suggestion
    *
    * @return json
    */
    public function getTagSuggestion(Request $request){
        $requestlist_raw = explode(",",$request->text);
        $requestlist = [];
        foreach ($requestlist_raw as $rq) {
            array_push($requestlist, trim($rq," "));
        }
        $tagtext = end($requestlist);
        if(count($requestlist_raw) == 1){
            $valuestring = "";
        }else{
            array_pop($requestlist);
            $valuestring = implode(',', (array)$requestlist);
            $valuestring = $valuestring.',';
        }
        $returndata = [];
        $tagarr = Tag::where('name', 'like',$tagtext.'%')->get();
        foreach ($tagarr as $tag){
            $array = [
                "value" => $valuestring."".$tag->name,
                "label" => $tag->name,
            ];
            array_push($returndata,$array);
        }
        return json_encode($returndata);

    }

    /**
    * Get subcategory suggestion
    *
    * @return json
    */
    public function getSubcategorySuggestion(Request $request){
        $returndata = [];
        if($request->category_id != null){
            $subcategoryarr = Subcategory::where('category_id',$request->category_id)->where('name', 'like', trim($request->text," ").'%')->get();
            foreach ($subcategoryarr as $subcategory){
                $array = [
                    "value" => $subcategory->name,
                    "label" => $subcategory->name,
                ];
                array_push($returndata, $array);
            }
        }
        return json_encode($returndata);

    }


    /**
    * Get submit page
    *
    * @return submit view
    */
    public function getSubmit(){
        if(Auth::check()){
            $picture_temp = PictureTemp::where('users_id', Auth::id())->first();
            if($picture_temp == null){
                return view('picture.upload');
            }else{
                if (!file_exists(".".$picture_temp->url)) {
                    $picture_temp->delete();
                    return redirect('/picture/submit');
                }
                $category = Category::all();
                $resolution = getimagesize(".".$picture_temp->url);
                $size = filesize(".".$picture_temp->url);
                $size = $this->returnByte($size);
                //return view('picture.upload');
                return view('picture.submit')->with('picture_temp' , $picture_temp)->with("category", $category)->with("resolution", $resolution)->with("size", $size);
            }
        }else{
            return redirect('/users/login');
        }
    }

    public $folder = '/picture_temp/'; // add slashes for better url handling
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        // get all pictures
        $pictures = PictureTemp::all()->where('users_id', Auth::id());
        
        // add properties to pictures
        $pictures->map(function ($picture) {
            $picture['size'] = File::size(public_path($picture['url']));
            $picture['thumbnailUrl'] = Croppa::url($picture['url'], 80, 80, ['resize']);
            $picture['deleteType'] = 'DELETE';
            $picture['deleteUrl'] = route('pictures.destroy', $picture->id);
            return $picture;
        });
        // show all pictures
        return response()->json(['files' => $pictures]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // create upload path if it does not exist
        $path = public_path($this->folder);
        if(!File::exists($path)) {
            File::makeDirectory($path);
        };

        // Simple validation (max file size 100MB and only two allowed mime types)
        $validator = new FileUpload\Validator\Simple('100M', ['image/png', 'image/jpg', 'image/jpeg']);

        // Simple path resolver, where uploads will be put
        $pathresolver = new FileUpload\PathResolver\Simple($path);

        // The machine's filesystem
        $filesystem = new FileUpload\FileSystem\Simple();

        // FileUploader itself
        $fileupload = new FileUpload\FileUpload($_FILES['files'], $_SERVER);
        $slugGenerator = new FileUpload\FileNameGenerator\Slug();

        // Adding it all together. Note that you can use multiple validators or none at all
        $fileupload->setPathResolver($pathresolver);
        $fileupload->setFileSystem($filesystem);
        $fileupload->addValidator($validator);
        $fileupload->setFileNameGenerator($slugGenerator);
        
        // Doing the deed
        list($files, $headers) = $fileupload->processAll();

        // Outputting it, for example like this
        foreach($headers as $header => $value) {
            header($header . ': ' . $value);
        }

        foreach($files as $file){
            //Remember to check if the upload was completed
            if ($file->completed) {

                // set some data
                $filename = $file->getFilename();
                $url = $this->folder . $filename;
                
                // save data
                $picture = PictureTemp::create([
                    'name' => $filename,
                    'url' => $this->folder . $filename,
                    'users_id' => Auth::id(),
                ]);
                
                // prepare response
                $data[] = [
                    'size' => $file->size,
                    'name' => $filename,
                    'url' => $url,
                    'thumbnailUrl' => Croppa::url($url, 80, 80, ['resize']),
                    'deleteType' => 'DELETE',
                    'deleteUrl' => route('pictures.destroy', $picture->id),
                ];
                
                // output uploaded file response
                return response()->json(['files' => $data]);
            }
        }
        // errors, no uploaded file
        return response()->json(['files' => $files]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Picture  $picture
     * @return \Illuminate\Http\Response
     */
    public function destroy(PictureTemp $picture)
    {
        Croppa::delete($picture->url); // delete file and thumbnail(s)
        $picture->delete(); // delete db record
        return response()->json([$picture->url]);
    }
    public function delete(Request $request)
    {
        $picture = PictureTemp::where('id', $request->id)->get();
        Croppa::delete($picture->url); // delete file and thumbnail(s)
        $picture->delete(); // delete db record
        return response()->json([
                    'complete' => true,
                ], 200);
    }
    public function editTags(Request $request){
        $picture_id = $request->id;
        $taglist = PictureTag::where('picture_id', $picture_id)->get()->toArray();
        $returndata = "";
        foreach($taglist as $key => $tag){
            $tagtemp = Tag::where('id', $tag['tag_id'])->first()->toArray();
            $returndata .= '<div class="tag-element" id="tag_' . $tagtemp["id"] . '">
				' . $tagtemp["name"] . '
				<span style="color:red;" tag_id="' . $tagtemp["id"] . '" class="remove-tag"><i class="el el-remove"></i></span>
            </div>';
        }
        return $returndata;
    }

    public function completeTags(Request $request){
        $picture_id = $request->id;
        $taglist = PictureTag::where('picture_id', $picture_id)->get()->toArray();
        $returndata = "";
        foreach($taglist as $key => $tag){
            $tagtemp = Tag::where('id', $tag['tag_id'])->first()->toArray();

            $returndata .= '<div class="tag-element">
                    <a href="/by_tag/' . $tagtemp["id"] . '">' . $tagtemp["name"] . '</a>
                </div>';
        }
        return $returndata;
    }

    public function removeTags(Request $request){
        $picture_id = $request->picture_id;
        $tag_id = $request->tag_id;
        $picture = Picture::where('id', $picture_id)->first();
        if($picture == null){
            return response() -> json([
                'error' => true,
                'message' => "Picture doesn't exist"
            ], 200);
        }elseif(Auth::user()->type != "admin" && Auth::user()->id != $picture->users_id){
            return response() -> json([
                'error' => true,
            ], 200);
        }
        $check = Tag::where('id', $tag_id)->first();
        if($check == null){
            return response() -> json([
                    'error' => true,
                    'message' => "The tag you are trying to remove doesn't exist!"
                ], 200);
        }else{
            $check = PictureTag::where('picture_id', $picture_id)->where('tag_id', $tag_id)->first();
            if($check == null){
                return response() -> json([
                        'error' => true,
                        'message' => "The tag you are trying to remove isn't associated with this Picture!"
                    ], 200);
            }else{
                $check->delete();
                return response() -> json([
                        'error' => false,
                    ], 200);
            }
        }
        
    }

    public function addTags(Request $request){
        $picture = Picture::where('id', $request->picture_id)->first();
        if($picture == null){
            return response() -> json([
                    'error' => true,
                    'message' => "The picture doesn't exist!"
                ], 200);
        }else{
            if($request->tags != null){
                $tagarr = explode(",",$request->tags);
                foreach ($tagarr as $tag){
                    $tag = trim($tag, " ");
                    if(!empty($tag)){
                        $picture_tag = new PictureTag;
                        $picture_tag->picture_id = $picture->id;
                        $check_tag = Tag::where('name', $tag)->first();
                        //create tag if not exist
                        if(empty($check_tag)){
                            $check_tag = new Tag;
                            $check_tag->name = $tag;
                            $check_tag->save();
                            $picture_tag->tag_id = $check_tag->id;
                            $picture_tag->save();
                        }else{
                            $picture_tag_check = PictureTag::where('picture_id', $picture->id)->where('tag_id', $check_tag->id)->first();
                            if($picture_tag_check == null){
                                $picture_tag->tag_id = $check_tag->id;
                                $picture_tag->save();
                            }
                        }
                    }
                }
                return response() -> json([
                    'error' => false,
                ], 200);
            }else{
                return response() -> json([
                    'error' => true,
                    'message' => "Tag list can't be empty!"
                ], 200);
            }
        }
    }

    public function getShow(Request $request){
        $id = $request->id;
        $picture = Picture::where('id', $id)->first();
        if($picture == null){
            return abort(404);
        }
        $picture->view = $picture->view + 1;
        $picture->save();
        $picture = Picture::with('subcategory','tag','color')->where('id', $id)->first()->toArray();
        $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
        $picture['uploader'] = $uploader;
        $picture['uploader']['numberofpicture'] = Picture::where('users_id', $picture['uploader']['id'])->count();
        $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
        unset($picture['subcategory_id']);
        unset($picture['users_id']);
        $picture['created_at'] = date("d/m/Y", strtotime($picture['created_at']));
        $resolution = explode('x', $picture['resolution']);
        $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
        $picture['type'] =  strtoupper(pathinfo($picture["link"])['extension']);
        $picture['tagstring'] = '';
        foreach($picture['tag'] as $key => $tag){
            unset($picture['tag'][$key]['picture_id']);
            $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
            $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
        }
        $picture['tagstring'] = trim($picture['tagstring'], ' ');
        return view('picture.show', array('picture' => $picture));
    }

    public function getPrevNextNav(Request $request){
        $id = $request->query('id');
        $dir = $request->query('dir');
        $scope = $request->query('scope');
        $scope_id = $request->query('scope_id');
        $picturelist = [];
        if ($scope == 'category') {
            $category = Category::where('id', $scope_id)->first()->toArray();
            if($category != null){
                //get picture list by category
                $picturewsubcategorylist = Picture::with(['subcategory'])->orderBy('id', 'asc')->get()->toArray();
                foreach ($picturewsubcategorylist as $picture) {
                    if($picture['subcategory']['category_id'] == $category['id']){
                        array_push($picturelist, $picture['id']);
                    }
                }
            }
        }elseif ($scope == 'subcategory') {
            $subcategory = Subcategory::where('id', $scope_id)->first()->toArray();
            if($subcategory != null){
                //get picture list by category
                $picturewsubcategorylist = Picture::with(['subcategory'])->orderBy('id', 'asc')->get()->toArray();
                foreach ($picturewsubcategorylist as $picture) {
                    if($picture['subcategory']['id'] == $subcategory['id']){
                        array_push($picturelist, $picture['id']);
                    }
                }
            }
        }elseif ($scope == 'user'){
            $user = Users::where('id', $scope_id)->first()->toArray();
            if($user != null){
                //get picture list by category
                $picturewsubcategorylist = Picture::with(['subcategory'])->orderBy('id', 'asc')->get()->toArray();
                foreach ($picturewsubcategorylist as $picture) {
                    if($picture['users_id'] == $user['id']){
                        array_push($picturelist, $picture['id']);
                    }
                }
            }
        }else{
            $picturewsubcategorylist = Picture::with(['subcategory'])->orderBy('id', 'asc')->get()->toArray();
                foreach ($picturewsubcategorylist as $picture) {
                    array_push($picturelist, $picture['id']);
                }
        }
        if(count($picturelist) <= 1){
            return back();
        }
        foreach ($picturelist as $key => $value) {
            if($value == $id){
                if($dir == 'next'){
                    if($key < (count($picturelist) - 1)){
                        return redirect('/picture/show/' . $picturelist[$key+1]);
                    }else{
                        return redirect('/picture/show/' . $picturelist[0]);
                    }
                }elseif ($dir == 'prev') {
                    if($key > 0){
                        return redirect('/picture/show/' . $picturelist[$key-1]);
                    }else{
                        return redirect('/picture/show/' . $picturelist[count($picturelist) - 1]);
                    }
                }else{
                    return back();
                }
            }
        }
    }

    public function getCrop(Request $request){
        $id = $request->query('id');
        $id= trim($id, ' ');
        if($id != null){
            $picture = Picture::where('id', $id)->first()->toArray();
            $name = $picture['id'] . '.' . pathinfo($picture['link'])['extension'];
            return view('picture.crop', array('picture' => $picture, 'name' => $name));
        }
        return redirect('/');
    }


    public function getDownloadLink(Request $request){
        $id = $request->id;
        $picture = Picture::where('id', $id)->first();
        if($picture != null){
            $picture->download = $picture->download + 1;
            $picture->save();
            $file = '.'.$picture->link;
            if(file_exists($file)){
                $name = basename($file);
                return response()->download($file, $name);
            }
        }
    }

    public function deletePicture(Request $request){
        $id = $request->id;
        $checkpicture = Picture::where('id', $id)->first();
        if($checkpicture == null){
            return response() -> json([
                    'error' => true,
                    'message' => "Something wrong1"
                ], 200);
        }
        if(Auth::user()->id != $checkpicture->id && Auth::user()->type != 'admin'){
                return response() -> json([
                    'error' => true,
                    'message' => "Something wrong"
                ], 200);
        }else{
            unlink('.'.$checkpicture->link);
            unlink('.'.$checkpicture->thumb);
            $checkpicture->delete();
            return response() -> json([
                    'error' => false
                ], 200);
        }
    }
}