<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'test_results';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id', 'id');
    }
}
