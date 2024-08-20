<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Course;

use App\Http\Controllers\Api\BaseController;

class CourseController extends BaseController
{
    public function __invoke($subjectId)
    {
        $courses = Course::select('id', 'name')->where('subject_id', $subjectId)->get()->map(function ($course) {
            $course->name = ucfirst($course->name);
            return $course;
        });
        return $this->sendResponse(['courses' => $courses]);
    }
}
