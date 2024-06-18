<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldApplicationRemark extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'old_application_remarks';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function oldApplication()
    {
        return $this->belongsTo(OldApplication::class);
    }

}
