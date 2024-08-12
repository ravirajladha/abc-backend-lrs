<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Test;

use App\Http\Controllers\Api\BaseController;

class TestController extends BaseController
{
    public function __invoke($subjectId)
    {
        $tests = Test::select('id', 'name')->get()->map(function ($test) {
            $test->name = ucfirst($test->name);
            return $test;
        });

        return $this->sendResponse(['tests' => $tests]);
    }

}
