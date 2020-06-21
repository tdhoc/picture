<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Validator;

use Hash;

use Storage;

use Illuminate\Support\MessageBag;

use Illuminate\Support\Facades\Auth;

use App\Http\Model\Users;

use App\Http\Model\Picture;

use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
    * Get profile page
    *
    * @return profile view
    */
    public function getProfile(Users $users){
        $check_users = Users::where('id', $users->id)->first();
        if ($check_users) {
            $count = Picture::where('users_id', $users->id)->count();
            $recentPictures = Picture::where('users_id', $users->id)->orderBy('created_at', 'DESC')->get();
            $viewPictures = Picture::where('users_id', $users->id)->orderBy('view', 'DESC')->take(5)->get();
            $view = Picture::where('users_id', $users->id)->sum('view');
            $downloadPictures = Picture::where('users_id', $users->id)->orderBy('download', 'DESC')->take(5)->get();
            $download = Picture::where('users_id', $users->id)->sum('download');
            return view('users.profile', array('users' => $users,
                        'recentPictures' => $recentPictures,
                        'viewPictures' => $viewPictures,
                        'downloadPictures' => $downloadPictures,
                        'view' => $view,
                        'download' => $download,
                        'count' => $count));
        } else return abort(404);
    }

    /**
    * Get edit-profile page
    *
    * @return edit-profile view
    */
    public function getEditProfile(Request $request){
        if(Auth::check()){
            return view('users.edit-profile', array('user' => Auth::user()));
        } else {
            return redirect('/users/login');
        }
    }

    /**
     * Handle edit-profile request
     * @param request - edit-profile data
     * @return json - result
     */
    public function postEditProfile(Request $request){
        $password = $request->input('password');
        $new_password = $request->input('new_password');
        $new_password_confirmation = $request->input('new_password_confirmation');
        $email = $request->input('email');

        if ($new_password) {
            $rules = [
                'password' => 'required|min:8',
                'new_password' => 'required|min:8|confirmed',
                'new_password_confirmation' => 'required|min:8',
                'email' => 'required|email|max:100',
            ];
            $message = [
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 characters',
                'new_password.min' => 'Password must be at least 8 characters',
                'new_password.confirmed' => 'Password confirmation does not match',
                'new_password_confirmation.required' => 'Confirm New Password is required',
                'new_password_confirmation.min' => 'Confirm New Password must be at least 8 characters',
                'email.email' => 'Email must be a valid email address',
                'email.max' => 'Email must not be longer than 24 characters',
            ];
        } else {
            $rules = [
                'password' => 'required|min:8',
                'email' => 'required|email|max:100',
            ];
            $message = [
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 characters',
                'email.email' => 'Email must be a valid email address',
                'email.max' => 'Email must not be longer than 24 characters',
            ];
        }

    	$validator = Validator::make($request->all(), $rules, $message);
    	
    	if($validator->fails()){
    		return response() -> json([
                'error' => true,
                'message' => $validator->errors()
            ], 200);
    	} else {
            if (Hash::check($password, Auth::user()->password)) {
                if (($new_password != null) && ($new_password == $new_password_confirmation)) {
                    $users = Auth::user();
                    $users->password = Hash::make($new_password);
                    $users->email = $email;
                    if ($request->hasFile('avatar')){
                        $avatar = $request->file('avatar');
                        $filename = time() . '.' . $avatar->getClientOriginalExtension();
                        $avatar->move(public_path('/avatar'), $filename);
                        $users->avatar = '/avatar/' . $filename;
                    }
                    if ($request->hasFile('background')){
                        $background = $request->file('background');
                        $filename = time() . '.' . $background->getClientOriginalExtension();
                        $background->move(public_path('/background'), $filename);
                        $users->background = '/background/' . $filename;
                    }
                    $users->save();
                    return response() -> json([
                        'error' => false,
                    ], 200);
                } else if (($new_password == null) && ($new_password_confirmation == null)) {
                    $users = Auth::user();
                    $users->email = $email;
                    if ($request->hasFile('avatar')){
                        $avatar = $request->file('avatar');
                        $filename = time() . '.' . $avatar->getClientOriginalExtension();
                        $avatar->move(public_path('/avatar'), $filename);
                        $users->avatar = '/avatar/' . $filename;
                    }
                    if ($request->hasFile('background')){
                        $background = $request->file('background');
                        $filename = time() . '.' . $background->getClientOriginalExtension();
                        $background->move(public_path('/background'), $filename);
                        $users->background = '/background/' . $filename;
                    }
                    $users->save();
                    return response() -> json([
                        'error' => false,
                    ], 200);
                } else {
                    $errors = new MessageBag(['edit' => 'Something wrong with new password']);
                    return response() -> json([
                        'error' => true,
                        'message' => $errors
                    ], 200);
                }
    		} else {
    			$errors = new MessageBag(['edit' => 'Invalid password']);
                return response() -> json([
                    'error' => true,
                    'message' => $errors
                ], 200);
    		}
        }
    }
}