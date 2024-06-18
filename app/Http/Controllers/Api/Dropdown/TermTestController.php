<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\TermTest;

use App\Http\Controllers\Api\BaseController;

class TermTestController extends BaseController
{
    public function __invoke($classId)
    {
        $termTests = TermTest::select('id', 'name')->get()->map(function ($termTest) {
            $termTest->name = ucfirst($termTest->name);
            return $termTest;
        });
    
        return $this->sendResponse(['term_test' => $termTests]);
    }
    
}
