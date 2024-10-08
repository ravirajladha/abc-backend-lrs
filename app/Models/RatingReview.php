<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingReview extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'ratings_reviews';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
    // Fields that are mass-assignable
    protected $fillable = [
        'course_id',
        'student_id',
        'rating',
        'review',
    ];

}
