<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'colleges';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    protected $fillable = [
        'name',
        'city',
        'state',
        'address',
        'status',
        'created_by'
    ];
}
