<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Validator;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\MessageBag;

use App\Http\Model\Users;

class UsersController extends Controller
{
    /**
    * Get register page
    *
    * @return register view
    */
    public function getRegister(){
        if(Auth::check()){
            return redirect('/');
        }else{
            return view('users.register');
        }
    }

    /**
     * Handle register request
     * @param request - Register data
     * @return json - result
     */
    public function postRegister(Request $request){
    	$rules = [
    		'username' => 'required|unique:users,username|min:6|max:24',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8',
            'email' => 'required|email|max:100'
    	];
    	$message = [
            'username.required' => 'Username is required',
            'username.unique' => 'Username already exists',
            'username.min' => 'Username must be at least 6 characters',
            'username.max' => 'Username must not be longer than 24 characters',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'password_confirmation.required' => 'Confirm Password is required',
            'password_confirmation.min' => 'Confirm Password must be at least 8 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.max' => 'Email must not be longer than 24 characters',
    	];

    	$validator = Validator::make($request->all(), $rules, $message);
    	
    	if($validator->fails()){
    		return response() -> json([
                'error' => true,
                'message' => $validator->errors()
            ], 200);
    	}else{
            $user = new Users;
            $user->username = $request->username;
            $user->password = bcrypt($request->password);
            $user->type = 'user';
            $user->email = $request->email;
            $user->save();
            return response() -> json([
                'error' => false
            ], 200);
        }
    }

    /**
    * Get login page
    *
    * @return login view
    */
    public function getLogin(){
        if(Auth::check()){
            return redirect('/');
        }else{
            return view('users.login');
        }
    }

    /**
     * Handle login request
     * @param request - login data
     * @return json - result
     */
    public function postLogin(Request $request){
        $rules = [
    		'username' => 'required',
    		'password' => 'required',
    	];
    	$message = [
    		'username.required' => 'Username field is required',
    		'password.required' => 'Password field is required',
        ];
        $validator = Validator::make($request->all(), $rules, $message);
        if($validator->fails()){
    		return response() -> json([
                'error' => true,
                'message' => $validator->errors()
            ], 200);
    	}else{
    		$remember = $request->input('remember');
    		$username = $request->input('username');
    		$password = $request->input('password');
    		if (Auth::attempt(['username' => $username, 'password' => $password], $remember)) {
                return response() -> json([
                    'error' => false
                ], 200);

    		} else {
    			$errors = new MessageBag(['signin' => 'Invalid username/password combination']);
                return response() -> json([
                    'error' => true,
                    'message' => $errors
                ], 200);
    		}
    	}
    }


    /**
     * Logout
     */
    public function getLogout(){
        if(Auth::check()){
            Auth::logout();
            return redirect('/');
        }else{
            return redirect('/');
        }
    }
}
