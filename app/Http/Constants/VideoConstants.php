<?php

namespace App\Http\Constants;

class VideoConstants
{

    const STATUS_STARTED = 1;
    const STATUS_COMPLETED = 2;

    const STATUS = [
        self::STATUS_STARTED => 'Started',
        self::STATUS_COMPLETED => 'Completed'
    ];

}
