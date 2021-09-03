<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
// use JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function index()
    {
        DB::beginTransaction();
        try {
            $user = User::all();
            return $this->respondWithSuccess($user);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                "firstname" => "required|regex:/^([^0-9]*)$/",
                "lastname" => "required|regex:/^([^0-9]*)$/",
                "mobile" => "required|numeric|digits:10|unique:users,mobile",
                "email" => "required|email|unique:users,email",
                "gender" => "required|in:m,f,0",
                "age" => "required|numeric|min:1|max:3",
                "city" => "required",
                'password' => 'min:6|required_with:password_confirmation|same:password_confirmation|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
                'password_confirmation' => 'min:6'
            ]);
            if ($validator->fails()) {
                return $this->respondWithValidationError($validator->errors()->first());
            }
            $user = new User();
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->mobile = $request->mobile;
            $user->email = $request->email;
            $user->gender = $request->gender;
            $user->age = $request->age;
            $user->city = $request->city;
            $user->password = Hash::make($request->password);
            $user->save();
            DB::commit();
            if ($user) {
                return $this->respondWithSuccess($user);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }

    public function edit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::where("u_id", $id)->first();
            if (!$user) {
                return $this->respondWithError("user not found");
            }
            return $this->respondWithSuccess($user);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if (!$user) {
                return $this->respondWithError("user not found");
            }
            $validator = Validator::make($request->all(), [
                "firstname" => "required|regex:/^([^0-9]*)$/",
                "lastname" => "required|regex:/^([^0-9]*)$/",
                "mobile" => "required|numeric|digits:10", Rule::unique('users')->ignore($user->u_id, 'u_id'),
                "email" => "required|email", Rule::unique('users')->ignore($user->u_id, 'u_id'),
                "gender" => "required|in:m,f,0",
                "age" => "required|numeric|min:1|max:3",
                "city" => "required",
            ]);
            if ($validator->fails()) {
                return $this->respondWithValidationError($validator->errors()->first());
            }
            // $user = User::where("u_id",$id)->update(["firstname"=>$request->firstname]);
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->mobile = $request->mobile;
            $user->email = $request->email;
            $user->gender = $request->gender;
            $user->age = $request->age;
            $user->city = $request->city;
            $user->save();
            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }
    
    public function delete(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::where("u_id", $id)->delete();
            if ($user) {
                return $this->respondWithSuccess("","user deleted successfully");
            }
            DB::commit();
            return $this->respondWithError($user);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }

    public function login(Request $request){
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator->errors()->first());
        }
        if (! $token = JWTAuth::attempt($credentials)) {
                return $this->respondWithError("Login credentials are invalid.");
        }
        return $this->respondWithSuccess($token);
    }

    public function userDetails(Request $request)
    {
        // $this->validate($request, [
        //     'token' => 'required'
        // ]);
        // return "sdfdsfs";
        return $request->user;
        // $user = JWTAuth::authenticate($request->token);
        // return response()->json(['user' => $user]);
    }
}
