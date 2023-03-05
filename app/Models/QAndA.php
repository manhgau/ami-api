<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QAndA extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'category_id',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted',
        'thumbnail'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getALL($perPage = 10, $page = 1,  $category_id = null)
    {
        $q_and_a_s =  DB::table('q_and_a_s')
            ->join('q_and_a_categories', 'q_and_a_categories.id', '=', 'q_and_a_s.category_id')
            ->select('q_and_a_s.id', 'q_and_a_s.title', 'q_and_a_s.slug', 'q_and_a_s.content', 'q_and_a_s.category_id', 'q_and_a_s.thumbnail', 'q_and_a_categories.title as category_name')->where('q_and_a_s.deleted', self::NOT_DELETED)->where('q_and_a_s.status', self::STATUS_ACTIVE)->orderBy('q_and_a_s.id', 'desc');
        if ($category_id != null) {
            $q_and_a_s->where('q_and_a_s.category_id', $category_id);
        }
        $datas = $q_and_a_s->paginate($perPage, "*", "page", $page)->toArray();
        return $datas;
    }

    public static  function getQAndARelate($perPage = 10, $page = 1,  $category_id, $slug)
    {
        $q_and_a_s =  DB::table('q_and_a_s')
            ->join('q_and_a_categories', 'q_and_a_categories.id', '=', 'q_and_a_s.category_id')
            ->select('q_and_a_s.id', 'q_and_a_s.title', 'q_and_a_s.slug', 'q_and_a_s.content', 'q_and_a_s.category_id', 'q_and_a_s.thumbnail', 'q_and_a_categories.title as category_name')->where('q_and_a_s.deleted', self::NOT_DELETED)->where('q_and_a_s.status', self::STATUS_ACTIVE)->orderBy('q_and_a_s.id', 'desc')
            ->where('q_and_a_s.category_id', $category_id)->where('q_and_a_s.slug', '<>', $slug);
        $datas = $q_and_a_s->paginate($perPage, "*", "page", $page)->toArray();
        return $datas;
    }

    public static  function getDetail($slug)
    {
        $qa =  DB::table('q_and_a_s')
            ->join('q_and_a_categories', 'q_and_a_categories.id', '=', 'q_and_a_s.category_id')
            ->select('q_and_a_s.*', 'q_and_a_categories.title as category_name')->where('q_and_a_s.deleted', self::NOT_DELETED)
            ->where('q_and_a_s.status', self::STATUS_ACTIVE)
            ->where('q_and_a_s.slug', $slug)->first();
        return $qa;
    }
}
