<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumAnswerVote extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'forum_answer_votes';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
    
    protected $fillable = [
        'answer_id', // Add 'answer_id' to the fillable array
        'student_id',
        'vote_type',
    ];
}
