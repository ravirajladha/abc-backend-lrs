<?php

namespace App\Services\School;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getSchoolDashboardItems($schoolId)
    {
        $classes = DB::table('classes')->count();
        $students = DB::table('students')->where('school_id', $schoolId)->count();
        $trainers = DB::table('trainers')->count();
        $applications = DB::table('applications')->count();

        $res = [
            'class' => $classes,
            'students' => $students,
            'trainers' => $trainers,
            'applications' => $applications,
        ];

        return $res;
    }
}
