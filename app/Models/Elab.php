<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Elab extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'elabs';
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }


    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
