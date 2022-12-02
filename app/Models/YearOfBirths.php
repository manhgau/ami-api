<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YearOfBirths extends Model
{

    protected $fillable = [
        'id',
        'name',
        'min_value',
        'max_value',
        'status',
        'deleted',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status'];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function updateYearOfBirth($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->update($data);
    }

    public static  function getAllYearOfBirth()
    {
        return self::where('deleted', self::NOT_DELETED)->get();
    }

    public static  function updateMany($data, $ids)
    {
        return self::where('deleted', self::NOT_DELETED)->whereIn('id', $ids)->update($data);
    }

    public static  function getDetail($id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->first();
    }

    public function scopeGetYearOfBirth()
    {
        return $this->where('deleted', self::NOT_DELETED);
    }
    public static  function getYearOfBirth($perPage = 100,  $page = 1,  $name = null)
    {
        $family_people = self::where('deleted', self::NOT_DELETED)
            ->orderBy('id', 'ASC')
            ->where('status', self::STATUS_ACTIVE);
        if ($name != null) {
            $family_people->where('name', 'like', '%' . $name . '%');
        }

        $data =  $family_people->paginate($perPage, "*", "page", $page)->toArray();
        return $data;
    }
}
