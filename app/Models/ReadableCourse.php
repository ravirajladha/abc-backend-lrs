<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadableCourse extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'readable_courses';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

}
