<?php

namespace App\Services\Teacher;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getTeacherDashboardItems($teacherId)
    {
        $schoolId = DB::table('teachers')->where('id', $teacherId)->pluck('school_id');

        $classIds = DB::table('teacher_classes')->where('teacher_id', $teacherId)->pluck('class_id');
        $classesCount = count($classIds);

        $totalStudentsCount = 0;
        $studentsCountByClass = [];
        foreach ($classIds as $classId) {
            $studentsCount = DB::table('students')->where('class_id', $classId)->where('school_id', $schoolId)->count();
            $studentsCountByClass[$classId] = $studentsCount;
            $totalStudentsCount += $studentsCount;
        }

        $subjectIds = DB::table('teacher_subjects')->where('teacher_id', $teacherId)->pluck('subject_id');
        $subjectsCount = count($subjectIds);

        $percent = 0;
        $results = DB::table('students as s')
            ->select(
                DB::raw('AVG(r.percentage) as average_percentage')
            )
            ->leftJoin('assessment_results as r', 'r.student_id', 's.id')
            ->leftJoin('assessments as a', 'a.id', 'r.assessment_id')
            ->whereIn('a.subject_id', $subjectIds)
            ->groupBy('s.id', 's.name', 's.section_id')
            ->orderBy('s.name', 'asc')
            ->get();

        $totalPercentages = $results->sum('average_percentage');
        $averagePercentage = $totalStudentsCount > 0 ? $totalPercentages / $totalStudentsCount : 0;
        $percent = number_format($averagePercentage, 2);

        $subjects = [];

        foreach ($subjectIds as $subjectId) {
            $students = DB::table('subjects as sub')
                ->leftJoin('students as stu', 'stu.class_id', '=', 'sub.class_id')
                ->where('stu.school_id', $schoolId)
                ->where('sub.id', $subjectId)
                ->select('stu.*')
                ->get();

            $subject = DB::table('subjects')
                ->where('id', $subjectId)
                ->first();
            $class_name = DB::table('classes')->where('id',$subject->class_id)->value('name');

            $studentsCount = count($students);

            $subjects[] = [
                'class_id' => $subject->class_id,
                'class_name' => $class_name,
                'subject_id' => $subjectId,
                'students' => $studentsCount,
                'subject_name' => $subject->name,
                'subject_image' => $subject->image,
            ];
        }



        $res = [
            'classes' => $classesCount,
            'students' => $totalStudentsCount,
            'subjects' => $subjectsCount,
            'percent' => $percent,
            'class_subjects' => $subjects,
        ];

        return $res;
    }
}
