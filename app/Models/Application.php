<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'applications';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
    public function remarks()
    {
        return $this->hasMany(ApplicationRemark::class, 'application_id');
    }
}
