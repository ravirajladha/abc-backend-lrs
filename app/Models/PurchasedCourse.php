<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasedCourse extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'purchased_courses';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
