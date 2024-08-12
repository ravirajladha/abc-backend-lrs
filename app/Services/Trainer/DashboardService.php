<?php

namespace App\Services\Trainer;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getTrainerDashboardItems($teacherId)
    {
        // $schoolId = DB::table('teachers')->where('id', $teacherId)->pluck('school_id');

        $subjectIds = DB::table('teacher_subjects')->where('teacher_id', $teacherId)->pluck('subject_id');
        $subjectsCount = count($subjectIds);

        $totalStudentsCount = 0;
        $studentsCountBySubject = [];
        foreach ($subjectIds as $subjectId) {
            $studentsCount = DB::table('students')->where('subject_id', $subjectId)->count();
            $studentsCountBySubject[$subjectId] = $studentsCount;
            $totalStudentsCount += $studentsCount;
        }

        $courseIds = DB::table('teacher_courses')->where('teacher_id', $teacherId)->pluck('course_id');
        $coursesCount = count($courseIds);

        $percent = 0;
        $results = DB::table('students as s')
            ->select(
                DB::raw('AVG(r.percentage) as average_percentage')
            )
            ->leftJoin('assessment_results as r', 'r.student_id', 's.id')
            ->leftJoin('assessments as a', 'a.id', 'r.assessment_id')
            ->whereIn('a.course_id', $courseIds)
            ->groupBy('s.id', 's.name', 's.section_id')
            ->orderBy('s.name', 'asc')
            ->get();

        $totalPercentages = $results->sum('average_percentage');
        $averagePercentage = $totalStudentsCount > 0 ? $totalPercentages / $totalStudentsCount : 0;
        $percent = number_format($averagePercentage, 2);

        $courses = [];

        foreach ($courseIds as $courseId) {
            $students = DB::table('courses as cou')
                ->leftJoin('students as stu', 'stu.subject_id', '=', 'cou.subject_id')
                // ->where('stu.school_id', $schoolId)
                ->where('cou.id', $courseId)
                ->select('stu.*')
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
            'percent' => $percent,
            'subject_courses' => $courses,
        ];

        return $res;
    }
}
