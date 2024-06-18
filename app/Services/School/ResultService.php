<?php

namespace App\Services\School;

use Illuminate\Support\Facades\DB;

use App\Http\Constants\TermTestConstants;

class ResultService
{
    public function getSchoolResults($schoolId = null, $classId = null, $sectionId = null, $term = null)
    {
        // Get all subjects for the specified class
        $subjects = DB::table('subjects as s')->where('s.class_id', $classId)->pluck('name');

        $formattedResults = [];

        // Get results for the specified conditions
        $results = DB::table('students as s')
            ->select('s.id as student_id', 's.name as student_name', 's.section_id', 'r.id as result_id', 'sub.name as subject_name', 'r.test_id', 'r.score')
            ->leftJoin('term_test_results as r', 'r.student_id', 's.id')
            ->leftJoin('term_tests as t', 't.id', 'r.test_id')
            ->leftJoin('subjects as sub', 'sub.id', 't.subject_id')
            ->where('t.class_id', $classId)
            ->where('r.school_id', $schoolId);

        if ($term !== null) {
            $results->where('t.term_type', $term);
        }
        if ($sectionId !== null) {
            $results->where('s.section_id', $sectionId);
        }

        $results = $results
            ->orderBy('s.name', 'asc')
            ->get();

        foreach ($results as $result) {
            $formattedResult = [
                'student_id' => $result->student_id,
                'student_name' => $result->student_name,
                'term' => $term,
                'total_score' => 0,
                'results' => []
            ];

            if (!isset($formattedResults[$result->student_id])) {
                $formattedResults[$result->student_id] = $formattedResult;
            }

            // Iterate through all subjects and initialize scores to 0 if not present
            foreach ($subjects as $subject) {
                $subjectKey = $subject;

                if(!isset($formattedResults[$result->student_id]['results'][$subjectKey]['test_id'])) {
                    $formattedResults[$result->student_id]['results'][$subjectKey] = [
                        'test_id' => null,
                        'score' => 0
                    ];
                }
            }

            // Update the score for the current subject
            $subjectKey = $result->subject_name;

            $formattedResults[$result->student_id]['results'][$subjectKey] = [
                'test_id' => $result->test_id,
                'score' => $result->score
            ];

            $formattedResults[$result->student_id]['total_score'] += $result->score;
        }

        $formattedResults = array_values($formattedResults);

        $resultWithRanks = $this->getRanks($formattedResults);

        return $resultWithRanks;
    }

    public function getRanks($results)
    {
        $rank = 1;
        $prevTotalScore = null;
        $prevRank = null;

        foreach ($results as &$result) {
            if ($result['total_score'] !== $prevTotalScore) {
                $result['rank'] = $rank;
                $prevRank = $rank;
            } else {
                $result['rank'] = $prevRank;
            }

            $prevTotalScore = $result['total_score'];
            // $prevRank = $result['rank'];
            $rank++;
        }

        unset($result);

        return $results;
    }
}
