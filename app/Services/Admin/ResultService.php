<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;

class ResultService
{
    public function getClassResults($classId, $term = NULL)
    {
        $results =  DB::table('term_test_results as r')
            ->select(
                'r.student_id',
                DB::raw('SUM(r.score) as total_score'),
                's.name as student_name'
            )
            ->leftJoin('term_tests as t', 't.id', 'r.test_id')
            ->leftJoin('classes as c', 'c.id', 't.class_id')
            ->leftJoin('subjects as subj', 'subj.id', 't.subject_id')
            ->leftJoin('students as s', 's.id', 'r.student_id')
            ->where('c.id', $classId)
            ->groupBy('r.student_id', 's.name')
            ->orderBy('total_score', 'desc');

        if ($term) {
            $results->where('t.term_type', $term);
        }

        $results = $results->get();

        $class_results = $this->getRanks($results);

        return $class_results;
    }


    public function getSubjectResults($subjectId, $term = NULL)
    {
        $results =  DB::table('term_test_results as r')
            ->select(
                'r.student_id',
                DB::raw('SUM(r.score) as total_score'),
                's.name as student_name'
            )
            ->leftJoin('term_tests as t', 't.id', 'r.test_id')
            ->leftJoin('classes as c', 'c.id', 't.class_id')
            ->leftJoin('subjects as subj', 'subj.id', 't.subject_id')
            ->leftJoin('students as s', 's.id', 'r.student_id')
            ->where('t.subject_id', $subjectId)
            ->groupBy('r.student_id', 's.name')
            ->orderBy('total_score', 'desc');

        if ($term) {
            $results->where('t.term_type', $term);
        }

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
