<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\Common\CFunction;
use App\Helpers\Context;
use App\Helpers\JWTClient;
use Illuminate\Http\Request;

use App\Models\User;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\FtpSv;
use App\Jobs\SendActiveAcountEmailJob;
use App\Jobs\SendResetPasswordEmailJob;
use App\Models\AppSetting;
use App\Models\Survey;
use App\Models\UserPackage;
use Illuminate\Support\Str;
use App\Models\UserRefreshToken;
use Carbon\Carbon;
use DB;

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
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ClientResponse::responseError('Vui lòng nhập email và mật khẩu');
        }
        $email = $request->email;
        $password = $request->password;
        $user = User::loginAttempByEmail($email, $password);
        if ($user) {
            if ($user->is_active != User::IS_ACTIVE) {
                return ClientResponse::response(ClientResponse::$user_not_active, 'Tài khoản chưa kích hoạt');
            }
            //tạo access và refresh token mới
            $token = UserRefreshToken::generateAccessRefreshToken($user->id);
            if ($token) {
                $data = [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name ?? '',
                        'email' => $user->email ?? '',
                    ],
                    'access_token'  => $token['access_token'] ?? '',
                    'refresh_token' => $token['refresh_token'] ?? '',
                ];
                return ClientResponse::responseSuccess('Đăng nhập thành công', $data);
            } else {
                return ClientResponse::responseError('Đã có lỗi xảy ra, vui lòng thử lại sau');
            }
        } else {
            return ClientResponse::responseError('Email hoặc mật khẩu không đúng');
        }
    }

    private function __username()
    {
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
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        $active_code = Str::random(20);
        $active_expire = (time() + 86400);
        try {
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
            $web_link = CFunction::getFrontendWebUrl();
            $app_name = env('APP_NAME');
            dispatch(new SendActiveAcountEmailJob([
                'to'    =>  $request->email,
                'active_link' => '' . $web_link . '/active-account?uid=' . $user->id . '&active_code=' . $active_code . '',
                'subject'   =>  'Kích hoạt tài khoản ' . $app_name . ' của bạn',
                'expire_time'   =>  date('H:i:s d/m/Y', $active_expire),
            ]));
            return ClientResponse::responseSuccess('Đăng ký tài khoản thành công. Chúng tôi đã gửi cho bạn một email vào địa chỉ ' . $request->email . ', làm theo hướng dẫn trong email để kích hoạt tài khoản');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        return ClientResponse::responseSuccess('Đăng xuất thành công');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        $token = $request->header('Authorization');
        $refresh_token = JWTClient::checkAccessToken($token);
        if ($refresh_token) {
            $access_token_id = $refresh_token->aid ?? 0;
            $type = $refresh_token->type ?? '';
            $tokenInfo = UserRefreshToken::where('aid', $access_token_id)->first();
            if ($tokenInfo && $type == UserRefreshToken::TYPE_REFRESH_TOKEN) {
                $user = $tokenInfo->user;
                if ($user) {
                    $time = time();

                    $refresh_token_expire = $tokenInfo->refresh_expire ?? 0;
                    if ($refresh_token_expire >= $time) {
                        //
                        DB::beginTransaction();
                        try {
                            //tạo access và refresh token mới
                            $token = UserRefreshToken::generateAccessRefreshToken($user->id);
                            if ($token) {
                                $data = [
                                    /*'user' => [
                                        'id' => $user->id,
                                        'name' => $user->name ?? '',
                                        'phone' => $user->email,
                                    ],*/
                                    'access_token'  => $token['access_token'] ?? '',
                                    'refresh_token' => $token['refresh_token'] ?? '',
                                ];
                                DB::commit();
                                return ClientResponse::responseSuccess('Refresh thành công', $data);
                            } else {
                                return ClientResponse::responseError('Đã có lỗi xảy ra, vui lòng thử lại sau');
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return ClientResponse::responseError($e->getMessage());
                        }
                    } else {
                        return ClientResponse::response(ClientResponse::$required_login_code, 'Refresh token đã hết hạn');
                    }
                } else {
                    return ClientResponse::responseError('Tài khoản không tồn tại');
                }
            } else {
                return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Yêu cầu truy cập bị từ chối');
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
        $user = User::find($user_id);
        $all_settings = AppSetting::getAllSetting();
        $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
        if (!$user['logo']) {
            $logo  = AppSetting::getByKey(AppSetting::LOGO, $all_settings);
            $user['logo'] = $logo;
        }
        $user['logo'] = $image_domain . $user['logo'];
        $user['avatar'] = $image_domain . $user['avatar'];
        $time_now = Carbon::now();
        $user_package = UserPackage::getPackageUser($user_id, $time_now);
        $user_package['number_of_projects']  = Survey::countSurvey($user_id);
        $data = [
            'user_package' => $user_package,
            'user_profile' => $user,
        ];
        return ClientResponse::responseSuccess('Thông tin tài khoản', $data);
    }


    public function changePassWord(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }

        $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
        $user = User::find($user_id);
        if ($user) {
            $userId = $user->id;
            User::where('id', $userId)->update(
                ['password' => User::generatePasswordHash($request->new_password)]
            );
            return ClientResponse::responseSuccess('Thay đổi mật khẩu thành công');
        } else {
            return ClientResponse::responseError('Tài khoản không tồn tại');
        }
    }

    public function activeByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'active_code' => 'required',
        ]);

        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        $user_id = $request->user_id;
        $active_code = $request->active_code;

        $user = User::findUserActiveEmail($user_id, $active_code);
        if (!$user) {
            return ClientResponse::responseError('Dữ liệu không hợp lệ');
        }
        if ($user->active_expire < time()) {
            return ClientResponse::responseError('Link kích hoạt đã hết hạn');
        }
        $user->is_active = User::IS_ACTIVE;
        $user->active_code = '';
        $user->active_expire = time();
        $user->save();

        return ClientResponse::responseSuccess('Kích hoạt tài khoản thành công');
    }

    public function resendActiveEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ClientResponse::responseError('Vui lòng nhập email đúng định dạng');
        }
        $email = $request->email;
        $user = User::findUserByEmail($email);
        if ($user) {
            $active_code = Str::random(20);
            $active_expire = (time() + 86400);
            //
            $user->active_code = $active_code;
            $user->active_expire = $active_expire;
            if ($user->save()) {
                //gửi mail kích hoạt
                $web_link = env('FRONTEND_APP_URL');
                $app_name = env('APP_NAME');
                dispatch(new SendActiveAcountEmailJob([
                    'to'    =>  $request->email,
                    'active_link' => '' . $web_link . '/active-account?uid=' . $user->id . '&active_code=' . $active_code . '',
                    'subject'   =>  'Kích hoạt tài khoản ' . $app_name . ' của bạn',
                    'expire_time'   =>  date('H:i:s d/m/Y', $active_expire),
                ]));
                return ClientResponse::responseSuccess('Gửi email kích hoạt thành công, vui lòng xem hộp thư hoặc thư mục thư rác( spam) để kích hoạt tài khoản');
            } else {
                return ClientResponse::responseError('Không thể lưu dữ liệu');
            }
        } else {
            return ClientResponse::responseError('Email không khớp với tài khoản nào');
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);
        $email = $request->email;
        if ($validator->fails()) {
            return ClientResponse::responseError('Vui lòng nhập địa chỉ email');
        }
        $user = User::findUserByEmail($email);
        if ($user) {
            $forgot_code = Str::random(20);
            $forgot_expire = (time() + 86400);
            //
            $user->forgot_code = $forgot_code;
            $user->forgot_expire = $forgot_expire;
            if ($user->save()) {
                //gửi mail reset password
                $web_link = $web_link = CFunction::getFrontendWebUrl();
                $app_name = env('APP_NAME');
                dispatch(new SendResetPasswordEmailJob([
                    'to'    =>  $request->email,
                    'active_link' => '' . $web_link . '/reset-password?uid=' . $user->id . '&forgot_code=' . $forgot_code . '',
                    'subject'   =>  'Thay đổi mật khẩu tài khoản ' . $app_name . ' của bạn',
                    'expire_time'   =>  date('H:i:s d/m/Y', $forgot_expire),
                    'fullname'  =>  $user->name,
                    'app_name'  =>  $app_name,
                ]));
                return ClientResponse::responseSuccess('Chúng tôi đã gửi cho bạn một email vào địa chỉ ' . $email . ', làm theo hướng dẫn trong email để thay đổi mật khẩu cho tài khoản của bạn');
            } else {
                return ClientResponse::responseError('Không thể lưu dữ liệu');
            }
        } else {
            return ClientResponse::responseError('Email không khớp với tài khoản nào');
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'forgot_code' => 'required',
            'password' => 'required|string|confirmed|min:6',
        ]);
        $user_id = $request->user_id;
        $forgot_code = $request->forgot_code;
        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        $user = User::findUserForgotPassByEmail($user_id, $forgot_code);
        if (!$user) {
            return ClientResponse::responseError('Link thay đổi mật khẩu đã hết hạn');
        }
        if ($user->forgot_expire < time()) {
            return ClientResponse::responseError('Link thay đổi mật khẩu đã hết hạn');
        }
        $user->password = User::generatePasswordHash($request->password);
        $user->forgot_code = '';
        $user->forgot_expire = time();
        $user->save();

        return ClientResponse::responseSuccess('Đổi mật khẩu thành công');
    }

    public function updateImage(Request $request)
    {
        try {
            $type_image = $request->type_image;
            if (User::checkImageValid($type_image) === false) {
                return ClientResponse::responseError('Lỗi ! Không có loại ảnh này.');
            }
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            if ($file = $request->file('image')) {
                $name =   md5($file->getClientOriginalName() . rand(1, 9999)) . '.' . $file->extension();
                if ($type_image == User::LOGO) {
                    $time_now = Carbon::now();
                    $user_package = UserPackage::getPackageUser($user_id, $time_now);
                    if ($user_package['add_logo']) {
                        $path = env('FTP_PATH') . FtpSv::LOGO_FOLDER;
                        $image = FtpSv::upload($file, $name, $path, FtpSv::LOGO_FOLDER);
                        $update_image = User::updateProfile([User::LOGO => $image], $user_id);
                        if (!$update_image) {
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                    }
                    return ClientResponse::response(ClientResponse::$add_logo, 'Bạn không có quyền thêm logo, Vui lòng đăng ký gói cước để sử dụng chứ năng này');
                } else {
                    $path = env('FTP_PATH') . FtpSv::AVATAR_FOLDER;
                    $image = FtpSv::upload($file, $name, $path, FtpSv::AVATAR_FOLDER);
                    $update_image = User::updateProfile([User::AVATAR => $image], $user_id);
                    if (!$update_image) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                }
                $all_settings = AppSetting::getAllSetting();
                $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
                return ClientResponse::responseSuccess('OK', $image_domain .  $image);
            }
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
