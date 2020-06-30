<?php

namespace App\Http\Controllers\Api;


use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseController as ResponseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;
use App\Notifications\SignupActivate;
use Illuminate\Support\Str;

class AuthController extends ResponseController
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|',
            'email' => 'required|string|email|unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileToStore =$image->getClientOriginalName();
            $image->move(base_path('public/user'), $fileToStore);
        } else {
            $fileToStore = 'noimage.jpg';
        }

        $input = $request->all();
        $input['username']=$request->username;
        $input['password'] = bcrypt($input['password']);
        $input['activation_token']=Str::random(60);
        $role = Role::find(1);
        $user = User::create($input);
        $user['image']=$fileToStore;
        $role->users()->save($user);
        if($user){
            $success['token'] =  $user->createToken('token')->accessToken;
            //team, ky reshti eshte per me notify userin me nje email per me aktivizu accountin
//            $user->notify(new SignupActivate($user));
            return response()->json([
                'created' => true,
                'access_token' => $success['token'],
                'user' => $user
            ], 201);
        }
        else{
            $error = "Sorry! Registration is not successfull.";
            return $this->sendError($error, 401);
        }

    }

    public function login(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        $credentials = request(['email', 'password']);
        $credentials['active'] = 1;
        $credentials['deleted_at'] = null;
        if(!Auth::attempt($credentials)){
            $error = "Unauthorized";
            return $this->sendError($error, 401);
        }
        $user = $request->user();
        $success['token'] =  $user->createToken('token')->accessToken;
        return response()->json([
            'access_token' => $success['token'],
            'user' => $user
        ], 200);
    }

    public function logout(Request $request)
    {

        $isUser = $request->user()->token()->revoke();
        if($isUser){
            $success['message'] = "Successfully logged out.";
            return $this->sendResponse($success);
        }
        else{
            $error = "Something went wrong.";
            return $this->sendResponse($error);
        }
    }

    public function getUser(Request $request)
    {
        //$id = $request->user()->id;
        $user = $request->user();
        if($user){
            return $this->sendResponse($user);
        }
        else{
            $error = "user not found";
            return $this->sendResponse($error);
        }
    }
    public function signupActivate($token)
    {
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            return response()->json([
                'message' => 'This activation token is invalid.'
            ], 404);
        }
        $user->active = true;
        $user->activation_token = '';
        $user->save();
        return $user;
    }
}
