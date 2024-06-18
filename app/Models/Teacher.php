<?php

namespace App\Models;

use App\Http\Constants\AuthConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'teachers';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function authInfo()
    {
        return $this->auth()
            ->select('email', 'username', 'phone_number', 'status')
            ->first();
    }

    public function auth()
    {
        return $this->belongsTo(Auth::class, 'auth_id', 'id')->where('type', AuthConstants::TYPE_TEACHER);
    }
}
