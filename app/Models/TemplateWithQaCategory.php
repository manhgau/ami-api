<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TemplateWithQaCategory extends Model
{

    protected $table = "template_with_qa_category";
    protected $fillable = [
        'qa_category_id ',
        'template_id ',
        'created_at',
        'updated_at',
    ];

    const ACTIVE = 1;
    const INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    public static  function getListTemplateByCategoryQa($perPage = 10,  $page = 1, $category_qa)
    {
        $query =  DB::table('template_with_qa_category as a')
            ->join('survey_templates as b', 'b.id', '=', 'a.template_id')
            ->select(
                'a.id',
                'a.qa_category_id',
                'a.template_id as survey_template_id',
                'b.title',
                'b.description',
                'b.thumbnail',
            )
            ->where('b.active', self::ACTIVE)
            ->where('a.qa_category_id', $category_qa)
            ->orderBy('b.created_at', 'desc');
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }
}
