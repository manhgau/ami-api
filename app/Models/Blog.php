<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'category_id',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted',
        'thumbnail',
        'type'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const BLOG  = 'blog';
    const SOLUTION  = 'solution';

    public static  function getALL($perPage = 10, $page = 1,  $category_id = null)
    {
        $blogs =  DB::table('blogs')
            ->join('blog_categories', 'blog_categories.id', '=', 'blogs.category_id')
            ->select(
                'blogs.id',
                'blogs.title',
                'blogs.slug',
                'blogs.description',
                'blogs.category_id',
                'blogs.thumbnail',
                'blog_categories.title as category_name',
                'blogs.type'
            )
            ->where('blogs.deleted', self::NOT_DELETED)->where('blogs.status', self::STATUS_ACTIVE)
            ->orderBy('blogs.id', 'desc');
        if ($category_id != null) {
            $blogs->where('blogs.category_id', $category_id);
        }
        $datas = $blogs->paginate($perPage, "*", "page", $page)->toArray();
        return $datas;
    }

    public static  function getBlogRelate($perPage = 10, $page = 1,  $category_id, $slug)
    {
        $blogs =  DB::table('blogs')
            ->join('blog_categories', 'blog_categories.id', '=', 'blogs.category_id')
            ->select(
                'blogs.id',
                'blogs.title',
                'blogs.slug',
                'blogs.description',
                'blogs.category_id',
                'blogs.thumbnail',
                'blog_categories.title as category_name',
                'blogs.type'
            )
            ->where('blogs.deleted', self::NOT_DELETED)->where('blogs.status', self::STATUS_ACTIVE)->orderBy('blogs.id', 'desc')
            ->where('blogs.category_id', $category_id)->where('blogs.slug', '<>', $slug);
        $datas = $blogs->paginate($perPage, "*", "page", $page)->toArray();
        return $datas;
    }

    public static  function getDetail($slug)
    {
        $blog =  DB::table('blogs')
            ->join('blog_categories', 'blog_categories.id', '=', 'blogs.category_id')
            ->select('blogs.*', 'blog_categories.title as category_name')->where('blogs.deleted', self::NOT_DELETED)
            ->where('blogs.status', self::STATUS_ACTIVE)
            ->where('blogs.slug', $slug)->first();
        return $blog;
    }
}
