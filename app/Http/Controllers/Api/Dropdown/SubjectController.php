<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Subject;

use App\Http\Controllers\Api\BaseController;

class SubjectController extends BaseController
{
    public function __invoke()
    {
        // $classes = Classes::select('id', 'name')->get();
        // To pass the class names with alphabetically capitalized letters
        $subjects = Subject::select('id', 'name')->get()->map(function ($subject) {
            $subject->name = ucwords($subject->name);
            return $subject;
        });
        return $this->sendResponse(['subjects' => $subjects]);
    }
}
