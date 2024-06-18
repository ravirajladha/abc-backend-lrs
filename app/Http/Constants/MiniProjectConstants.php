<?php

namespace App\Http\Constants;

class MiniProjectConstants
{

    const STATUS_PENDING = 0;
    const STATUS_STARTED = 1;
    const STATUS_COMPLETED = 2;

    const STATUS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_STARTED => 'Started',
        self::STATUS_COMPLETED => 'Completed'
    ];

}
