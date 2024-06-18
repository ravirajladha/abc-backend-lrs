<?php

namespace App\Http\Constants;

class AuthConstants
{

    const TYPE_ADMIN = 0;
    const TYPE_SCHOOL = 1;
    const TYPE_TEACHER = 2;
    const TYPE_PARENT = 3;
    const TYPE_STUDENT = 4;
    const TYPE_RECRUITER = 5;

    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;

    const STATUS = [
        self::STATUS_DISABLED => 'DISABLED',
        self::STATUS_ACTIVE => 'ACTIVE',
    ];

    const TYPES = [
        self::TYPE_ADMIN => 'Admin',
        self::TYPE_SCHOOL => 'School',
        self::TYPE_TEACHER => 'Teacher',
        self::TYPE_PARENT => 'Parent',
        self::TYPE_STUDENT => 'Student',
        self::TYPE_RECRUITER => 'recruiter'
    ];

}
