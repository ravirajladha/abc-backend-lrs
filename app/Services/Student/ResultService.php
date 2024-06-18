<?php

namespace App\Services\Student;

use Illuminate\Support\Facades\DB;
use App\Services\Admin\TermTestService;

use App\Http\Constants\TermTestConstants;

class ResultService
{
    public function getSubjectResults($studentId, $classId)
    {
        $results = [];

        $subjects = DB::table('subjects as s')
            ->select('s.id', 's.name', 's.image')
            ->where('s.class_id', $classId)
            ->get();

        foreach ($subjects as $subject) {

            $results[$subject->name]['term_marks'] = DB::table('students as s')
                ->select('t.term_type', 'r.score')
                ->leftJoin('term_test_results as r', 'r.student_id', 's.id')
                ->leftJoin('term_tests as t', 't.id', 'r.test_id')
                ->leftJoin('subjects as sub', 'sub.id', 't.subject_id')
                ->where('s.id', $studentId)
                ->where('sub.id', $subject->id)
                ->orderBy('sub.name')
                ->get();

            $totalScore = DB::table('students as s')
                ->select(DB::raw('SUM(r.score) as total_score'))
                ->leftJoin('term_test_results as r', 'r.student_id', 's.id')
                ->leftJoin('term_tests as t', 't.id', 'r.test_id')
                ->leftJoin('subjects as sub', 'sub.id', 't.subject_id')
                ->where('s.id', $studentId)
                ->where('sub.id', $subject->id)
                ->get();

            $results[$subject->name]['total_score'] = $totalScore[0]->total_score ?? 0;
        }

        return $results;
    }

    public function getClassRank($studentId, $classId)
    {
        $class_results = $this->getClassMarks($classId);

        foreach ($class_results as $result) {
            if ((string)$result->student_id === (string)$studentId) {
                return $result->rank;
            }
        }
    }

    public function getSectionRank($studentId, $sectionId)
    {
        $section_results = $this->getSectionMarks($sectionId);
        foreach ($section_results as $result) {
            if ((string)$result->student_id === (string)$studentId) {
                return $result->rank;
            }
        }
    }

    public function getTotalMarks($studentId)
    {
        $marks = [];

        $firstTermScore = $this->getTermTestTotalResult($studentId, TermTestConstants::FIRST_TERM);
        $secondTermScore = $this->getTermTestTotalResult($studentId, TermTestConstants::SECOND_TERM);
        $thirdTermScore = $this->getTermTestTotalResult($studentId, TermTestConstants::THIRD_TERM);

        $totalScore =  $firstTermScore + $secondTermScore + $thirdTermScore;

        $marks = [
            'first_term' => $firstTermScore,
            'second_term' => $secondTermScore,
            'third_term' => $thirdTermScore,
            'total' => $totalScore,
        ];

        return $marks;
    }

    public function getTermTestTotalMarks($classId)
    {
        $total_marks = [];
        $termTestService = new TermTestService();

        $firstTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::FIRST_TERM);
        $secondTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::SECOND_TERM);
        $thirdTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::THIRD_TERM);

        $totalScore =  $firstTermTotalMarks + $secondTermTotalMarks + $thirdTermTotalMarks;

        $marks = [
            'first_term_total_marks' => $firstTermTotalMarks !== 0 ? $firstTermTotalMarks : null,
            'second_term_total_marks' => $secondTermTotalMarks !== 0 ? $secondTermTotalMarks : null,
            'third_term_total_marks' => $thirdTermTotalMarks !== 0 ? $thirdTermTotalMarks : null,
            'total' => $totalScore,
        ];

        return $marks;
    }


    public function getTermTestTotalResult($studentId, $term)
    {
        return DB::table('students as s')
            ->select(DB::raw('SUM(r.score) as total_score'))
            ->leftJoin('term_test_results as r', 'r.student_id', 's.id')
            ->leftJoin('term_tests as t', 't.id', 'r.test_id')
            ->leftJoin('subjects as sub', 'sub.id', 't.subject_id')
            ->where('s.id', $studentId)
            ->where('t.term_type', $term)
            ->value('total_score');
    }

    public function getClassMarks($classId)
    {
        $class_results =  DB::table('term_test_results as r')
            ->select(
                'r.student_id',
                DB::raw('SUM(r.score) as total_marks'),
            )
            ->leftJoin('term_tests as t', 't.id', 'r.test_id')
            ->leftJoin('classes as c', 'c.id', 't.class_id')
            ->leftJoin('subjects as subj', 'subj.id', 't.subject_id')
            ->where('c.id', $classId)
            ->groupBy('r.student_id')
            ->get();

        $rank = 1;
        foreach ($class_results as $key => $value) {
            $class_results[$key]->rank = $rank++;
        }

        return $class_results;
    }

    public function getSectionMarks($sectionId)
    {
        $section_results = DB::table('term_test_results as r')
            ->select(
                'r.student_id',
                DB::raw('SUM(r.score) as total_marks')
            )
            ->leftJoin('students as s', 's.id', 'r.student_id')
            ->leftJoin('term_tests as t', 't.id', 'r.test_id')
            ->leftJoin('classes as c', 'c.id', 't.class_id')
            ->leftJoin('subjects as subj', 'subj.id', 't.subject_id')
            ->leftJoin('sections as sec', 'sec.id', 's.section_id')
            ->where('s.section_id', $sectionId)
            ->groupBy('r.student_id')
            ->get();

        $rank = 1;
        foreach ($section_results as $key => $value) {
            $section_results[$key]->rank = $rank++;
        }

        return $section_results;
    }

    public function getAverageAssessmentScore($studentId){
        $assessementResults = DB::table('assessment_results')
                ->join('assessments', 'assessment_results.assessment_id', '=', 'assessments.id')
                ->join('subjects', 'assessments.subject_id', '=', 'subjects.id')
                ->select('subjects.name as subject_name', DB::raw('AVG(assessment_results.score) as avg_score'))
                ->where('assessment_results.student_id', $studentId)
                ->groupBy('subjects.name')
                ->get();
        return $assessementResults;
    }
}
