<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermTest extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'term_tests';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function results()
    {
        return $this->hasMany(TermTestResult::class, 'test_id', 'id');
    }
}
