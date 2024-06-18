<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldApplication extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'old_applications';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function oldRemarks()
    {
        return $this->hasMany(OldApplicationRemark::class, 'application_id');
    }
}
