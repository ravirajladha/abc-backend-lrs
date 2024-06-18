<?php

namespace App\Services\School;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getSchoolDashboardItems($schoolId)
    {
        $classes = DB::table('classes')->count();
        $students = DB::table('students')->where('school_id', $schoolId)->count();
        $teachers = DB::table('teachers')->where('school_id', $schoolId)->count();
        $applications = DB::table('applications')->count();

        $res = [
            'class' => $classes,
            'students' => $students,
            'teachers' => $teachers,
            'applications' => $applications,
        ];

        return $res;
    }
}
