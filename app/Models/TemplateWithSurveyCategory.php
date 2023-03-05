<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TemplateWithSurveyCategory extends Model
{

    protected $table = "template_with_survey_category";
    protected $fillable = [
        'survey_category_id  ',
        'template_id ',
        'created_at',
        'updated_at',
    ];

    const ACTIVE = 1;
    const INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getTopicTemplate($survey_template_id)
    {
        $query =  DB::table('template_with_survey_category as a')
            ->join('survey_categories as b', 'b.id', '=', 'a.survey_category_id')
            ->select(
                'a.id',
                'a.survey_category_id',
                'b.title',
            )
            ->where('a.template_id', $survey_template_id);
        return $query->get();
    }
}
