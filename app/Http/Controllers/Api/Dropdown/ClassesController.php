<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Classes;

use App\Http\Controllers\Api\BaseController;

class ClassesController extends BaseController
{
    public function __invoke()
    {
        // $classes = Classes::select('id', 'name')->get();
        // To pass the class names with alphabetically capitalized letters
        $classes = Classes::select('id', 'name')->get()->map(function ($class) {
            $class->name = ucwords($class->name);
            return $class;
        });
        return $this->sendResponse(['classes' => $classes]);
    }
}
