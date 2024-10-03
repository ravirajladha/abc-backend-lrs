<?php

namespace App\Services\Trainer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    public function getTrainerDashboardItems($trainerId)
    {
        $subjectIds = DB::table('trainer_subjects')->where('trainer_id', $trainerId)->pluck('subject_id');
        $subjectsCount = count($subjectIds);

        $totalStudentsCount = 0;
        $studentsCountBySubject = [];
        foreach ($subjectIds as $subjectId) {
            $studentsCount = DB::table('students')->count();
            $totalStudentsCount += $studentsCount;
        }

        $courseIds = DB::table('trainer_courses')->where('trainer_id', $trainerId)->pluck('course_id');
        $coursesCount = count($courseIds);

        $courses = [];

        foreach ($courseIds as $courseId) {
            $students = DB::table('courses as cou')
                // ->leftJoin('students as stu', 'stu.subject_id', '=', 'cou.subject_id')
                ->where('cou.id', $courseId)
                // ->select('stu.*')
                ->get();

            $course = DB::table('courses')
                ->where('id', $courseId)
                ->first();
            $subject_name = DB::table('subjects')->where('id',$course->subject_id)->value('name');

            $studentsCount = count($students);

            $courses[] = [
                'subject_id' => $course->subject_id,
                'subject_name' => $subject_name,
                'course_id' => $courseId,
                'students' => $studentsCount,
                'course_name' => $course->name,
                'course_image' => $course->image,
            ];
        }



        $res = [
            'subjects' => $subjectsCount,
            'students' => $totalStudentsCount,
            'courses' => $coursesCount,
            'subject_courses' => $courses,
        ];

        return $res;
    }
}
