<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    
    public function login(Request $request){
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|string',
                'password' => 'required',
            ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
        }

        $user = User::where(['email'  => $request->email])->first();

        if (isset($user) && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('authToken')->accessToken;
            
            return response()->json([
                'status' => 200,
                'user' => new UserResource($user),
                'token' =>$token,
            ], 200);
            
        }else{
            return response()->json(['status' => 400, 'message' => "Invalid credentials"], 200);
        }
    }

    public function register(Request $request) {
        
        try {

            $validator = Validator::make($request->all(),
            [
                'name' => 'required|string|max:30',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|min:5|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => 400, 'message' => $validator->errors()->first()], 200);
            }

            $image_path = null;
            if($request->file('image')) {
                $data = $request->file('image');
                $file_name = (string) Str::uuid().time() . '.png';
                Storage::putFileAs('users', $data, $file_name);
                $image_path = 'users/'. $file_name;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'image' => $image_path,
                'email_verified_at' => \Carbon\Carbon::now()
            ]);

            if (isset($user) && Hash::check($request->password, $user->password)) {
                $token = $user->createToken('authToken')->accessToken;
                
                return response()->json([
                    'status' => 200,
                    'user' => new UserResource($user),
                    'token' => $token,
                ], 200);
                
            } else {
                return response()->json(['status' => 400, 'message' => "Error creating account"], 200);
            }

        } catch(\Exception $e) {
            return response()->json([
                'status' => 401,
                'error' => $e->getMessage(),
                'message' => "Sorry, we encountered an error"
            ]);
        }
    }

    public function logout(Request $request) {
        $token = $request->user()->token();
        $token->revoke();
        return response()->json(['status' => 200]);
    }
}
