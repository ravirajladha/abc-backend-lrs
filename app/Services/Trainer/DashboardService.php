<?php

namespace App\Services\Trainer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Course;

class DashboardService
{
    public function getTrainerDashboardItems($trainerId)
    {
        // $subjectIds = DB::table('trainer_subjects')->where('trainer_id', $trainerId)->pluck('subject_id');
        $subjectIds = Course::where('trainer_id', $trainerId)
        ->distinct()
        ->pluck('subject_id')->toArray();

        $subjectsCount = count($subjectIds);

        $totalStudentsCount = 0;
        $studentsCountBySubject = [];
        foreach ($subjectIds as $subjectId) {
            $studentsCount = DB::table('students')->count();
            $totalStudentsCount += $studentsCount;
        }

        // $courseIds = DB::table('trainer_courses')->where('trainer_id', $trainerId)->pluck('course_id');
        $courseIds = Course::where('trainer_id', $trainerId)
        ->pluck('id')->toArray();
        $coursesCount = count($courseIds);

        // $courses = [];

        // foreach ($courseIds as $courseId) {
        //     $students = DB::table('courses as cou')
        //         // ->leftJoin('students as stu', 'stu.subject_id', '=', 'cou.subject_id')
        //         ->where('cou.id', $courseId)
        //         // ->select('stu.*')
        //         ->get();

        //     $course = DB::table('courses')
        //         ->where('id', $courseId)
        //         ->first();
        //     $subject_name = DB::table('subjects')->where('id',$course->subject_id)->value('name');

        //     $studentsCount = count($students);

        //     $courses[] = [
        //         'subject_id' => $course->subject_id,
        //         'subject_name' => $subject_name,
        //         'course_id' => $courseId,
        //         'students' => $studentsCount,
        //         'course_name' => $course->name,
        //         'course_image' => $course->image,
        //     ];
        // }

        $courses = DB::table('courses as cou')
        ->select('cou.id as course_id', 'cou.name as course_name','cou.image as course_image', 's.name as subject_name')
        ->leftJoin('subjects as s', 's.id', '=', 'cou.subject_id')
        ->where('cou.trainer_id', $trainerId)
        ->get();

        $res = [
            'subjects' => $subjectsCount,
            'students' => $totalStudentsCount,
            'courses' => $coursesCount,
            'subject_courses' => $courses,
        ];

        return $res;
    }
}
