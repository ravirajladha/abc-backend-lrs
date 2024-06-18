<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectReport extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'project_reports';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function modules()
    {
        return $this->hasMany(ProjectReportModule::class);
    }
}
