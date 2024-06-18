<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationRemark extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'application_remarks';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
