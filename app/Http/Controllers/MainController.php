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

class MainController extends Controller
{
    public function getMain(){
        $picturelist = Picture::with(['subcategory', 'tag'])->orderBy('id', 'desc')->limit(30)->get()->toArray();
        foreach ($picturelist as $key => $picture) {
            $picturelist[$key]['uploader'] = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
            $picturelist[$key]['subcategory']['category'] = Category::where('id', $picture['subcategory']['category_id'])->first()->toArray()['name'];
            unset($picturelist[$key]['subcategory_id']);
            unset($picturelist[$key]['users_id']);
            $picturelist[$key]['tagstring'] = '';
            foreach($picture['tag'] as $key2 => $tag){
                unset($picturelist[$key]['tag'][$key2]['picture_id']);
                $picturelist[$key]['tag'][$key2]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                $picturelist[$key]['tagstring'] .= ' '.$picturelist[$key]['tag'][$key2]['name'];
            }
            $picturelist[$key]['tagstring'] = trim($picturelist[$key]['tagstring'], ' ');
        }
        
        $picturetemplist = Picture::with(['subcategory', 'tag'])->get()->toArray();
        $categorylist = Category::all()->toArray();
        foreach ($categorylist as $key => $category) {
            $categorylist[$key]['numofpicture'] = 0;
            foreach ($picturetemplist as $value) {
                if($value['subcategory']['category_id'] == $category['id']){
                    $categorylist[$key]['numofpicture'] += 1;
                }
            }
        }

        $subcategorylist = Subcategory::all()->toArray();
        foreach ($subcategorylist as $key => $subcategorytemp) {
            $subcategorylist[$key]["numofpicture"] = Picture::where('subcategory_id', $subcategorytemp['id'])->count();
            unset($subcategorylist[$key]['category_id']);
        }
        usort($subcategorylist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($subcategorylist) > 15) {
            $subcategorylist = array_slice($subcategorylist, 0, 15);
        }

        $taglist = [];
        $countview = 0;
        $countdownload = 0;
        foreach($picturetemplist as $picturetemp){
            $countview += $picturetemp['view'];
            $countdownload += $picturetemp['download'];
            foreach ($picturetemp['tag'] as $tagtemp) {
                if(!array_key_exists($tagtemp['tag_id'], $taglist)){
                    $taglist[$tagtemp['tag_id']]['numofpicture'] = 0;
                    $taglist[$tagtemp['tag_id']]['id'] = $tagtemp['tag_id'];
                    $taglist[$tagtemp['tag_id']]['name'] = Tag::where('id', $tagtemp['tag_id'])->first()->toArray()['name'];
                }
                $taglist[$tagtemp['tag_id']]['numofpicture'] += 1;
            }
        }
        usort($taglist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($taglist) > 15) {
            $taglist = array_slice($taglist, 0, 15);
        }

        $userlist = [];
        $usertemp = Picture::groupBy('users_id')->select('users_id', DB::raw('count(*) as numofpicture'))->get()->toArray();
        foreach ($usertemp as $value) {
            if(!array_key_exists($value['users_id'], $userlist)){
                $userlist[$value['users_id']]['numofpicture'] = 0;
                $userlist[$value['users_id']]['id'] = $value['users_id'];
                $userlist[$value['users_id']]['username'] = Users::where('id', $value['users_id'])->first()->toArray()['username'];
                $userlist[$value['users_id']]['avatar'] = Users::where('id', $value['users_id'])->first()->toArray()['avatar'];
            }
            $userlist[$value['users_id']]['numofpicture'] += $value['numofpicture'];
        }
        usort($userlist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($userlist) > 15) {
            $userlist = array_slice($userlist, 0, 15);
        }

        $resolutionlist = [];
        $resolutiontemp = Picture::groupBy('resolution')->select('resolution', DB::raw('count(*) as numofpicture'))->get()->toArray();
        foreach ($resolutiontemp as $value) {
            if(!array_key_exists($value['resolution'], $resolutionlist)){
                $resolutionlist[$value['resolution']]['numofpicture'] = 0;
                $resolutionlist[$value['resolution']]['resolution'] = $value['resolution'];
            }
            $resolutionlist[$value['resolution']]['numofpicture'] += $value['numofpicture'];
        }
        usort($resolutionlist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($resolutionlist) > 15) {
            $resolutionlist = array_slice($resolutionlist, 0, 15);
        }
        
        $total = Picture::count();
        $user = Users::count();

        $about = ['totalpic' => $total, 'user' => $user, 'view' => $countview, 'download' => $countdownload];

        return view('main', array('picturelist' => $picturelist, 'subcategorylist' => $subcategorylist, 'categorylist' => $categorylist, 'taglist' => $taglist, 'userlist' => $userlist, 'resolutionlist' => $resolutionlist, 'about' => $about));
        //return view('listtemp', array('list' => $resolutionlist));
    }
}