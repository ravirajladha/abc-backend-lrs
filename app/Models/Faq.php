<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'faqs';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    protected $fillable = [
        'course_id',
        'created_by',
        'question',
        'answer'
    ];
}
