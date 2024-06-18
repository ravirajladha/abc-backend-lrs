<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectReportModule extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'project_report_modules';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function sections()
    {
        return $this->hasMany(ProjectReportSection::class);
    }
}
