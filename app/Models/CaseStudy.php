<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'case_studies';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function modules()
    {
        return $this->hasMany(CaseStudyModule::class);
    }
}
