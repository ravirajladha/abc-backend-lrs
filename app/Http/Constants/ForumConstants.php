<?php

namespace App\Http\Constants;

class ForumConstants
{

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;

    const STATUS = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_DISABLED => 'Disabled'
    ];

}
