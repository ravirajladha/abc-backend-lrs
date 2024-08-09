<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'tests';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function results()
    {
        return $this->hasMany(TestResult::class, 'test_id', 'id');
    }
}
