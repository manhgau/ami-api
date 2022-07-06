<?php

namespace App\Http\Controllers\v1\client;
use Illuminate\Http\Request;

use App\Models\User;
use Validator;
use App\Helpers\ClientResponse;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    /*public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }*/

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return ClientResponse::responseError('Vui lòng nhập email và mật khẩu',$validator->errors());
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return ClientResponse::responseError('Email hoặc mật khẩu không đúng');
        }

        $user =  $this->createNewToken($token);
        return ClientResponse::responseSuccess('Đăng nhập thành công', $user);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return ClientResponse::responseError('Input invalid', $validator->errors());
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        return ClientResponse::responseSuccess('User successfully registered', $user);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return ClientResponse::responseSuccess('User successfully signed out');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        if(!auth()->check()) {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Unauthorized');
        }

        return ClientResponse::responseSuccess('Refresh token success', [
            $this->createNewToken(auth()->refresh())
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        if(auth()->check()) {
            return ClientResponse::responseSuccess('User successfully registered', [
                auth()->user()
            ]);
        }else{
            return ClientResponse::responseError('Unauthorized');
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ];
    }

    public function changePassWord(Request $request) {
        if(!auth()->check()) {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Unauthorized');
        }
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return ClientResponse::responseError('Vui lòng nhập email và mật khẩu',$validator->errors());
        }
        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->update(
                    ['password' => bcrypt($request->new_password)]
                );
        return ClientResponse::responseSuccess('User successfully changed password', $user);
    }
}
