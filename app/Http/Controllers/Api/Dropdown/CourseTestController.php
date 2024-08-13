<?php

namespace App\Http\Controllers\Api\Dropdown;

use App\Models\Course;
use App\Models\Test;

use Illuminate\Support\Facades\Log;
use App\Http\Constants\CourseTypeConstants;
use App\Http\Controllers\Api\BaseController;

// This controller which show the courses on select of subject, but only those courses which are already not the part of any term_tests
class CourseTestController extends BaseController
{
    public function __invoke($subjectId)
    {
        Log::info(['subjectId' => $subjectId]);
        // Fetch courses for the given subject ID
        $courses = Course::select('id', 'name')
            ->where('subject_id', $subjectId)
            ->get()
            ->map(function ($course) {
                $course->name = ucfirst($course->name);
                return $course;
            });

        // Fetch courses that already have a term test assigned
        $assignedCourses = Test::select('course_id')
            ->whereIn('course_id', $courses->pluck('id'))
            ->where('status', true)
            ->get()
            ->pluck('course_id')
            ->toArray();

        // Add flag to courses to indicate if they already have a test assigned
    $courses = $courses->map(function ($course) use ($assignedCourses) {
        $course->isDisabled = in_array($course->id, $assignedCourses);
        return $course;
    });
    Log::info(['courses' => $courses]);
        return $this->sendResponse(['courses' => $courses]);
    }


}
