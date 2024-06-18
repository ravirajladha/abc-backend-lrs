<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseStudySection extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'case_study_sections';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function elements()
    {
        return $this->hasMany(CaseStudyElement::class);
    }
}
