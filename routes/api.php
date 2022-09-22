<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\client\AuthController;
use App\Http\Controllers\v1\partner\AuthController as PartnerAuthController;
use App\Http\Controllers\v1\client\ConfigController as  ClientConfigController;
use App\Http\Controllers\v1\client\ContactController;
use App\Http\Controllers\v1\client\SettingController;
use App\Http\Controllers\v1\client\SurveyCategoryController;
use App\Http\Controllers\v1\client\SurveyController;
use App\Http\Controllers\v1\client\SurveyPartnerInputAnynomousController;
use App\Http\Controllers\v1\client\SurveyPartnerInputLineAnynomousController;
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
use App\Http\Controllers\v1\partner\PackageController;
use App\Http\Controllers\v1\partner\PersonalIncomeLevelsController;
use App\Http\Controllers\v1\partner\SurveyPartnerController;
use App\Http\Controllers\v1\partner\SurveyPartnerInputController;
use App\Http\Controllers\v1\partner\SurveyPartnerInputLineController;
use App\Http\Controllers\v1\partner\SurveyQuestionPartnerController;
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
        //
        Route::get('/settings', [ClientConfigController::class, 'settings']);
        Route::get('/info', [SettingController::class, 'getInfo']);
        Route::post('contact', [ContactController::class, 'addContact']);
        //auth
        Route::group([
            'prefix' => 'auth'

        ], function ($router) {
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/active-by-email', [AuthController::class, 'activeByEmail']);
            Route::post('/resend-active-email', [AuthController::class, 'resendActiveEmail']);
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);

            Route::group([
                'middleware' => 'client_auth',

            ], function ($router) {
                Route::post('/logout', [AuthController::class, 'logout']);
                Route::get('/profile', [AuthController::class, 'profile']);
                Route::post('/change-pass', [AuthController::class, 'changePassWord']);
            });
        });
        Route::group([
            'prefix' => 'survey'

        ], function ($router) {
            Route::get('/category', [SurveyCategoryController::class, 'getListSurveyCategory']);
            Route::get('/question-type', [SurveyController::class, 'getQuestionType']);
            Route::get('/get-statistic/{survey_id}/question/{question_id}', [SurveyStatisticCpntroller::class, 'getSurveyStatisticDetail']);
            Route::get('/get-statistic/{survey_id}/target/{group_by}', [SurveyStatisticCpntroller::class, 'getSurveyStatistic']);
            Route::group([
                'prefix' => 'anynomous'
            ], function ($router) {
                Route::post('/input/{survey_id}', [SurveyPartnerInputAnynomousController::class, 'answerSurveyAnynomous']);
                Route::put('/input/{survey_id}/edit/{partner_input_id}', [SurveyPartnerInputAnynomousController::class, 'updateAnswerSurveyAnynomous']);
                Route::post('/input/{survey_id}/line/{partner_input_id}/question/{question_id}', [SurveyPartnerInputLineAnynomousController::class, 'surveyPartnerInputLineAnynomous']);
            });
            Route::group([
                'middleware' => 'client_auth',
            ], function ($router) {
                Route::post('/create', [SurveyController::class, 'createSurvey']);
                Route::get('/get-list', [SurveyController::class, 'getListSurvey']);
                Route::get('/get-detail/{survey_id}', [SurveyController::class, 'getDetailSurvey']);
                Route::get('/template/get-list', [SurveyTemplateController::class, 'getListSurveyTemplate']);
                Route::post('/template/update-logo/{template_id}', [SurveyTemplateController::class, 'updateLogoTemplate']);
                Route::get('/template/get-detail/{survey_template_id}', [SurveyTemplateController::class, 'getDetailSurveyTemplate']);
                Route::post('/use-template/{survey_template_id}', [SurveyController::class, 'useSurveyTemplate']);
                //Route::get('/get-statistic/{survey_id}/question/{question_id}', [SurveyStatisticCpntroller::class, 'getSurveyStatisticDetail']);
                //            Route::get('/get-statistic/{survey_id}/question/{question_id}', [SurveyStatisticCpntroller::class, 'getSurveyStatistic']);
                Route::group([
                    'middleware' => 'client_owner_survey',

                ], function ($router) {
                    Route::put('/edit/{id}', [SurveyController::class, 'editSurvey']);
                    Route::delete('/del/{id}', [SurveyController::class, 'deleteSurvey']);
                    Route::post('/question/{survey_id}', [SurveyQuestionController::class, 'createSurveyQuestion']);
                    Route::get('/question/{survey_id}', [SurveyQuestionController::class, 'getListSurveyQuestion']);
                    Route::get('/question/{survey_id}/detail/{question_id}', [SurveyQuestionController::class, 'getDetailSurveyQuestion']);
                    Route::put('/question/{survey_id}', [SurveyQuestionController::class, 'updateManySurveyQuestion']);
                    Route::put('/question/{survey_id}/edit/{question_id}', [SurveyQuestionController::class, 'updateSurveyQuestion']);
                    Route::put('/question-answer/{survey_id}/edit/{answer_id}', [SurveyQuestionController::class, 'updateSurveyQuestionAnswer']);
                    Route::delete('/question/{survey_id}/del/{question_id}', [SurveyQuestionController::class, 'delSurveyQuestion']);
                });
            });
        });
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
    //END client (web)

    //partner (app)
    Route::group([
        'prefix' => 'partner'
    ], function ($router) {
        Route::get('/settings', [PartnerConfigController::class, 'settings']);
        //auth
        Route::group([
            'prefix' => 'auth'

        ], function ($router) {
            Route::post('/login', [PartnerAuthController::class, 'login']);
            Route::post('/refresh', [PartnerAuthController::class, 'refresh']);
            Route::post('/check-register', [PartnerAuthController::class, 'checkRegister']);
            Route::post('/register', [PartnerAuthController::class, 'register']);
            Route::post('/forgot-password', [PartnerAuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [PartnerAuthController::class, 'resetPassword']);

            Route::group([
                'middleware' => 'partner_auth',

            ], function ($router) {
                Route::post('/logout', [PartnerAuthController::class, 'logout']);
                Route::get('/profile', [PartnerAuthController::class, 'profile']);
                Route::post('/update-profile', [PartnerAuthController::class, 'updateProfile']);
                Route::post('/change-password', [PartnerAuthController::class, 'changePassWord']);
                Route::post('/mapping-uid-fcmtoken', [MappingUidFcmTokenController::class, 'mappingUidFcmToken']);
            });
        });

        Route::group([
            'prefix' => 'survey'


        ], function ($router) {
            Route::group([
                'middleware' => 'partner_auth',

            ], function ($router) {
                Route::get('/', [SurveyPartnerController::class, 'getlistSurveyPartner']);
                Route::get('/input', [SurveyPartnerInputController::class, 'getlistSurveyPartnerInput']);
                Route::get('/question/{survey_id}', [SurveyQuestionPartnerController::class, 'getSurveyQuestion']);
                Route::post('/input/{survey_id}', [SurveyPartnerInputController::class, 'answerSurvey']);
                Route::put('/input/{survey_id}/edit/{partner_input_id}', [SurveyPartnerInputController::class, 'updateAnswerSurvey']);
                Route::post('/input/{survey_id}/line/{partner_input_id}/question/{question_id}', [SurveyPartnerInputLineController::class, 'surveyPartnerInputLine']);
            });
        });

        //end auth
        //required login
        Route::group([
            'middleware' => 'partner_auth',

        ], function ($router) {
        });
    });
    //END partner (app)

    //visitor (web)
    Route::group([
        'prefix' => 'visitor'
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
    //END visitor (web)

    //client (app)
    Route::group([
        'prefix' => 'client-app'
    ], function ($router) {
    });
    //END client (app)
});
