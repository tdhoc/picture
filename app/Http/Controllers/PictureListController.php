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
use Cookie;
use DB;

class PictureListController extends Controller
{

    /**
    * Sort
    *
    * @return json with cookie
    */
    public function sort(Request $request){
       /* if($request->view == "paged"){

        }elseif ($request->view == "simple") {

        }elseif ($request->view == "slideshow") {

        }elseif ($request->view == "infinite") {

        }*/
        if($request->min_resolution == "0x0"){
            $resolution_equals = ">=";
        }else{
            $resolution_equals = $request->resolution_equals;
        }
        return response()->json(['error' => 'false'])->withCookie(cookie()->forever('view', $request->view))->withCookie(cookie()->forever('min_resolution', $request->min_resolution))->withCookie(cookie()->forever('resolution_equals', $resolution_equals))->withCookie(cookie()->forever('sort', $request->sort))->withCookie(cookie()->forever('elementsperpage', $request->elementsperpage));

    }

    public function getSubmissionInfos(Request $request){
        $id = $request->picture_id;
        $picture = Picture::where('id', $id)->first()->toArray();
        $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
        $count = Picture::where('users_id', $uploader['id'])->count();
        $subcategory = Subcategory::where('id', $picture['subcategory_id'])->first()->toArray();
        $colors = MainColor::where('picture_id', $picture['id'])->get()->toArray();
        $tags = PictureTag::where('picture_id', $picture['id'])->get()->toArray();
        $returndata = '<div class="submitter-info"><div><b>Shared By:</b><div class="submitter-avatar"><img alt="'. $uploader['username'] . ' - Avatar" src="'.$uploader['avatar'].'"></div></div><div class="user-dropdown"><a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#"><i class="el el-user"></i> ' . $uploader['username'] . ' <span class="caret"></span></a><ul class="dropdown-menu"><li><a href="/users/profile/'. $uploader['id'] .'"><i class="el el-eye-open"></i> Profile</a></li><li><a href="/users/profile/'. $uploader['id'] .'">' . $count . ' Pictures</a></li></ul></div></div><table class="table table-striped table-condensed table-user-info"><tbody><tr><td><span class="nav">&nbsp;&nbsp;ID</span></td><td><span>' . $picture["id"] . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Views</span></td><td><span>' . $picture["view"] . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Downloads</span></td><td><span>' . $picture["download"] . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Resolution</span></td><td><span><a class="resolution-link" href="/by_resolution/' . $picture["resolution"] . '">' . $picture["resolution"] . ' <i class="el el-link"></i></a></span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Size / Type</span></td><td><span>' . $picture["size"] . ' / '. strtoupper(pathinfo($picture["link"])['extension']) . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Date Added</span></td><td><span>' . date("d/m/Y", strtotime($picture['created_at'])) . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Colors</span></td><td class="colors-container"><span>';
        $count = 1;
        foreach($colors as $key => $color){
            if($count <= 4){
                $returndata .= '<a class="color-infos color-infos' . $count . '" style="background-color:' . $color['color'] . '" href="/by_color/' . trim($color['color'],'#') . '"></a>';
                $count++;
            }
        }
        $returndata .='</span></td></tr><tr><td colspan="2"><div class="slideTools"><span title="Download Picture"! class="btn btn-primary btn-custom download-button align-top" data-id="' . $picture["id"] . '"><i class="el el-download-alt"></i> Download</span></div></td></tr></tbody></table>';
        return $returndata;
    }

    private function getCookie(Request $request){
        $view = $request->cookie('view');
        if($view == null){
            $view = 'paged';
        }
        $min_resolution = $request->cookie('min_resolution');
        if($min_resolution == null){
            $min_resolution = '0x0';
        }
        $resolution_min = explode("x", $min_resolution);
        $resolution_equals = $request->cookie('resolution_equals');
        if($resolution_equals == null){
            $resolution_equals = ">=";
        }
        $sort = $request->cookie('sort');
        if($sort == null || $sort == "newest"){
            $sort = "created_at";
        }
        $elementsperpage = $request->cookie('elementsperpage');
        if($elementsperpage == null){
            $elementsperpage = 30;
        }
        $cookie = [
            'view' => $view,
            'min_resolution' => $min_resolution,
            'resolution_equals' => $resolution_equals,
            'sort' => $sort,
            'elementsperpage' => $elementsperpage
        ];
        return $cookie;
    }

    public function getInfiniteData(Request $request){
        $cookie = $this->getCookie($request);
        $view = $cookie['view'];
        $min_resolution = $cookie['min_resolution'];
        $resolution_equals = $cookie['resolution_equals'];
        $sort = $cookie['sort'];
        $elementsperpage = $cookie['elementsperpage'];
        if($request->by == "by_category"){
            $picturelist = $this->getByCategoryPictureList($cookie, $request->id);
            $maxpage = CEIL(count($picturelist)/$elementsperpage);
            $page = $request->next;
            if ($maxpage == 0) {
                $maxpage = 1;
            }
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_category/' . $request->id);
            }elseif($page > $maxpage){
                return response()->json(['error' => "out of picture"]);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
            }
            return response()->json(['error' => 'false', 'picturelist' => $returndata, 'page' => $page]);
        }elseif($request->by == "by_subcategory"){
            $picturelist = $this->getBySubcategoryPictureList($cookie, $request->id);
            $maxpage = CEIL(count($picturelist)/$elementsperpage);
            $page = $request->next;
            if ($maxpage == 0) {
                $maxpage = 1;
            }
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_subcategory/' . $request->id);
            }elseif($page > $maxpage){
                return response()->json(['error' => "out of picture"]);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
            }
            return response()->json(['error' => 'false', 'picturelist' => $returndata, 'page' => $page]);
        }elseif($request->by == "by_tag"){
            $picturelist = $this->getByTagPictureList($cookie, $request->id);
            $maxpage = CEIL(count($picturelist)/$elementsperpage);
            $page = $request->next;
            if ($maxpage == 0) {
                $maxpage = 1;
            }
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_tag/' . $request->id);
            }elseif($page > $maxpage){
                return response()->json(['error' => "out of picture"]);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
            }
            return response()->json(['error' => 'false', 'picturelist' => $returndata, 'page' => $page]);
        }elseif($request->by == "search"){
            $picturelist = $this->getByTagPictureList($cookie, $request->id);
            $maxpage = CEIL(count($picturelist)/$elementsperpage);
            $page = $request->next;
            if ($maxpage == 0) {
                $maxpage = 1;
            }
            if($page == null || $page < 1 || !isset($page)){
                return redirect('search/?keyword=' . $request->id. '&page=1');
            }elseif($page > $maxpage){
                return response()->json(['error' => "out of picture"]);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
            }
            return response()->json(['error' => 'false', 'picturelist' => $returndata, 'page' => $page]);
        }elseif($request->by == "by_color"){
            $picturelist = $this->getByColorPictureList($cookie, $request->id);
            $maxpage = CEIL(count($picturelist)/$elementsperpage);
            $page = $request->next;
            if ($maxpage == 0) {
                $maxpage = 1;
            }
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_tag/' . $request->id);
            }elseif($page > $maxpage){
                return response()->json(['error' => "out of picture"]);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
            }
            return response()->json(['error' => 'false', 'picturelist' => $returndata, 'page' => $page]);
        }elseif($request->by == "by_resolution"){
            $picturelist = $this->getByResolutionPictureList($cookie, $request->id);
            $maxpage = CEIL(count($picturelist)/$elementsperpage);
            $page = $request->next;
            if ($maxpage == 0) {
                $maxpage = 1;
            }
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_tag/' . $request->id);
            }elseif($page > $maxpage){
                return response()->json(['error' => "out of picture"]);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
            }
            return response()->json(['error' => 'false', 'picturelist' => $returndata, 'page' => $page]);
        }
    }

    private function getBySearchPictureList($cookie, $keyword){
        $resolution_min = explode("x", $cookie['min_resolution']);
        $picturelist = [];
        $picturewsubcategorylist = Picture::with(['subcategory', 'tag'])->orderBy($cookie['sort'], 'desc')->get()->toArray();
        foreach ($picturewsubcategorylist as $picture) {
            $resolution = explode('x', $picture['resolution']);
            if($cookie['resolution_equals'] == '>='){
                if($resolution[0] >= $resolution_min[0] && $resolution[1] >= $resolution_min[1]){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    if (strpos($picture['tagstring'], $keyword) !== false || strpos($picture['subcategory']['name'], $keyword) !== false || strpos($picture['subcategory']['category'], $keyword) !== false ) {
                        array_push($picturelist, $picture);
                    }
                }
            }elseif ( $cookie['resolution_equals'] == '=') {
                if($resolution[0] == $resolution_min[0] && $resolution[1] == $resolution_min[1]){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];

                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    if (strpos($picture['tagstring'], $keyword) !== false || strpos($picture['subcategory']['name'], $keyword) !== false || strpos($picture['subcategory']['category'], $keyword) !== false ) {
                        array_push($picturelist, $picture);
                    }
                }
            }
        }
        return $picturelist;
    }
        
    public function getBySearch(Request $request){
        $cookie = $this->getCookie($request);
        $view = $cookie['view'];
        $min_resolution = $cookie['min_resolution'];
        $resolution_equals = $cookie['resolution_equals'];
        $sort = $cookie['sort'];
        $elementsperpage = $cookie['elementsperpage'];
        $keyword = $request->query('keyword');
        $keyword = trim($keyword, ' ');
        if(!isset($keyword) || $keyword == null){
            return back();
        }
        $page = $request->query('page');
        $picturelist = $this->getBySearchPictureList($cookie, $keyword);
        $maxpage = CEIL(count($picturelist)/$elementsperpage);
        if ($maxpage == 0) {
            $maxpage = 1;
        }
        $categorylist = [];
        $userlist = [];
        $taglist = [];
        foreach($picturelist as $picturetemp){
            if(!array_key_exists($picturetemp['subcategory']['category_id'], $categorylist)){
                $categorylist[$picturetemp['subcategory']['category_id']]['numofpicture'] = 0;
                $categorylist[$picturetemp['subcategory']['category_id']]['id'] = $picturetemp['subcategory']['category_id'];
                $categorylist[$picturetemp['subcategory']['category_id']]['name'] = $picturetemp['subcategory']['category'];
            }
            $categorylist[$picturetemp['subcategory']['category_id']]['numofpicture'] += 1;

            foreach ($picturetemp['tag'] as $tagtemp) {
                if(!array_key_exists($tagtemp['tag_id'], $taglist)){
                    $taglist[$tagtemp['tag_id']]['numofpicture'] = 0;
                    $taglist[$tagtemp['tag_id']]['id'] = $tagtemp['tag_id'];
                    $taglist[$tagtemp['tag_id']]['name'] = $tagtemp['name'];
                }
                $taglist[$tagtemp['tag_id']]['numofpicture'] += 1;
            }

            if(!array_key_exists($picturetemp['uploader']['id'], $userlist)){
                $userlist[$picturetemp['uploader']['id']]['numofpicture'] = 0;
                $userlist[$picturetemp['uploader']['id']]['id'] = $picturetemp['uploader']['id'];
                $userlist[$picturetemp['uploader']['id']]['username'] = $picturetemp['uploader']['username'];
            }
            $userlist[$picturetemp['uploader']['id']]['numofpicture'] += 1;
        }

        usort($categorylist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($categorylist) > 10) {
            $categorylist = array_slice($categorylist, 0, 10);
        }
        usort($taglist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($taglist) > 10) {
            $taglist = array_slice($taglist, 0, 10);
        }
        usort($userlist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($userlist) > 10) {
            $userlist = array_slice($userlist, 0, 10);
        }

        $column1 = '<div class="panel-heading center">
                        Popular Category Of This Keyword
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($categorylist as $categorytemp) {
            $column1 .= '<li>
                                <a title="' . $categorytemp['name'] . ' HD Picture " href="/by_category/' .$categorytemp['id']. '/1">
                                    <span class="badge pull-right">' .$categorytemp['numofpicture']. '</span>' . $categorytemp['name'] . '
                                </a>
                            </li>';
        }
        $column1 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/category">
                            <i class="el el-th-list"></i> View All Category
                        </a>
                    </strong>
                </div>';

        $column2 = '<div class="panel-heading center">
                        Popular Tags Of This Keyword
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($taglist as $tagtemp) {
            $column2 .= '<li>
                                <a title="' . $tagtemp['name'] . ' HD Picture " href="/by_tag/' .$tagtemp['id']. '/1">
                                    <span class="badge pull-right">' .$tagtemp['numofpicture']. '</span>' . $tagtemp['name'] . '
                                </a>
                            </li>';
        }
        $column2 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/tags">
                            <i class="el el-th-list"></i> View All Tags
                        </a>
                    </strong>
                </div>';

        $column3 = '<div class="panel-heading center">
                        Most Uploader In This Keyword
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($userlist as $usertemp) {
            $column3 .= '<li>
                                <a title="' . $usertemp['username'] . ' HD Picture " href="/users/profile/' .$usertemp['id']. '">
                                    <span class="badge pull-right">' .$usertemp['numofpicture']. '</span>' . $usertemp['username'] . '
                                </a>
                            </li>';
        }
        $column3 .= '</ul></div>';
        $footer = ['column1' => $column1, 'column2' => $column2, 'column3' => $column3];
        $sortcurrent = "";
        if ($sort == "created_at") {
            $sort = "newest";
            $sortcurrent .= "Newest";
        }elseif ($sort == "view") {
            $sortcurrent .= "Most Viewed";
        }elseif ($sort == "download") {
            $sortcurrent .= "Most Download";
        }
        if ($min_resolution != "0x0") {
            $sortcurrent .= ", Resolution ".$resolution_equals." ".$min_resolution;
        }
        $sortdata = [
            'current' => $sortcurrent,
            'view' => '',
            'resolution' => $min_resolution,
            'resolution_equals' => $resolution_equals,
            'sort' => $sort,
            'elementsperpage' => $elementsperpage
        ];
        $pagetitle = count($picturelist) . ' ' . $keyword . ' Pictures';

        if($view == "paged"){
            $sortdata['view'] = "paged";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('search/?keyword=' . $keyword . '&page=1');
            }elseif($page > $maxpage){
                return redirect('search/?keyword=' . $keyword . '&page='.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/search/?keyword=' . $keyword . '&page='. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/search/?keyword=' . $keyword . '&page='.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/search/?keyword=' . $keyword . '&page='. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/search/?keyword=' . $keyword . '&page='. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/search/?keyword=' . $keyword . '&page='.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/search/?keyword=' . $keyword . '&page='.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/search/?keyword=' . $keyword . '&page='.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/search/?keyword=' . $keyword . '&page='.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_tag/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/search/?keyword=' . $keyword . '&page='.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/search/?keyword=' . $keyword . '&page='.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/search/?keyword=' . $keyword . '&page='.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_tag/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/search/?keyword=' . $keyword . '&page='.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/search/?keyword=' . $keyword . '&page='.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/search/?keyword=' . $keyword . '&page='.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$keyword.' HD Pictures | Images" href="/search/?keyword=' . $keyword . '&page=1">
                            <span>'. $keyword  .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.pagination', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'search', 'id' => $keyword, 'sort' => $sortdata, 'pagedata' => $pagedata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "infinite") {
            $sortdata['view'] = "infinite";
            if($page == null || $page != 1 || !isset($page)){
                return redirect('/search/?keyword=' . $keyword . '&page=1');
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }

                $peta = '<a class="breadcrumb-element" title="'.$keyword.' HD Pictures | Images" href="/search/?keyword=' . $keyword . '&page=1">
                            <span>'. $keyword  .'</span>
                        </a>';

                return view('list.infinite', array('picturelist' => $returndata, 'maxpage' => $maxpage,'by' => 'search', 'id' => $keyword, 'pagetitle' => $pagetitle, 'sort' => $sortdata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "slideshow") {
            $sortdata['view'] = "slideshow";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('/search/?keyword=' . $keyword . '&page=1');
            }elseif($page > $maxpage){
                return redirect('/search/?keyword=' . $keyword . '&page='.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/search/?keyword=' . $keyword . '&page='. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/search/?keyword=' . $keyword . '&page='.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/search/?keyword=' . $keyword . '&page='. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/search/?keyword=' . $keyword . '&page='. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/search/?keyword=' . $keyword . '&page='.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/search/?keyword=' . $keyword . '&page='.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/search/?keyword=' . $keyword . '&page='.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/search/?keyword=' . $keyword . '&page='.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_tag/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/search/?keyword=' . $keyword . '&page='.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/search/?keyword=' . $keyword . '&page='.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/search/?keyword=' . $keyword . '&page='.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_tag/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/search/?keyword=' . $keyword . '&page='.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/search/?keyword=' . $keyword . '&page='.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/search/?keyword=' . $keyword . '&page='.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$keyword.' HD Pictures | Images" href="/search/?keyword=' . $keyword . '&page=1">
                            <span>'. $keyword  .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.slideshow', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'search', 'id' => $keyword, 'sort' => $sortdata, 'pagedata' => $pagedata, 'next' => $page+1, 'null' => $peta, 'footer' => $footer));
            }
        }
    }

    private function getByTagPictureList($cookie, $id){
        $resolution_min = explode("x", $cookie['min_resolution']);
        $picturelist = [];
        $picturewsubcategorylist = Picture::with(['subcategory', 'tag'])->orderBy($cookie['sort'], 'desc')->get()->toArray();
        foreach ($picturewsubcategorylist as $picture) {
            $resolution = explode('x', $picture['resolution']);
            if($cookie['resolution_equals'] == '>='){
                if($resolution[0] >= $resolution_min[0] && $resolution[1] >= $resolution_min[1]){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    $tagcheck = 0;
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                        if($id == $picture['tag'][$key]['tag_id']){
                            $tagcheck = 1;
                        }
                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    if($tagcheck == 1){
                        array_push($picturelist, $picture);
                    }
                }
            }elseif ($cookie['resolution_equals'] == '=') {
                if($resolution[0] == $resolution_min[0] && $resolution[1] == $resolution_min[1]){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    $tagcheck = 0;
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                        if($id == $picture['tag'][$key]['tag_id']){
                            $tagcheck = 1;
                        }
                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    if($tagcheck == 1){
                        array_push($picturelist, $picture);
                    }
                }
            }
        }
        return $picturelist;
    }
    
    public function getByTag(Request $request){
        $cookie = $this->getCookie($request);
        $view = $cookie['view'];
        $min_resolution = $cookie['min_resolution'];
        $resolution_equals = $cookie['resolution_equals'];
        $sort = $cookie['sort'];
        $elementsperpage = $cookie['elementsperpage'];
        $tag = Tag::where('id', $request->id)->first();
        if($tag == null){
            return abort(404);
        }else{
            $tag->toArray();
        }
        $page = $request->page;
        $picturelist = $this->getByTagPictureList($cookie, $request->id);
        $maxpage = CEIL(count($picturelist)/$elementsperpage);
        if ($maxpage == 0) {
            $maxpage = 1;
        }

        $categorylist = [];
        $subcategorylist = [];
        $userlist = [];
        foreach($picturelist as $picturetemp){
            if(!array_key_exists($picturetemp['subcategory']['category_id'], $categorylist)){
                $categorylist[$picturetemp['subcategory']['category_id']]['numofpicture'] = 0;
                $categorylist[$picturetemp['subcategory']['category_id']]['id'] = $picturetemp['subcategory']['category_id'];
                $categorylist[$picturetemp['subcategory']['category_id']]['name'] = $picturetemp['subcategory']['category'];
            }
            $categorylist[$picturetemp['subcategory']['category_id']]['numofpicture'] += 1;

            if(!array_key_exists($picturetemp['subcategory']['id'], $subcategorylist)){
                $subcategorylist[$picturetemp['subcategory']['id']]['numofpicture'] = 0;
                $subcategorylist[$picturetemp['subcategory']['id']]['id'] = $picturetemp['subcategory']['id'];
                $subcategorylist[$picturetemp['subcategory']['id']]['name'] = $picturetemp['subcategory']['name'];
            }
            $subcategorylist[$picturetemp['subcategory']['id']]['numofpicture'] += 1;

            if(!array_key_exists($picturetemp['uploader']['id'], $userlist)){
                $userlist[$picturetemp['uploader']['id']]['numofpicture'] = 0;
                $userlist[$picturetemp['uploader']['id']]['id'] = $picturetemp['uploader']['id'];
                $userlist[$picturetemp['uploader']['id']]['username'] = $picturetemp['uploader']['username'];
            }
            $userlist[$picturetemp['uploader']['id']]['numofpicture'] += 1;
        }

        usort($categorylist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($categorylist) > 10) {
            $categorylist = array_slice($categorylist, 0, 10);
        }

        usort($subcategorylist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($subcategorylist) > 10) {
            $subcategorylist = array_slice($subcategorylist, 0, 10);
        }

        usort($userlist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($userlist) > 10) {
            $userlist = array_slice($userlist, 0, 10);
        }

        $column1 = '<div class="panel-heading center">
                        Popular Category Of This Tag
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($categorylist as $categorytemp) {
            $column1 .= '<li>
                                <a title="' . $categorytemp['name'] . ' HD Picture " href="/by_category/' .$categorytemp['id']. '/1">
                                    <span class="badge pull-right">' .$categorytemp['numofpicture']. '</span>' . $categorytemp['name'] . '
                                </a>
                            </li>';
        }
        $column1 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/category">
                            <i class="el el-th-list"></i> View All Category
                        </a>
                    </strong>
                </div>';

        $column2 = '<div class="panel-heading center">
                        Popular Subcategories Of This Tag
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($subcategorylist as $subcategorytemp) {
            $column2 .= '<li>
                                <a title="' . $subcategorytemp['name'] . ' HD Picture " href="/by_subcategory/' .$subcategorytemp['id']. '/1">
                                    <span class="badge pull-right">' .$subcategorytemp['numofpicture']. '</span>' . $subcategorytemp['name'] . '
                                </a>
                            </li>';
        }
        $column2 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/subcategories">
                            <i class="el el-th-list"></i> View All Subcategories
                        </a>
                    </strong>
                </div>';

        $column3 = '<div class="panel-heading center">
                        Most Uploader In This Tag
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($userlist as $usertemp) {
            $column3 .= '<li>
                                <a title="' . $usertemp['username'] . ' HD Picture " href="/users/profile/' .$usertemp['id']. '">
                                    <span class="badge pull-right">' .$usertemp['numofpicture']. '</span>' . $usertemp['username'] . '
                                </a>
                            </li>';
        }
        $column3 .= '</ul></div>';
        $footer = ['column1' => $column1, 'column2' => $column2, 'column3' => $column3];
        $sortcurrent = "";
        if ($sort == "created_at") {
            $sort = "newest";
            $sortcurrent .= "Newest";
        }elseif ($sort == "view") {
            $sortcurrent .= "Most Viewed";
        }elseif ($sort == "download") {
            $sortcurrent .= "Most Download";
        }
        if ($min_resolution != "0x0") {
            $sortcurrent .= ", Resolution ".$resolution_equals." ".$min_resolution;
        }
        $sortdata = [
            'current' => $sortcurrent,
            'view' => '',
            'resolution' => $min_resolution,
            'resolution_equals' => $resolution_equals,
            'sort' => $sort,
            'elementsperpage' => $elementsperpage
        ];
        $pagetitle = count($picturelist) . ' ' . $tag['name'] . ' Pictures';

        if($view == "paged"){
            $sortdata['view'] = "paged";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_tag/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_tag/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_tag/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_tag/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_tag/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_tag/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_tag/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_tag/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_tag/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_tag/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_tag/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_tag/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_tag/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_tag/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_tag/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_tag/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_tag/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_tag/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$tag['name'].' HD Picture | Images" href="/by_tag/'.  $tag["id"] .'/1">
                            <span>'. $tag["name"]  .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.pagination', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_tag', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "infinite") {
            $sortdata['view'] = "infinite";
            if($page == null || $page != 1 || !isset($page)){
                return redirect('by_tag/' . $request->id . '/1');
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }

                $peta = '<a class="breadcrumb-element" title="'.$tag['name'].' HD Picture | Images" href="/by_tag/'.  $tag["id"] .'/1">
                            <span>'. $tag["name"]  .'</span>
                        </a>';

                return view('list.infinite', array('picturelist' => $returndata, 'maxpage' => $maxpage, 'by' => 'by_tag', 'id' => $request->id, 'pagetitle' => $pagetitle, 'sort' => $sortdata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "slideshow") {
            $sortdata['view'] = "slideshow";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_tag/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_tag/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_tag/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_tag/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_tag/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_tag/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_tag/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_tag/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_tag/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_tag/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_tag/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_tag/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_tag/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_tag/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_tag/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_tag/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_tag/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_tag/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$tag['name'].' HD Picture | Images" href="/by_tag/'.  $tag["id"] .'/1">
                            <span>'. $tag["name"]  .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.slideshow', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_tag', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'next' => $page+1, 'null' => $peta, 'id' => $request->id, 'footer' => $footer));
            }   
        }
    }

    private function getBySubcategoryPictureList($cookie, $id){
        $resolution_min = explode("x", $cookie['min_resolution']);
        $picturelist = [];
        $picturewsubcategorylist = Picture::with(['subcategory', 'tag'])->orderBy($cookie['sort'], 'desc')->get()->toArray();
        foreach ($picturewsubcategorylist as $picture) {
            $resolution = explode('x', $picture['resolution']);
            if($cookie['resolution_equals'] == '>='){
                if($resolution[0] >= $resolution_min[0] && $resolution[1] >= $resolution_min[1] && $picture['subcategory']['id'] == $id){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    array_push($picturelist, $picture);
                }
            }elseif ( $cookie['resolution_equals'] == '=') {
                if($resolution[0] == $resolution_min[0] && $resolution[1] == $resolution_min[1] && $picture['subcategory']['id'] == $id){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    array_push($picturelist, $picture);
                }
            }
        }
        return $picturelist;
    }

    public function getBySubcategory(Request $request){
        $cookie = $this->getCookie($request);
        $view = $cookie['view'];
        $min_resolution = $cookie['min_resolution'];
        $resolution_equals = $cookie['resolution_equals'];
        $sort = $cookie['sort'];
        $elementsperpage = $cookie['elementsperpage'];
        $subcategory = Subcategory::where('id', $request->id)->first();
        if($subcategory == null){
            return abort(404);
        }else{
            $subcategory->toArray();
        }
        $page = $request->page;
        $picturelist = $this->getBySubcategoryPictureList($cookie, $request->id);
        $category = Category::where('id', $subcategory['category_id'])->first()->toArray();
        $maxpage = CEIL(count($picturelist)/$elementsperpage);
        if ($maxpage == 0) {
            $maxpage = 1;
        }
        $taglist = [];
        foreach($picturelist as $picturetemp){
            foreach ($picturetemp['tag'] as $tagtemp) {
                if(!array_key_exists($tagtemp['tag_id'], $taglist)){
                    $taglist[$tagtemp['tag_id']]['numofpicture'] = 0;
                    $taglist[$tagtemp['tag_id']]['id'] = $tagtemp['tag_id'];
                    $taglist[$tagtemp['tag_id']]['name'] = $tagtemp['name'];
                }
                $taglist[$tagtemp['tag_id']]['numofpicture'] += 1;
            }
        }
        usort($taglist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($taglist) > 10) {
            $taglist = array_slice($taglist, 0, 10);
        }
        $subcategorylist = Subcategory::where('category_id', $category['id'])->get()->toArray();
        foreach ($subcategorylist as $key => $subcategorytemp) {
            $subcategorylist[$key]["numofpicture"] = Picture::where('subcategory_id', $subcategorytemp['id'])->count();
            unset($subcategorylist[$key]['category_id']);
        }
        
        usort($subcategorylist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($subcategorylist) > 10) {
            $subcategorylist = array_slice($subcategorylist, 0, 10);
        }

        $userlist = [];
        $usertemp = Picture::groupBy('users_id')->select('users_id', DB::raw('count(*) as numofpicture'))->where('subcategory_id', $subcategory['id'])->get()->toArray();
        foreach ($usertemp as $value) {
            if(!array_key_exists($value['users_id'], $userlist)){
                $userlist[$value['users_id']]['numofpicture'] = 0;
                $userlist[$value['users_id']]['id'] = $value['users_id'];
                $userlist[$value['users_id']]['username'] = Users::where('id', $value['users_id'])->first()->toArray()['username'];
            }
            $userlist[$value['users_id']]['numofpicture'] += $value['numofpicture'];
        }
        usort($userlist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($userlist) > 10) {
            $userlist = array_slice($userlist, 0, 10);
        }

        $column1 = '<div class="panel-heading center">
                        Popular Subcategories In Same Category
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($subcategorylist as $subcategorytemp) {
            $column1 .= '<li>
                                <a title="' . $subcategorytemp['name'] . ' HD Picture " href="/by_subcategory/' .$subcategorytemp['id']. '/1">
                                    <span class="badge pull-right">' .$subcategorytemp['numofpicture']. '</span>' . $subcategorytemp['name'] . '
                                </a>
                            </li>';
        }
        $column1 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/subcategories">
                            <i class="el el-th-list"></i> View All Subcategories
                        </a>
                    </strong>
                </div>';

        $column2 = '<div class="panel-heading center">
                        Popular Tags In This Subcategory
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($taglist as $tagtemp) {
            $column2 .= '<li>
                                <a title="' . $tagtemp['name'] . ' HD Picture " href="/by_tag/' .$tagtemp['id']. '/1">
                                    <span class="badge pull-right">' .$tagtemp['numofpicture']. '</span>' . $tagtemp['name'] . '
                                </a>
                            </li>';
        }
        $column2 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/tags">
                            <i class="el el-th-list"></i> View All Tags
                        </a>
                    </strong>
                </div>';

        $column3 = '<div class="panel-heading center">
                        Most Uploader In This Subcategory
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($userlist as $usertemp) {
            $column3 .= '<li>
                                <a title="' . $usertemp['username'] . ' HD Picture " href="/users/profile/' .$usertemp['id']. '">
                                    <span class="badge pull-right">' .$usertemp['numofpicture']. '</span>' . $usertemp['username'] . '
                                </a>
                            </li>';
        }
        $column3 .= '</ul></div>';
        $footer = ['column1' => $column1, 'column2' => $column2, 'column3' => $column3];
        $sortcurrent = "";
        if ($sort == "created_at") {
            $sort = "newest";
            $sortcurrent .= "Newest";
        }elseif ($sort == "view") {
            $sortcurrent .= "Most Viewed";
        }elseif ($sort == "download") {
            $sortcurrent .= "Most Download";
        }
        if ($min_resolution != "0x0") {
            $sortcurrent .= ", Resolution ".$resolution_equals." ".$min_resolution;
        }
        $sortdata = [
            'current' => $sortcurrent,
            'view' => '',
            'resolution' => $min_resolution,
            'resolution_equals' => $resolution_equals,
            'sort' => $sort,
            'elementsperpage' => $elementsperpage
        ];
        $pagetitle = count($picturelist) . ' ' . $subcategory['name'] . ' Pictures';

        if($view == "paged"){
            $sortdata['view'] = "paged";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_subcategory/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_subcategory/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_subcategory/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_subcategory/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_subcategory/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_subcategory/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_subcategory/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_subcategory/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_subcategory/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_subcategory/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_subcategory/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_subcategory/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_subcategory/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_subcategory/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_subcategory/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_subcategory/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_subcategory/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_subcategory/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$category['name'].' HD Pictures | Images" href="/by_category/'.  $category["id"] .'/1">
                            <span>'. $category["name"]  .'</span>
                        </a>
                        <a class="breadcrumb-element" title="'.$subcategory['name'].' HD Pictures | Imagees" href="/by_subcategory/'. $subcategory["id"] .'/1">
                            <span>'. $subcategory["name"]  .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.pagination', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_subcategory', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "infinite") {
            $sortdata['view'] = "infinite";
            if($page == null || $page != 1 || !isset($page)){
                return redirect('by_subcategory/' . $request->id . '/1');
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }

                $peta = '<a class="breadcrumb-element" title="'.$category['name'].' HD Pictures | Images" href="/by_category/'.  $category["id"] .'/1">
                            <span>'. $category["name"]  .'</span>
                        </a>
                        <a class="breadcrumb-element" title="'.$subcategory['name'].' HD Pictures | Images" href="/by_subcategory/'. $subcategory["id"] .'/1">
                            <span>'. $subcategory["name"]  .'</span>
                        </a>';

                return view('list.infinite', array('picturelist' => $returndata, 'maxpage' => $maxpage, 'by' => 'by_subcategory', 'id' => $request->id, 'pagetitle' => $pagetitle, 'sort' => $sortdata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "slideshow") {
            $sortdata['view'] = "slideshow";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_subcategory/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_subcategory/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_subcategory/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_subcategory/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_subcategory/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_subcategory/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_subcategory/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_subcategory/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_subcategory/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_subcategory/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_subcategory/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_subcategory/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_subcategory/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_subcategory/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_subcategory/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_subcategory/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_subcategory/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_subcategory/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$category['name'].' HD Pictures | Images" href="/by_category/'.  $category["id"] .'/1">
                            <span>'. $category["name"]  .'</span>
                        </a>
                        <a class="breadcrumb-element" title="'.$subcategory['name'].' HD Pictures | Images" href="/by_subcategory/'. $subcategory["id"] .'/1">
                            <span>'. $subcategory["name"]  .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.slideshow', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_subcategory', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'next' => $page+1, 'null' => $peta, 'footer' => $footer));
            }   
        }
    }

    private function getByResolutionPictureList($cookie, $resolution){
        $resolution_min = explode("x", $resolution);
        $picturelist = [];
        $picturewsubcategorylist = Picture::with(['subcategory', 'tag'])->orderBy($cookie['sort'], 'desc')->get()->toArray();
        foreach ($picturewsubcategorylist as $picture) {
            $resolution = explode('x', $picture['resolution']);
            if($resolution[0] == $resolution_min[0] && $resolution[1] == $resolution_min[1]){
                $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                $picture['uploader'] = $uploader;
                $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
                unset($picture['subcategory_id']);
                unset($picture['users_id']);
                $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                $picture['tagstring'] = '';
                foreach($picture['tag'] as $key => $tag){
                    unset($picture['tag'][$key]['picture_id']);
                    $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                    $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                }
                $picture['tagstring'] = trim($picture['tagstring'], ' ');
                array_push($picturelist, $picture);
            }
        
        }
        return $picturelist;
    }
    public function getByResolution(Request $request){
        $cookie = $this->getCookie($request);
        $view = $cookie['view'];
        $min_resolution = $cookie['min_resolution'];
        $resolution_equals = $cookie['resolution_equals'];
        $sort = $cookie['sort'];
        $elementsperpage = $cookie['elementsperpage'];
        $page = $request->page;
        $picturelist = $this->getByResolutionPictureList($cookie, $request->id);
        
        $maxpage = CEIL(count($picturelist)/$elementsperpage);
        if ($maxpage == 0) {
            $maxpage = 1;
        }

        $subcategorylist = [];
        $userlist = [];
        $taglist = [];
        foreach($picturelist as $picturetemp){
            if(!array_key_exists($picturetemp['subcategory']['id'], $subcategorylist)){
                $subcategorylist[$picturetemp['subcategory']['id']]['numofpicture'] = 0;
                $subcategorylist[$picturetemp['subcategory']['id']]['id'] = $picturetemp['subcategory']['id'];
                $subcategorylist[$picturetemp['subcategory']['id']]['name'] = $picturetemp['subcategory']['name'];
            }
            $subcategorylist[$picturetemp['subcategory']['id']]['numofpicture'] += 1;

            foreach ($picturetemp['tag'] as $tagtemp) {
                if(!array_key_exists($tagtemp['tag_id'], $taglist)){
                    $taglist[$tagtemp['tag_id']]['numofpicture'] = 0;
                    $taglist[$tagtemp['tag_id']]['id'] = $tagtemp['tag_id'];
                    $taglist[$tagtemp['tag_id']]['name'] = $tagtemp['name'];
                }
                $taglist[$tagtemp['tag_id']]['numofpicture'] += 1;
            }

            if(!array_key_exists($picturetemp['uploader']['id'], $userlist)){
                $userlist[$picturetemp['uploader']['id']]['numofpicture'] = 0;
                $userlist[$picturetemp['uploader']['id']]['id'] = $picturetemp['uploader']['id'];
                $userlist[$picturetemp['uploader']['id']]['username'] = $picturetemp['uploader']['username'];
            }
            $userlist[$picturetemp['uploader']['id']]['numofpicture'] += 1;
        }

        usort($subcategorylist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($subcategorylist) > 10) {
            $subcategorylist = array_slice($subcategorylist, 0, 10);
        }
        usort($taglist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($taglist) > 10) {
            $taglist = array_slice($taglist, 0, 10);
        }
        usort($userlist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($userlist) > 10) {
            $userlist = array_slice($userlist, 0, 10);
        }

        $column1 = '<div class="panel-heading center">
                        Popular Subcategories Of This Keyword
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($subcategorylist as $subcategorytemp) {
            $column1 .= '<li>
                                <a title="' . $subcategorytemp['name'] . ' HD Picture " href="/by_subcategory/' .$subcategorytemp['id']. '/1">
                                    <span class="badge pull-right">' .$subcategorytemp['numofpicture']. '</span>' . $subcategorytemp['name'] . '
                                </a>
                            </li>';
        }
        $column1 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/subcategories">
                            <i class="el el-th-list"></i> View All Subcategories
                        </a>
                    </strong>
                </div>';

        $column2 = '<div class="panel-heading center">
                        Popular Tags Of This Keyword
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($taglist as $tagtemp) {
            $column2 .= '<li>
                                <a title="' . $tagtemp['name'] . ' HD Picture " href="/by_tag/' .$tagtemp['id']. '/1">
                                    <span class="badge pull-right">' .$tagtemp['numofpicture']. '</span>' . $tagtemp['name'] . '
                                </a>
                            </li>';
        }
        $column2 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/tags">
                            <i class="el el-th-list"></i> View All Tags
                        </a>
                    </strong>
                </div>';

        $column3 = '<div class="panel-heading center">
                        Most Uploader In This Keyword
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($userlist as $usertemp) {
            $column3 .= '<li>
                                <a title="' . $usertemp['username'] . ' HD Picture " href="/users/profile/' .$usertemp['id']. '">
                                    <span class="badge pull-right">' .$usertemp['numofpicture']. '</span>' . $usertemp['username'] . '
                                </a>
                            </li>';
        }
        $column3 .= '</ul></div>';
        $footer = ['column1' => $column1, 'column2' => $column2, 'column3' => $column3];
        $sortcurrent = "";
        if ($sort == "created_at") {
            $sort = "newest";
            $sortcurrent .= "Newest";
        }elseif ($sort == "view") {
            $sortcurrent .= "Most Viewed";
        }elseif ($sort == "download") {
            $sortcurrent .= "Most Download";
        }
        if ($min_resolution != "0x0") {
            $sortcurrent .= ", Resolution ".$resolution_equals." ".$min_resolution;
        }
        $sortdata = [
            'current' => $sortcurrent,
            'view' => '',
            'resolution' => $min_resolution,
            'resolution_equals' => $resolution_equals,
            'sort' => $sort,
            'elementsperpage' => $elementsperpage
        ];
        $pagetitle = count($picturelist) . ' ' . $request->id . ' Pictures';

        if($view == "paged"){
            $sortdata['view'] = "paged";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_resolution/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_resolution/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_resolution/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_resolution/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_resolution/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_resolution/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_resolution/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_resolution/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_resolution/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_resolution/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_resolution/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_resolution/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_resolution/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_resolution/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_resolution/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_resolution/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_resolution/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_resolution/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$request->id.' HD Pictures | Images" href="/by_resolution/'.  $request->id .'/1">
                            <span>'. $request->id .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';
                return view('list.pagination', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_resolution', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "infinite") {
            $sortdata['view'] = "infinite";
            if($page == null || $page != 1 || !isset($page)){
                return redirect('by_resolution/' . $request->id . '/1');
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }

                $peta = '<a class="breadcrumb-element" title="'.$request->id.' HD Pictures | Images" href="/by_resolution/'.  $request->id .'/1">
                            <span>'. $request->id .'</span>
                        </a>';

                return view('list.infinite', array('picturelist' => $returndata, 'maxpage' => $maxpage, 'by' => 'by_resolution', 'id' => $request->id, 'pagetitle' => $pagetitle, 'sort' => $sortdata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "slideshow") {
            $sortdata['view'] = "slideshow";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_resolution/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_resolution/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_resolution/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_resolution/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_resolution/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_resolution/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_resolution/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_resolution/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_resolution/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_resolution/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_resolution/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_resolution/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_resolution/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_resolution/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_resolution/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_resolution/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_resolution/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_resolution/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$request->id.' HD Pictures | Images" href="/by_resolution/'.  $request->id .'/1">
                            <span>'. $request->id .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.slideshow', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_resolution', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'next' => $page+1, 'null' => $peta, 'footer' => $footer));
            }   
        }
    }

    private function getByColorPictureList($cookie, $color){
        $picturelisttemp = [0=>[], 1=>[], 2=>[], 3=>[]];
        $picturewsubcategorylist = Picture::with(['subcategory', 'tag', 'color'])->get()->toArray();
        foreach ($picturewsubcategorylist as $picture) {
            $resolution = explode('x', $picture['resolution']);
            $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
            $picture['uploader'] = $uploader;
            $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
            unset($picture['subcategory_id']);
            unset($picture['users_id']);
            $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
            $picture['tagstring'] = '';
            foreach($picture['tag'] as $key => $tag){
                unset($picture['tag'][$key]['picture_id']);
                $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
            }
            $picture['tagstring'] = trim($picture['tagstring'], ' ');
            foreach($picture['color'] as $key => $colortmp){
                if(trim($colortmp['color'],'#') == strtoupper($color)){
                    array_push($picturelisttemp[$key], $picture);
                    break;
                }
            }
        }
        $picturelist = [];
        foreach($picturelisttemp as $listtemp){
            foreach($listtemp as $pic){
                array_push($picturelist, $pic);
            }
        }
        return $picturelist;
    }
    public function getByColor(Request $request){
        $cookie = $this->getCookie($request);
        $view = $cookie['view'];
        $min_resolution = $cookie['min_resolution'];
        $resolution_equals = $cookie['resolution_equals'];
        $sort = $cookie['sort'];
        $elementsperpage = $cookie['elementsperpage'];
        if($request->id == null){
            return back();
        }
        $page = $request->page;
        $picturelist = $this->getByColorPictureList($cookie, $request->id);
        //return view('listtemp', array('category' => $picturelist));
        //get max page
        $maxpage = CEIL(count($picturelist)/$elementsperpage);
        if ($maxpage == 0) {
            $maxpage = 1;
        }
        $subcategorylist = [];
        $userlist = [];
        $taglist = [];
        foreach($picturelist as $picturetemp){
            if(!array_key_exists($picturetemp['subcategory']['id'], $subcategorylist)){
                $subcategorylist[$picturetemp['subcategory']['id']]['numofpicture'] = 0;
                $subcategorylist[$picturetemp['subcategory']['id']]['id'] = $picturetemp['subcategory']['id'];
                $subcategorylist[$picturetemp['subcategory']['id']]['name'] = $picturetemp['subcategory']['name'];
            }
            $subcategorylist[$picturetemp['subcategory']['id']]['numofpicture'] += 1;

            foreach ($picturetemp['tag'] as $tagtemp) {
                if(!array_key_exists($tagtemp['tag_id'], $taglist)){
                    $taglist[$tagtemp['tag_id']]['numofpicture'] = 0;
                    $taglist[$tagtemp['tag_id']]['id'] = $tagtemp['tag_id'];
                    $taglist[$tagtemp['tag_id']]['name'] = $tagtemp['name'];
                }
                $taglist[$tagtemp['tag_id']]['numofpicture'] += 1;
            }

            if(!array_key_exists($picturetemp['uploader']['id'], $userlist)){
                $userlist[$picturetemp['uploader']['id']]['numofpicture'] = 0;
                $userlist[$picturetemp['uploader']['id']]['id'] = $picturetemp['uploader']['id'];
                $userlist[$picturetemp['uploader']['id']]['username'] = $picturetemp['uploader']['username'];
            }
            $userlist[$picturetemp['uploader']['id']]['numofpicture'] += 1;
        }

        usort($subcategorylist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($subcategorylist) > 10) {
            $subcategorylist = array_slice($subcategorylist, 0, 10);
        }
        usort($taglist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($taglist) > 10) {
            $taglist = array_slice($taglist, 0, 10);
        }
        usort($userlist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($userlist) > 10) {
            $userlist = array_slice($userlist, 0, 10);
        }

        $column1 = '<div class="panel-heading center">
                        Popular Subcategories Of This Keyword
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($subcategorylist as $subcategorytemp) {
            $column1 .= '<li>
                                <a title="' . $subcategorytemp['name'] . ' HD Picture " href="/by_subcategory/' .$subcategorytemp['id']. '/1">
                                    <span class="badge pull-right">' .$subcategorytemp['numofpicture']. '</span>' . $subcategorytemp['name'] . '
                                </a>
                            </li>';
        }
        $column1 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/subcategories">
                            <i class="el el-th-list"></i> View All Subcategories
                        </a>
                    </strong>
                </div>';

        $column2 = '<div class="panel-heading center">
                        Popular Tags Of This Keyword
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($taglist as $tagtemp) {
            $column2 .= '<li>
                                <a title="' . $tagtemp['name'] . ' HD Picture " href="/by_tag/' .$tagtemp['id']. '/1">
                                    <span class="badge pull-right">' .$tagtemp['numofpicture']. '</span>' . $tagtemp['name'] . '
                                </a>
                            </li>';
        }
        $column2 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/tags">
                            <i class="el el-th-list"></i> View All Tags
                        </a>
                    </strong>
                </div>';

        $column3 = '<div class="panel-heading center">
                        Most Uploader In This Keyword
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($userlist as $usertemp) {
            $column3 .= '<li>
                                <a title="' . $usertemp['username'] . ' HD Picture " href="/users/profile/' .$usertemp['id']. '">
                                    <span class="badge pull-right">' .$usertemp['numofpicture']. '</span>' . $usertemp['username'] . '
                                </a>
                            </li>';
        }
        $column3 .= '</ul></div>';
        $footer = ['column1' => $column1, 'column2' => $column2, 'column3' => $column3];
        $sortcurrent = "Relevance";
        $sortdata = [
            'current' => $sortcurrent,
            'view' => '',
            'resolution' => $min_resolution,
            'resolution_equals' => $resolution_equals,
            'sort' => 'relevance',
            'elementsperpage' => $elementsperpage
        ];
        $pagetitle = 'Sort Picture By Color';


        if($view == "paged"){
            $sortdata['view'] = "paged";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_color/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_color/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_color/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_color/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_color/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_color/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_color/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_color/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_color/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_color/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_color/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_color/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_color/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_color/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_color/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_color/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_color/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_color/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

               $peta = '<a class="breadcrumb-element" title="#'.$request->id.' HD Pictures | Images" href="/by_color/'. $request->id .'/1">
                                <span>#'. $request->id .'</span>
                            </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.pagination', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_color', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "infinite") {
            $sortdata['view'] = "infinite";
            if($page == null || $page != 1 || !isset($page)){
                return redirect('by_color/' . $request->id . '/1');
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }

                $peta = '<a class="breadcrumb-element" title="#'.$request->id.' HD Pictures | Images" href="/by_color/'. $request->id .'/1">
                                <span>#'. $request->id .'</span>
                            </a>';

                return view('list.infinite', array('picturelist' => $returndata, 'maxpage' => $maxpage, 'by' => 'by_color', 'id' => $request->id, 'pagetitle' => $pagetitle, 'sort' => $sortdata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "slideshow") {
            $sortdata['view'] = "slideshow";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_color/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_color/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_color/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_color/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_color/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_color/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_color/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_color/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_color/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_color/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_color/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_color/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_color/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_color/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_color/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_color/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_color/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_color/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="#'.$request->id.' HD Pictures | Images" href="/by_color/'. $request->id .'/1">
                                <span>#'. $request->id .'</span>
                            </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';
                return view('list.slideshow', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_color', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'next' => $page+1, 'null' => $peta, 'id' => $request->id, 'footer' => $footer));
            }   
        }
    }

    private function getByCategoryPictureList($cookie, $id){
        $resolution_min = explode("x", $cookie['min_resolution']);
        $picturelist = [];
        $picturewsubcategorylist = Picture::with(['subcategory', 'tag'])->orderBy($cookie['sort'], 'desc')->get()->toArray();
        foreach ($picturewsubcategorylist as $picture) {
            $resolution = explode('x', $picture['resolution']);
            if($cookie['resolution_equals'] == '>='){
                if($resolution[0] >= $resolution_min[0] && $resolution[1] >= $resolution_min[1] && $picture['subcategory']['category_id'] == $id){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    array_push($picturelist, $picture);
                }
            }elseif ($cookie['resolution_equals'] == '=') {
                if($resolution[0] == $resolution_min[0] && $resolution[1] == $resolution_min[1] && $picture['subcategory']['category_id'] == $id){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    $picture['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    array_push($picturelist, $picture);
                }
            }
        }
        return $picturelist;
    }
    /**
    * Get by_category page
    *
    * @return view
    */
    public function getByCategory(Request $request){
        $cookie = $this->getCookie($request);
        $view = $cookie['view'];
        $min_resolution = $cookie['min_resolution'];
        $resolution_equals = $cookie['resolution_equals'];
        $sort = $cookie['sort'];
        $elementsperpage = $cookie['elementsperpage'];
        $category = Category::where('id', $request->id)->first();
        if($category == null){
            return abort(404);
        }else{
            $category->toArray();
        }
        $page = $request->page;
        $picturelist = $this->getByCategoryPictureList($cookie, $request->id);
        //get max page
        $maxpage = CEIL(count($picturelist)/$elementsperpage);
        if ($maxpage == 0) {
            $maxpage = 1;
        }
        $taglist = [];
        foreach($picturelist as $picturetemp){
            foreach ($picturetemp['tag'] as $tagtemp) {
                if(!array_key_exists($tagtemp['tag_id'], $taglist)){
                    $taglist[$tagtemp['tag_id']]['numofpicture'] = 0;
                    $taglist[$tagtemp['tag_id']]['id'] = $tagtemp['tag_id'];
                    $taglist[$tagtemp['tag_id']]['name'] = $tagtemp['name'];
                }
                $taglist[$tagtemp['tag_id']]['numofpicture'] += 1;
            }
        }
        usort($taglist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($taglist) > 10) {
            $taglist = array_slice($taglist, 0, 10);
        }

        $subcategorylist = Subcategory::where('category_id', $category['id'])->get()->toArray();
        $userlist = [];
        foreach ($subcategorylist as $key => $subcategory) {
            $usertemp = Picture::groupBy('users_id')->select('users_id', DB::raw('count(*) as numofpicture'))->where('subcategory_id', $subcategory['id'])->get()->toArray();
            foreach ($usertemp as $value) {
                if(!array_key_exists($value['users_id'], $userlist)){
                    $userlist[$value['users_id']]['numofpicture'] = 0;
                    $userlist[$value['users_id']]['id'] = $value['users_id'];
                    $userlist[$value['users_id']]['username'] = Users::where('id', $value['users_id'])->first()->toArray()['username'];
                }
                $userlist[$value['users_id']]['numofpicture'] += $value['numofpicture'];
            }
            $subcategorylist[$key]["numofpicture"] = Picture::where('subcategory_id', $subcategory['id'])->count();
            unset($subcategorylist[$key]['category_id']);
        }

        usort($userlist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($userlist) > 10) {
            $userlist = array_slice($userlist, 0, 10);
        }

        usort($subcategorylist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($subcategorylist) > 10) {
            $subcategorylist = array_slice($subcategorylist, 0, 10);
        }

        $column1 = '<div class="panel-heading center">
                        Popular Subcategories In This Category
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($subcategorylist as $subcategorytemp) {
            $column1 .= '<li>
                                <a title="' . $subcategorytemp['name'] . ' HD Picture " href="/by_subcategory/' .$subcategorytemp['id']. '/1">
                                    <span class="badge pull-right">' .$subcategorytemp['numofpicture']. '</span>' . $subcategorytemp['name'] . '
                                </a>
                            </li>';
        }
        $column1 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/subcategories">
                            <i class="el el-th-list"></i> View All Subcategories
                        </a>
                    </strong>
                </div>';


        $column2 = '<div class="panel-heading center">
                        Popular Tags In This Category
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($taglist as $tagtemp) {
            $column2 .= '<li>
                                <a title="' . $tagtemp['name'] . ' HD Picture " href="/by_tag/' .$tagtemp['id']. '/1">
                                    <span class="badge pull-right">' .$tagtemp['numofpicture']. '</span>' . $tagtemp['name'] . '
                                </a>
                            </li>';
        }
        $column2 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/tags">
                            <i class="el el-th-list"></i> View All Tags
                        </a>
                    </strong>
                </div>';

        $column3 = '<div class="panel-heading center">
                        Most Uploader In This Category
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($userlist as $usertemp) {
            $column3 .= '<li>
                                <a title="' . $usertemp['username'] . ' HD Picture " href="/users/profile/' .$usertemp['id']. '">
                                    <span class="badge pull-right">' .$usertemp['numofpicture']. '</span>' . $usertemp['username'] . '
                                </a>
                            </li>';
        }
        $column3 .= '</ul></div>';
        $footer = ['column1' => $column1, 'column2' => $column2, 'column3' => $column3];
        $sortcurrent = "";
        if ($sort == "created_at") {
            $sort = "newest";
            $sortcurrent .= "Newest";
        }elseif ($sort == "view") {
            $sortcurrent .= "Most Viewed";
        }elseif ($sort == "download") {
            $sortcurrent .= "Most Download";
        }
        if ($min_resolution != "0x0") {
            $sortcurrent .= ", Resolution ".$resolution_equals." ".$min_resolution;
        }
        $sortdata = [
            'current' => $sortcurrent,
            'view' => '',
            'resolution' => $min_resolution,
            'resolution_equals' => $resolution_equals,
            'sort' => $sort,
            'elementsperpage' => $elementsperpage
        ];
        $pagetitle = count($picturelist) . ' ' . $category['name'] . ' Pictures';


        if($view == "paged"){
            $sortdata['view'] = "paged";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_category/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_category/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_category/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_category/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_category/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_category/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_category/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$category['name'].' HD Pictures | Images" href="/by_category/'. $category["id"] .'/1">
                            <span>'. $category["name"]  .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.pagination', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_category', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'current' => $page, 'null' => $peta, 'category' => $category, 'footer' => $footer));
            }
        }elseif ($view == "infinite") {
            $sortdata['view'] = "infinite";
            if($page == null || $page != 1 || !isset($page)){
                return redirect('by_category/' . $request->id . '/1');
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }

                $peta = '<a class="breadcrumb-element" title="'.$category['name'].' HD Pictures | Images" href="/by_category/'. $category["id"] .'/1">
                            <span>'. $category["name"]  .'</span>
                        </a>';

                return view('list.infinite', array('picturelist' => $returndata, 'maxpage' => $maxpage, 'by' => 'by_category', 'id' => $request->id, 'pagetitle' => $pagetitle, 'sort' => $sortdata, 'current' => $page, 'null' => $peta, 'footer' => $footer));
            }
        }elseif ($view == "slideshow") {
            $sortdata['view'] = "slideshow";
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_category/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_category/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_category/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_category/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_category/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_category/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_category/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$category['name'].' HD Pictures | Images" href="/by_category/'. $category["id"] .'/1">
                            <span>'. $category["name"]  .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.slideshow', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_category', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'next' => $page+1, 'null' => $peta, 'id' => $request->id, 'footer' => $footer));
            }   
        }
        /*if($request->page == null || $request->page < 1 ){
            $url = 'by_category/' . $request->id . '/1';
            return redirect('by_category/' . $request->id . '/1');
        }else{

            return view('list', array('id' => $request->id, 'page' => $request->page));
        }*/
        /*$value = $request->cookie('name');
        if($value == ''){
            Cookie::queue('name', 'MyValue');
            return view('list', array('id' => $request->id, 'page' => $request->page, 'name' => $value));
        }else{
            return view('list', array('id' => $request->id, 'page' => $request->page, 'name' => $value));
        }*/
    }

    public function getSubcategoryList(Request $request){
        $subcategorylist = Subcategory::orderBy('name', 'asc')->get()->toArray();
        $letter = trim($request->query('letter'), ' ');
        if($letter == null || $letter == ''){
            $letter = '#';
        }elseif((unpack("C*",$letter)[1] >= 65 && unpack("C*",$letter)[1] <= 90) || (unpack("C*",$letter)[1] >= 97 && unpack("C*",$letter)[1] <= 122)){
            $letter = strtoupper($letter);
        }else{
            $letter = '#';
        }
        $list = [ '#' => [], 'A' => [], 'B' => [], 'C' => [], 'D' => [], 'E' => [], 'F' => [], 'G' => [], 'H' => [], 'I' => [], 'J' => [], 'K' => [], 'L' => [], 'M' => [], 'N' => [], 'O' => [], 'P' => [], 'Q' => [], 'R' => [], 'S' => [], 'T' => [], 'U' => [], 'V' => [], 'W' => [], 'X' => [], 'Y' => [], 'Z' => [] ];
        foreach ($subcategorylist as $key => $subcategory) {
            $subcategory['numofpicture'] = Picture::where('subcategory_id', $subcategory['id'])->count();
            $subcategory['category'] = Category::where('id', $subcategory['category_id'])->first()->toArray()['name'];
            $firstletter = unpack("C*", substr($subcategory['name'], 0, 1))[1];
            if (($firstletter >= 65 && $firstletter <= 90) || ($firstletter >= 97 && $firstletter <= 122)){
                array_push($list[strtoupper(substr($subcategory['name'], 0, 1))], $subcategory);
            }else{
                array_push($list['#'], $subcategory);
            }
        }
        foreach ($list as $key => $value) {
            if(count($value) <= 0){
                unset($list[$key]);
            }
        }
        $custom_nav = '';
        $data = '';
        $peta = 'All Subcategory';
        $title = "View All " . count($subcategorylist) . " Subcategories";
        if(array_key_exists($letter, $list)){
            foreach (array_keys($list) as $key => $value) {
                if($value == $letter){
                    $custom_nav .= '<div class="active custom-nav-letters-element">' . $value . '</div>';
                }else{
                    $custom_nav .= '<a class="custom-nav-letters-element" title=" See all subcategories beginning with the letter ' . $value . '! " href="subcategories?letter=' . $value . '">' . $value . '</a>';
                }
            }
            foreach ($list[$letter] as $value) {
                $data .= '<div class="sub_category_container">
                <a
                  href="by_subcategory/' . $value["id"] . '/1"
                  title="' . $value["name"] . ' - ' . $value["category"] . ' HD Picture | Background Images"
                >
                  <span>&#187;</span> ' . $value["name"] . '
                </a>
                <span class="badge pull-right">' . $value["numofpicture"]. '</span></div>';
            }
            $title2 = count($list[$letter]) . " Subcategories beginning with " . $letter . " have been found";
            return view('list.list', array('custom_nav' => $custom_nav, 'list' => $data, 'null' => $peta, 'title2' => $title2, 'title' => $title));
        }else{
            return redirect("/subcategories?letter=" . array_keys($list)[0]);
        }
        
    }

    public function getCategoryList(Request $request){
        $categorylist = Category::orderBy('name', 'asc')->get()->toArray();
        $letter = trim($request->query('letter'), ' ');
        if($letter == null || $letter == ''){
            $letter = '#';
        }elseif((unpack("C*",$letter)[1] >= 65 && unpack("C*",$letter)[1] <= 90) || (unpack("C*",$letter)[1] >= 97 && unpack("C*",$letter)[1] <= 122)){
            $letter = strtoupper($letter);
        }else{
            $letter = '#';
        }
        $list = [ '#' => [], 'A' => [], 'B' => [], 'C' => [], 'D' => [], 'E' => [], 'F' => [], 'G' => [], 'H' => [], 'I' => [], 'J' => [], 'K' => [], 'L' => [], 'M' => [], 'N' => [], 'O' => [], 'P' => [], 'Q' => [], 'R' => [], 'S' => [], 'T' => [], 'U' => [], 'V' => [], 'W' => [], 'X' => [], 'Y' => [], 'Z' => [] ];
        foreach ($categorylist as $key => $category) {
            $piclist = Picture::with(['subcategory'])->get()->toArray();
            $category['numofpicture'] = 0;
            foreach ($piclist as $value) {
                if($value['subcategory']['category_id'] == $category['id']){
                    $category['numofpicture'] += 1;
                }
            }
            $firstletter = unpack("C*", substr($category['name'], 0, 1))[1];
            if (($firstletter >= 65 && $firstletter <= 90) || ($firstletter >= 97 && $firstletter <= 122)){
                array_push($list[strtoupper(substr($category['name'], 0, 1))], $category);
            }else{
                array_push($list['#'], $category);
            }
        }
        foreach ($list as $key => $value) {
            if(count($value) <= 0){
                unset($list[$key]);
            }
        }
        $custom_nav = '';
        $data = '';
        $peta = 'All Category';
        $title = "View All " . count($categorylist) . " Category";
        if(array_key_exists($letter, $list)){
            foreach (array_keys($list) as $key => $value) {
                if($value == $letter){
                    $custom_nav .= '<div class="active custom-nav-letters-element">' . $value . '</div>';
                }else{
                    $custom_nav .= '<a class="custom-nav-letters-element" title=" See all category beginning with the letter ' . $value . '! " href="category?letter=' . $value . '">' . $value . '</a>';
                }
            }
            foreach ($list[$letter] as $value) {
                $data .= '<div class="sub_category_container">
                <a
                  href="by_category/' . $value["id"] . '/1"
                  title="' . $value["name"] . ' HD Picture | Background Images"
                >
                  <span>&#187;</span> ' . $value["name"] . '
                </a>
                <span class="badge pull-right">' . $value["numofpicture"]. '</span></div>';
            }
            $title2 = count($list[$letter]) . " Category beginning with " . $letter . " have been found";
            return view('list.list', array('custom_nav' => $custom_nav, 'list' => $data, 'null' => $peta, 'title2' => $title2, 'title' => $title));
        }else{
            return redirect("/category?letter=" . array_keys($list)[0]);
        }
        
    }

    public function getTagList(Request $request){
        $taglist = Tag::orderBy('name', 'asc')->get()->toArray();
        $letter = trim($request->query('letter'), ' ');
        if($letter == null || $letter == ''){
            $letter = '#';
        }elseif((unpack("C*",$letter)[1] >= 65 && unpack("C*",$letter)[1] <= 90) || (unpack("C*",$letter)[1] >= 97 && unpack("C*",$letter)[1] <= 122)){
            $letter = strtoupper($letter);
        }else{
            $letter = '#';
        }
        $list = [ '#' => [], 'A' => [], 'B' => [], 'C' => [], 'D' => [], 'E' => [], 'F' => [], 'G' => [], 'H' => [], 'I' => [], 'J' => [], 'K' => [], 'L' => [], 'M' => [], 'N' => [], 'O' => [], 'P' => [], 'Q' => [], 'R' => [], 'S' => [], 'T' => [], 'U' => [], 'V' => [], 'W' => [], 'X' => [], 'Y' => [], 'Z' => [] ];
        foreach ($taglist as $key => $tag) {
            $tag['numofpicture'] = PictureTag::where('tag_id',$tag['id'])->count();
            $firstletter = unpack("C*", substr($tag['name'], 0, 1))[1];
            if (($firstletter >= 65 && $firstletter <= 90) || ($firstletter >= 97 && $firstletter <= 122)){
                array_push($list[strtoupper(substr($tag['name'], 0, 1))], $tag);
            }else{
                array_push($list['#'], $tag);
            }
        }
        foreach ($list as $key => $value) {
            if(count($value) <= 0){
                unset($list[$key]);
            }
        }
        $custom_nav = '';
        $data = '';
        $peta = 'All Tags';
        $title = "View All " . count($taglist) . " Tags";
        if(array_key_exists($letter, $list)){
            foreach (array_keys($list) as $key => $value) {
                if($value == $letter){
                    $custom_nav .= '<div class="active custom-nav-letters-element">' . $value . '</div>';
                }else{
                    $custom_nav .= '<a class="custom-nav-letters-element" title=" See all tag beginning with the letter ' . $value . '! " href="/tags?letter=' . $value . '">' . $value . '</a>';
                }
            }
            foreach ($list[$letter] as $value) {
                $data .= '<div class="sub_category_container">
                <a
                  href="by_tag/' . $value["id"] . '/1"
                  title="' . $value["name"] . ' HD Picture | Background Images"
                >
                  <span>&#187;</span> ' . $value["name"] . '
                </a>
                <span class="badge pull-right">' . $value["numofpicture"]. '</span></div>';
            }
            $title2 = count($list[$letter]) . " Tags beginning with " . $letter . " have been found";
            return view('list.list', array('custom_nav' => $custom_nav, 'list' => $data, 'null' => $peta, 'title2' => $title2, 'title' => $title));
        }else{
            return redirect("/tags?letter=" . array_keys($list)[0]);
        }
        
    }
    

}
