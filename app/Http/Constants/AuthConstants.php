<?php

namespace App\Http\Constants;

class AuthConstants
{
    // $table->tinyInteger('type')->default(1)->comment('0=>Admin; 1=>Student; 2=>Trainer; 3=> Recruiter; 4=> Internship Admin;');
    const TYPE_ADMIN = 0;
    const TYPE_STUDENT = 1;
    const TYPE_TRAINER = 2;
    const TYPE_RECRUITER = 3;
    const TYPE_INTERNSHIP_ADMIN = 4;
  

    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;

    const STATUS = [
        self::STATUS_DISABLED => 'DISABLED',
        self::STATUS_ACTIVE => 'ACTIVE',
    ];

    const TYPES = [
        self::TYPE_ADMIN => 'Admin',
        self::TYPE_INTERNSHIP_ADMIN => 'Internship_Admin',
        self::TYPE_TRAINER => 'Trainer',
        self::TYPE_STUDENT => 'Student',
        self::TYPE_RECRUITER => 'Recruiter'
    ];

}
