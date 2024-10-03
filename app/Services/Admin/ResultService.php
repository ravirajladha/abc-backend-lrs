<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;

class ResultService
{
    public function getCourseResults($courseId)
    {
        $results =  DB::table('test_results as r')
            ->select(
                'r.student_id',
                DB::raw('SUM(r.score) as total_score'),
                's.name as student_name'
            )
            ->leftJoin('tests as t', 't.id', 'r.test_id')
            ->leftJoin('subjects as subj', 'subj.id', 't.subject_id')
            ->leftJoin('courses as c', 'c.id', 't.course_id')
            ->leftJoin('students as s', 's.id', 'r.student_id')
            ->where('c.id', $courseId)
            ->groupBy('r.student_id', 's.name')
            ->orderBy('total_score', 'desc');

        $results = $results->get();

        $class_results = $this->getRanks($results);

        return $class_results;
    }


    public function getSubjectResults($subjectId)
    {
        $results =  DB::table('test_results as r')
            ->select(
                'r.student_id',
                DB::raw('SUM(r.score) as total_score'),
                's.name as student_name'
            )
            ->leftJoin('tests as t', 't.id', 'r.test_id')
            ->leftJoin('subjects as subj', 'subj.id', 't.subject_id')
            ->leftJoin('courses as c', 'c.id', 't.course_id')
            ->leftJoin('students as s', 's.id', 'r.student_id')
            ->where('t.subject_id', $subjectId)
            ->groupBy('r.student_id', 's.name')
            ->orderBy('total_score', 'desc');

        $results = $results->get();

        $subject_results = $this->getRanks($results);

        return $subject_results;
    }

    public function getRanks($results)
    {
        $rank = 1;
        $prevTotalScore = null;
        $prevRank = null;

        foreach ($results as $result) {
            if ($result->total_score !== $prevTotalScore) {
                $result->rank = $rank;
            } else {
                $result->rank = $prevRank;
            }

            $prevTotalScore = $result->total_score;
            $prevRank = $result->rank;
            $rank++;
        }

        return $results;
    }
}
