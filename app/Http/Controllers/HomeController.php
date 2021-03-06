<?php

namespace App\Http\Controllers;

use App\Document;
use App\Http\Transformers\UserTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct()
        {

        }

        //TODO: fix file linking system - what is connected to what and where is it stored
        //TODO: fix family recursion
        //TODO: NEXT BIG STEP BACKEND - RETURN FILE LINK PATHS WITH DOCUMENTS
        //TODO: NEXT BIG STEP FRONTEND - RETURN PERSONS DOCUMENT PICTURES

        /**
         * Show the application dashboard.
         *
         * @return \Illuminate\Http\Response
         */
        public function index()
        {
                return view('home');
        }

        public function test()
        {

                $family = User::where('id', 1)->get();
                return $this->respond($this->showCollection($family, new UserTransformer));
        }

        public function signup(Request $request)
        {
                $request->validate([
                        'name' => 'required|string',
                        'email' => 'required|string|email|unique:users',
                        'password' => 'required|string|confirmed'
                ]);
                $user = new User([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => bcrypt($request->password)
                ]);
                $user->save();
                return response()->json([
                        'message' => 'Successfully created user!'
                ], 201);
        }

        /**
         * Login user and create token
         *
         * @param  [string] email
         * @param  [string] password
         * @param  [boolean] remember_me
         * @return [string] access_token
         * @return [string] token_type
         * @return [string] expires_at
         */

        public function store(Request $request)
        {
                echo "getting here";
                $path = Storage::putFile('avatars', $request->file('uploaded_file'));
                return $path;
        }

        public function login(Request $request)
        {
                $request->validate([
                        'email' => 'required|string|email',
                        'password' => 'required|string',
                        'remember_me' => 'boolean'
                ]);
                $credentials = request(['email', 'password']);
                if (!Auth::attempt($credentials))
                        return response()->json([
                                'message' => 'Unauthorized'
                        ], 401);
                $user = $request->user();
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                if ($request->remember_me)
                        $token->expires_at = Carbon::now()->addWeeks(1);
                $token->save();
                return response()->json([
                        'access_token' => $tokenResult->accessToken,
                        'token_type' => 'Bearer',
                        'expires_at' => Carbon::parse(
                                $tokenResult->token->expires_at
                        )->toDateTimeString(),
                        'user_id' => $user->id,
                        'user_name' => $user->name
                ]);
        }

        /**
         * Logout user (Revoke the token)
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse [string] message
         */
        public function logout(Request $request)
        {
                $request->user()->token()->revoke();
                return response()->json([
                        'message' => 'Successfully logged out'
                ]);
        }

        /**
         * Get the authenticated User
         *
         * @return [json] user object
         */
        public function user(Request $request)
        {
                return response()->json($request->user());
        }

        public function users()
        {
                $users = User::all();
                return $this->respond($this->showCollection($users, new UserTransformer));
        }

        public function my_user($id)
        {
                $users = User::where('id', $id)->get();
                return $this->respond($this->showCollection($users, new UserTransformer));
        }
}
