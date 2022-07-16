<?php

namespace App\Http\Controllers\v1\client;
use Illuminate\Http\Request;

use App\Models\User;
use Validator;
use App\Helpers\ClientResponse;
use App\Jobs\SendActiveAcountEmailJob;
use App\Jobs\SendResetPasswordEmailJob;
use Illuminate\Support\Str;

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
            'username' => 'required',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return ClientResponse::responseError('Vui lòng nhập email và mật khẩu');
        }
        $fieldType = $this->__username();
        $token = auth()->attempt(array($fieldType => $request->username, 'password' => $request->password));
        if (! $token ) {
            return ClientResponse::responseError('Tài khoản hoặc mật khẩu không đúng');
        }
        $userData = auth()->user();

        if($userData->is_active!=User::IS_ACTIVE){
            return ClientResponse::response(ClientResponse::$user_not_active, 'Tài khoản chưa kích hoạt');
        }

        $user =  $this->createNewToken($token);
        return ClientResponse::responseSuccess('Đăng nhập thành công', $user);
    }

    private function __username(){
        $login = request()->input('username');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $login]);
        return $field;
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|min:6|max:100|unique:users|alpha_dash',
            'password' => 'required|string|confirmed|min:6',
        ]);
        if($validator->fails()){
            $errorString = implode(",",$validator->messages()->all());
            return ClientResponse::responseError( $errorString);
        }
        $active_code = Str::random(20);
        $active_expire = (time() + 86400);
        $user = User::create(array_merge(
                    $validator->validated(),
                    [
                        'password' => User::generatePasswordHash($request->password),
                        'active_code'  =>  $active_code,
                        'is_active'  =>  0,
                        'active_expire'  =>  $active_expire,
                    ]
                ));
        //gửi mail kích hoạt
        $web_link = env('FRONTEND_APP_URL');
        $app_name = env('APP_NAME');
        dispatch(new SendActiveAcountEmailJob([
            'to'    =>  $request->email,
            'active_link' => ''.$web_link.'/active-account?uid='.$user->id.'&active_code='.$active_code.'',
            'subject'   =>  'Kích hoạt tài khoản '.$app_name.' của bạn',
            'expire_time'   =>  date('H:i:s d/m/Y' ,$active_expire),
        ]));
        return ClientResponse::responseSuccess('Đăng ký tài khoản thành công', $user);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return ClientResponse::responseSuccess('Đăng xuất thành công');
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
            return ClientResponse::responseSuccess('Thông tin tài khoản', [
                [
                    'user'  =>  auth()->user()
                ]
            ]);
        }else{
            return ClientResponse::response(ClientResponse::$required_login_code, 'Unauthorized');
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
            'new_password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            $errorString = implode(",",$validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->update(
                    ['password' => User::generatePasswordHash($request->new_password)]
                );
        return ClientResponse::responseSuccess('Thay đổi mật khẩu thành công', $user);
    }

    public function activeByEmail(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'active_code' => 'required',
        ]);

        if($validator->fails()){
            $errorString = implode(",",$validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        $user_id = $request->user_id;
        $active_code = $request->active_code;

        $user = User::findUserActiveEmail($user_id, $active_code);
        if(!$user){
            return ClientResponse::responseError('Dữ liệu không hợp lệ');
        }
        if($user->active_expire < time()){
            return ClientResponse::responseError('Link kích hoạt đã hết hạn');
        }
        $user->is_active = User::IS_ACTIVE;
        $user->active_code = '';
        $user->active_expire = time();
        $user->save();

        return ClientResponse::responseSuccess('Kích hoạt tài khoản thành công');
    }

    public function resendActiveEmail(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if($validator->fails()){
            return ClientResponse::responseError('Vui lòng nhập email đúng định dạng');
        }
        $email = $request->email;
        $user = User::findUserByEmail($email);
        if($user){
            $active_code = Str::random(20);
            $active_expire = (time() + 86400);
            //
            $user->active_code = $active_code;
            $user->active_expire = $active_expire;
            if($user->save()){
                //gửi mail kích hoạt
                $web_link = env('FRONTEND_APP_URL');
                $app_name = env('APP_NAME');
                dispatch(new SendActiveAcountEmailJob([
                    'to'    =>  $request->email,
                    'active_link' => ''.$web_link.'/active-account?uid='.$user->id.'&active_code='.$active_code.'',
                    'subject'   =>  'Kích hoạt tài khoản '.$app_name.' của bạn',
                    'expire_time'   =>  date('H:i:s d/m/Y' ,$active_expire),
                ]));
                return ClientResponse::responseSuccess('Gửi email kích hoạt thành công, vui lòng xem hộp thư hoặc thư mục thư rác( spam) để kích hoạt tài khoản');
            }else{
                return ClientResponse::responseError('Không thể lưu dữ liệu');
            }
        }else{
            return ClientResponse::responseError('Email không khớp với tài khoản nào');
        }
    }

    public function forgotPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);
        $email = $request->email;
        if ($validator->fails()) {
            return ClientResponse::responseError('Vui lòng nhập địa chỉ email');
        }
        $user = User::findUserByEmail($email);
        if($user){
            $forgot_code = Str::random(20);
            $forgot_expire = (time() + 86400);
            //
            $user->forgot_code = $forgot_code;
            $user->forgot_expire = $forgot_expire;
            if($user->save()){
                //gửi mail reset password
                $web_link = env('FRONTEND_APP_URL');
                $app_name = env('APP_NAME');
                dispatch(new SendResetPasswordEmailJob([
                    'to'    =>  $request->email,
                    'active_link' => ''.$web_link.'/reset-password?uid='.$user->id.'&forgot_code='.$forgot_code.'',
                    'subject'   =>  'Thay đổi mật khẩu tài khoản '.$app_name.' của bạn',
                    'expire_time'   =>  date('H:i:s d/m/Y' ,$forgot_expire),
                    'fullname'  =>  $user->name,
                    'app_name'  =>  $app_name,
                ]));
                return ClientResponse::responseSuccess('Chúng tôi đã gửi cho bạn một email vào địa chỉ '.$email.', làm theo hướng dẫn trong email để thay đổi mật khẩu cho tài khoản của bạn');
            }else{
                return ClientResponse::responseError('Không thể lưu dữ liệu');
            }
        }else{
            return ClientResponse::responseError('Email không khớp với tài khoản nào');
        }
    }

    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'forgot_code' => 'required',
            'password' => 'required|string|confirmed|min:6',
        ]);
        $user_id = $request->user_id;
        $forgot_code = $request->forgot_code;
        if($validator->fails()){
            $errorString = implode(",",$validator->messages()->all());
            return ClientResponse::responseError( $errorString);
        }
        $user = User::findUserForgotPassByEmail($user_id, $forgot_code);
        if(!$user){
            return ClientResponse::responseError('Dữ liệu không hợp lệ');
        }
        if($user->forgot_expire < time()){
            return ClientResponse::responseError('Link thay đổi mật khẩu đã hết hạn');
        }
        $user->password = User::generatePasswordHash($request->password);
        $user->forgot_code = '';
        $user->forgot_expire = time();
        $user->save();

        return ClientResponse::responseSuccess('Đổi mật khẩu thành công');
    }
}
