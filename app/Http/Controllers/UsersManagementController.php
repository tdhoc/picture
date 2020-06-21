<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Validator;

use Illuminate\Support\MessageBag;

use Illuminate\Support\Facades\Auth;

use App\Http\Model\Users;

use Illuminate\Support\Facades\DB;

class UsersManagementController extends Controller
{
    /**
    * Get user page
    *
    * @return user view
    */
    public function getUser(Request $request){
        $users = Users::all();
        return view('admin.users-management', array('users' => $users, 'authUser' => Auth::user()));
    }

    /**
    * Edit user
    *
    * @param $request
    *
    * @return json
    */
    public function postEdit(Request $request){
        $rules = [
          'editemail' => 'required|email|max:100',
          'edittype' => 'required',
        ];

        $message = [
          'editemail.required' => 'Email is required',
          'editemail.email' => 'Email is not valid',
          'email.max' => 'Email must not be longer than 24 characters',
          'edittype.required' => 'Type is required',
        ];
        
        $validator = Validator::make($request->all(), $rules, $message);
        
        if($validator->fails()){
            return response() -> json([
                    'error' => true,
                    'message' => $validator->errors()
                ], 200);

       }else{
          $users = Users::where('id', $request->editid)->first();
          $users->email = $request->editemail;
          $users->type = $request->edittype;
          $users->save();
          return response() -> json([
                    'error' => false,
                    'message' => 'success'
                ], 200);
        }
      }

    /**
    * Add User
    *
    * @param $request
    *
    * @return json
    */
    public function postAdd(Request $request){

      $rules = [
        'username' => 'required|unique:users,username|min:6|max:24',
        'password' => 'required|min:8',
        'type' => 'required',
        'email' => 'required|email|max:100'
      ];

      $message = [
        'username.required' => 'Username is required',
        'username.unique' => 'Username already exists',
        'username.min' => 'Username must be at least 6 characters',
        'username.max' => 'Username must not be longer than 24 characters',
        'password.required' => 'Password is required',
        'password.min' => 'Password must be at least 8 characters',
        'type.required' => 'Type is required',
        'email.required' => 'Email is required',
        'email.email' => 'Email is not valid',
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
        $user->type = $request->type;
        $user->email = $request->email;
        $user->save();
        return response() -> json([
                  'error' => false,
                  'message' => 'success'
            ], 200);
        }
     }


    /**
    * Delete User
    *
    * @param $request
    *
    * @return json
    */
    public function postDelete(Request $request){
      $user = Users::where('id', $request->deleteid)->first();
      $user->delete();
      return response() -> json([
                  'error' => false,
            ], 200);
    }
}