<?php

namespace App\Models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;

class SurveyTemplate extends Model
{

    protected $fillable = [
        'title',
        'survey_id',
        'thumbnail',
        'description',
        'content',
        'active',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted',
    ];

    const ACTIVE = 1;
    const INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'updated_by', 'created_by'];

    public static  function getListSurveyTemplate($perPage = 10,  $page = 1, $category_id = null)
    {
        $query = self::where('deleted', self::NOT_DELETED)->where('active', self::ACTIVE)->orderBy('id', 'ASC');
        if ($category_id != null) {
            $query->where('category_id', $category_id);
        }

        $data =  $query->paginate($perPage, "*", "page", $page)->toArray();
        return $data;
    }

    public static  function getDetailSurveyTemplate($id)
    {
        return self::select('id as survey_template_id', 'title', 'survey_id', 'thumbnail', 'description', 'content')->where('deleted', self::NOT_DELETED)->where('id', $id)->where('active', self::ACTIVE)->first();
    }

    public static  function updateSurveyTemplate($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->update($data);
    }
    public function scopeGetSurveyTemplate()
    {
        return $this->where('deleted', self::NOT_DELETED);
    }

    public function surveyCategory()
    {
        return $this->belongsTo('App\Models\SurveyCategory', 'category_id', 'id');
    }

    public function userCreated()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }

    public static  function deleteAllSurveyTemplate($survey_id)
    {
        $query =  self::where('survey_id', $survey_id)->where('deleted', self::NOT_DELETED);
        return $query->delete();
    }
}
