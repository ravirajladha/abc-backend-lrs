<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'slash_amount',

        'referral_amount',
        'referrer_amount',
        'benefits',
        'description'
    ];
}
