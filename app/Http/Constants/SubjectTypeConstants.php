<?php

namespace App\Http\Constants;

class SubjectTypeConstants
{

    const TYPE_DEFAULT_SUBJECT = 1;
    const TYPE_SUPER_SUBJECT = 2;
    const TYPE_SUB_SUBJECT = 3;

    const TYPES = [
        self::TYPE_DEFAULT_SUBJECT => 'Default Subject',
        self::TYPE_SUPER_SUBJECT => 'Super Subject',
        self::TYPE_SUB_SUBJECT => 'Sub Subject',
    ];

}
