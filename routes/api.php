<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\client\AuthController;
use App\Http\Controllers\v1\partner\AuthController as PartnerAuthController;
use App\Http\Controllers\v1\client\ConfigController as  ClientConfigController;
use App\Http\Controllers\v1\client\ContactController;
use App\Http\Controllers\v1\client\ImagesController;
use App\Http\Controllers\v1\client\SettingController;
use App\Http\Controllers\v1\client\SubscribesController;
use App\Http\Controllers\v1\client\SurveyCategoryController;
use App\Http\Controllers\v1\client\SurveyController;
use App\Http\Controllers\v1\client\SurveyPartnerInputAnynomousController;
use App\Http\Controllers\v1\client\SurveyPartnerInputLineAnynomousController;
use App\Http\Controllers\v1\client\SurveyQuestionAnswersController;
use App\Http\Controllers\v1\client\SurveyQuestionController;
use App\Http\Controllers\v1\client\SurveyStatisticCpntroller;
use App\Http\Controllers\v1\client\SurveyTemplateController;
use App\Http\Controllers\v1\partner\MappingUidFcmTokenController;
use App\Http\Controllers\v1\partner\AcademicLevelCotroller;
use App\Http\Controllers\v1\partner\ConfigController as PartnerConfigController;
use App\Http\Controllers\v1\partner\DistrictController;
use App\Http\Controllers\v1\partner\JobStatusController;
use App\Http\Controllers\v1\partner\JobTypeCotroller;
use App\Http\Controllers\v1\partner\ProvinceController as PartnerProvinceController;
use App\Http\Controllers\v1\partner\WardController;
use App\Http\Controllers\v1\visitor\BlogCategoryController;
use App\Http\Controllers\v1\visitor\BlogController;
use App\Http\Controllers\v1\visitor\PageController;
use App\Http\Controllers\v1\visitor\QAndACategoryController;
use App\Http\Controllers\v1\visitor\QAndAController;
use App\Http\Controllers\v1\common\ToolsController;
use App\Http\Controllers\v1\partner\BusinessScopeCotroller;
use App\Http\Controllers\v1\partner\ChildrenAgeRangesController;
use App\Http\Controllers\v1\partner\FamilyIncomeLevelsController;
use App\Http\Controllers\v1\partner\GendersController;
use App\Http\Controllers\v1\partner\NotificationsFirebasePartnerController;
use App\Http\Controllers\v1\partner\NumberOfFamilyController;
use App\Http\Controllers\v1\partner\PackageController;
use App\Http\Controllers\v1\partner\PersonalIncomeLevelsController;
use App\Http\Controllers\v1\partner\SurveyPartnerController;
use App\Http\Controllers\v1\partner\SurveyPartnerInputController;
use App\Http\Controllers\v1\partner\SurveyPartnerInputLineController;
use App\Http\Controllers\v1\partner\SurveyQuestionPartnerController;
use App\Http\Controllers\v1\partner\SurveyQuestionProfileController;
use App\Http\Controllers\v1\visitor\FeedbackController;
use App\Http\Controllers\v1\visitor\PartnerContactsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//
Route::group(['prefix' => 'v1'], function () {
    //common
    Route::group([
        'prefix' => 'common'
    ], function ($router) {
        //clear cache, config cache
        Route::post('/clear-config-cache', [ToolsController::class, 'clearConfigCache']);
        Route::post('/delete-cache', [ToolsController::class, 'deleteCache']);
    });
    //END common

    //client (web)
    Route::group([
        'prefix' => 'client'

    ], function ($router) {
        Route::group([
            'middleware' => 'client_log_request',

        ], function ($router) {
            //
            Route::get('/settings', [ClientConfigController::class, 'settings']);
            Route::get('/info', [SettingController::class, 'getInfo']);
            Route::post('contact', [ContactController::class, 'addContact']);
            Route::post('subscribe', [SubscribesController::class, 'addSubscribes']);
            //auth
            Route::group([
                'prefix' => 'auth'

            ], function ($router) {
                Route::post('/login', [AuthController::class, 'login']);
                Route::post('/logout', [AuthController::class, 'logout']);
                Route::post('/refresh', [AuthController::class, 'refresh']);
                Route::post('/register', [AuthController::class, 'register']);
                Route::post('/active-by-email', [AuthController::class, 'activeByEmail']);
                Route::post('/resend-active-email', [AuthController::class, 'resendActiveEmail']);
                Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
                Route::post('/reset-password', [AuthController::class, 'resetPassword']);

                Route::group([
                    'middleware' => 'client_auth',

                ], function ($router) {
                    Route::get('/profile', [AuthController::class, 'profile']);
                    Route::post('/change-pass', [AuthController::class, 'changePassWord']);
                    Route::post('/upload-image/{type_image}', [AuthController::class, 'updateImage']);
                });
            });

            Route::group([
                'prefix' => 'survey'

            ], function ($router) {
                Route::get('/category', [SurveyCategoryController::class, 'getListSurveyCategory']);
                Route::get('/question-type', [SurveyController::class, 'getQuestionType']);
                Route::get('/format-date/type', [SurveyController::class, 'getFormatDateType']);
                Route::post('/detail/{survey_id}', [SurveyStatisticCpntroller::class, 'getSurveyDetail']);
                Route::group([
                    'middleware' => 'client_auth',
                ], function ($router) {
                    Route::post('/create', [SurveyController::class, 'createSurvey']);
                    Route::get('/get-list', [SurveyController::class, 'getListSurvey']);
                    Route::get('/get-detail/{survey_id}', [SurveyController::class, 'getDetailSurvey']);
                    Route::post('/question/upload-image', [ImagesController::class, 'uploadImage']);
                    Route::get('/question/template-image', [ImagesController::class, 'getTemplateImage']);
                    Route::group([
                        'prefix' => '/template'
                    ], function ($router) {
                        Route::get('/', [SurveyTemplateController::class, 'getListSurveyTemplate']);
                        Route::get('/{survey_template_id}', [SurveyTemplateController::class, 'getDetailSurveyTemplate']);
                        Route::post('/{survey_template_id}/use-template', [SurveyController::class, 'useSurveyTemplate']);
                    });
                    Route::group([
                        'middleware' => 'client_owner_survey',

                    ], function ($router) {
                        Route::post('/edit/{survey_id}', [SurveyController::class, 'editSurvey']);
                        Route::post('/delete/{survey_id}', [SurveyController::class, 'deleteSurvey']);
                        Route::group([
                            'prefix' => '/{survey_id}/question'

                        ], function ($router) {
                            Route::post('/', [SurveyQuestionController::class, 'createSurveyQuestion']);
                            Route::get('/', [SurveyQuestionController::class, 'getListSurveyQuestion']);
                            Route::get('/{question_id}/detail', [SurveyQuestionController::class, 'getDetailSurveyQuestion']);
                            Route::post('/update-many', [SurveyQuestionController::class, 'updateManySurveyQuestion']);
                            Route::post('/{question_id}/update', [SurveyQuestionController::class, 'updateSurveyQuestion']);
                            Route::post('/{question_id}/delete', [SurveyQuestionController::class, 'delSurveyQuestion']);
                            Route::group([
                                'prefix' => '/{question_id}/answers'

                            ], function ($router) {
                                Route::post('/', [SurveyQuestionAnswersController::class, 'creatQuestionAnswers']);
                                Route::post('/dropdown', [SurveyQuestionAnswersController::class, 'creatQuestionAnswersDropdown']);
                                Route::post('/{answer_id}/update', [SurveyQuestionAnswersController::class, 'updateQuestionAnswers']);
                            });
                        });
                    });
                });
                Route::group([
                    'prefix' => '/{survey_id}/statistic'
                ], function ($router) {
                    Route::post('/', [SurveyStatisticCpntroller::class, 'getStatisticSurvey']);
                    Route::post('/diagram/target/{group_by}', [SurveyStatisticCpntroller::class, 'getDiagramSurvey']);
                    Route::post('/question/{question_id}', [SurveyStatisticCpntroller::class, 'getSurveyStatisticDetail']);
                });
                Route::group([
                    'prefix' => '/{survey_id}/anynomous'
                ], function ($router) {
                    Route::post('/', [SurveyPartnerInputAnynomousController::class, 'answerSurveyAnynomous']);
                    Route::post('/{partner_input_id}/update', [SurveyPartnerInputAnynomousController::class, 'updateAnswerSurveyAnynomous']);
                    Route::post('line/{partner_input_id}/question/{question_id}', [SurveyPartnerInputLineAnynomousController::class, 'surveyPartnerInputLineAnynomous']);
                    Route::get('/detail/{survey_partner_id}', [SurveyPartnerInputAnynomousController::class, 'getDetailSurveyPartner']);
                    Route::get('/question', [SurveyQuestionPartnerController::class, 'getSurveyQuestion']);
                });
            });
            //package
            Route::get('package', [PackageController::class, 'getListPackage']);
            Route::get('package/{id}', [PackageController::class, 'getDetailPackage']);
            //

            //END auth
            //required login
            Route::group([
                'middleware' => 'client_auth',

            ], function ($router) {
            });
        });
    });
    //END client (web)

    //partner (app)
    Route::group([
        'prefix' => 'partner'
    ], function ($router) {
        Route::get('/settings', [PartnerConfigController::class, 'settings']);
        Route::get('province', [PartnerProvinceController::class, 'getProvince']);
        Route::get('district/{province_code}', [DistrictController::class, 'getDistrict']);
        Route::get('ward/{district_code}', [WardController::class, 'getWard']);
        //
        Route::get('job-status', [JobStatusController::class, 'getJobStatus']);
        Route::get('job-type', [JobTypeCotroller::class, 'getJobType']);
        Route::get('business-scope', [BusinessScopeCotroller::class, 'getBusinessScope']);
        Route::get('academic-level', [AcademicLevelCotroller::class, 'getAcademicLevel']);
        //
        Route::get('family-income-level', [FamilyIncomeLevelsController::class, 'getFamilyIncomeLevels']);
        Route::get('children-age-range', [ChildrenAgeRangesController::class, 'getChildrenAgeRanges']);
        Route::get('personal-income-level', [PersonalIncomeLevelsController::class, 'getPersonalIncomeLevels']);
        Route::get('gender', [GendersController::class, 'getGenders']);
        Route::get('family-people', [NumberOfFamilyController::class, 'getFamilyPeople']);
        //auth
        Route::group([
            'prefix' => 'auth'

        ], function ($router) {
            Route::post('/login', [PartnerAuthController::class, 'login']);
            Route::post('/logout', [PartnerAuthController::class, 'logout']);
            Route::post('/refresh', [PartnerAuthController::class, 'refresh']);
            Route::post('/check-register', [PartnerAuthController::class, 'checkRegister']);
            Route::post('/register', [PartnerAuthController::class, 'register']);
            Route::post('/forgot-password', [PartnerAuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [PartnerAuthController::class, 'resetPassword']);

            Route::group([
                'middleware' => 'partner_auth',

            ], function ($router) {
                Route::get('/profile', [PartnerAuthController::class, 'profile']);
                Route::post('/update-profile', [PartnerAuthController::class, 'updateProfile']);
                Route::post('/change-password', [PartnerAuthController::class, 'changePassWord']);
                Route::post('/mapping-uid-fcmtoken', [MappingUidFcmTokenController::class, 'mappingUidFcmToken']);
            });
        });

        Route::group([
            'prefix' => 'survey'
        ], function ($router) {
            Route::get('/question-type', [SurveyController::class, 'getQuestionType']);
            Route::group([
                'middleware' => 'partner_auth',

            ], function ($router) {
                Route::get('/{survey_profile_id}/profile/question', [SurveyQuestionProfileController::class, 'getSurveyQuestionProfile']);
                Route::post('/{survey_profile_id}/profile/question/{question_id}', [SurveyQuestionProfileController::class, 'answerSurveyQuestionProfile']);
                Route::group([
                    'middleware' => 'partner_profile',

                ], function ($router) {
                    Route::get('/get-list', [SurveyPartnerController::class, 'getlistSurveyPartner']);
                    Route::get('/get-detail/{survey_partner_id}', [SurveyPartnerController::class, 'getDetailSurveyPartner']);
                    Route::get('/save/{survey_partner_id}', [SurveyPartnerController::class, 'saveSurveyPartner']);
                    Route::get('/input', [SurveyPartnerInputController::class, 'getlistSurveyPartnerInput']);
                    Route::get('/input/{survey_partner_input_id}', [SurveyPartnerInputController::class, 'getDetailSurveyPartnerInput']);
                    Route::get('{survey_id}/input/check', [SurveyPartnerInputController::class, 'checkPartnerInput']);
                    Route::group([
                        'prefix' => '/{survey_id}/input'
                    ], function ($router) {
                        Route::get('/question', [SurveyQuestionPartnerController::class, 'getSurveyQuestion']);
                        Route::get('/question/profile', [SurveyQuestionProfileController::class, 'getQuestionProfileBySurvey']);
                        Route::post('/question/profile/{question_id}', [SurveyQuestionProfileController::class, 'answerQuestionProfileBySurvey']);
                        Route::post('/', [SurveyPartnerInputController::class, 'answerSurvey']);
                        Route::post('/{partner_input_id}/update', [SurveyPartnerInputController::class, 'updateAnswerSurvey']);
                        Route::post('/{partner_input_id}/question/{question_id}', [SurveyPartnerInputLineController::class, 'surveyPartnerInputLine']);
                        Route::get('/question/{question_id}/exit', [SurveyPartnerInputLineController::class, 'exitSurvey']);
                    });
                });
            });
        });

        //end auth
        //required login
        Route::group([
            'middleware' => 'partner_auth',

        ], function ($router) {
            Route::group([
                'prefix' => 'notification',

            ], function ($router) {
                Route::group([
                    'middleware' => 'partner_profile',

                ], function ($router) {
                    Route::get('/', [NotificationsFirebasePartnerController::class, 'getListNotificationPartner']);
                    Route::get('/type', [NotificationsFirebasePartnerController::class, 'getNotficationType']);
                    Route::get('/{notification_partner_id}', [NotificationsFirebasePartnerController::class, 'getDetailNotificationPartner']);
                });
            });
        });
    });
    //END partner (app)

    //visitor (web)
    Route::group([
        'prefix' => 'visitor'
    ], function ($router) {
        Route::group([
            'middleware' => 'client_log_request',

        ], function ($router) {
            Route::get('feedback', [FeedbackController::class, 'getList']);
            Route::group([
                'prefix' => 'blog'

            ], function ($router) {
                Route::get('blog-category', [BlogCategoryController::class, 'getAll']);
                Route::get('blog-category/{id}', [BlogCategoryController::class, 'getDetail']);
                Route::get('get-list', [BlogController::class, 'getAll']);
                Route::get('blog-relate/{slug}', [BlogController::class, 'getBlogRelate']);
                Route::get('get-detail/{slug}', [BlogController::class, 'getDetail']);
            });
            Route::group([
                'prefix' => 'qa'

            ], function ($router) {
                Route::get('qa-category', [QAndACategoryController::class, 'getAll']);
                Route::get('qa-category/{id}', [QAndACategoryController::class, 'getDetail']);
                Route::get('get-list', [QAndAController::class, 'getAll']);
                Route::get('qa-relate/{slug}', [QAndAController::class, 'getQAndARelate']);
                Route::get('get-detail/{slug}', [QAndAController::class, 'getDetail']);
            });
            Route::group([
                'prefix' => 'page'

            ], function ($router) {
                Route::get('get-list', [PageController::class, 'getAll']);
                Route::get('get-detail/{slug}', [PageController::class, 'getDetail']);
            });
            Route::group([
                'prefix' => 'partner-contact'

            ], function ($router) {
                Route::post('create', [PartnerContactsController::class, 'createPartnerContact']);
            });
        });
    });
    //END visitor (web)

    //client (app)
    Route::group([
        'prefix' => 'client-app'
    ], function ($router) {
        Route::group([
            'middleware' => 'partner_log_request',

        ], function ($router) {
        });
    });
    //END client (app)
});
