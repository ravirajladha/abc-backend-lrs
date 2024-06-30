<?php

namespace App\Services\Student;

use Illuminate\Support\Facades\DB;
use App\Services\Admin\TermTestService;

use App\Http\Constants\TermTestConstants;

class ResultService
{
    public function getSubjectResults1($studentId, $classId)
    {
        $results = [];

        $subjects = DB::table('subjects as s')
            ->select('s.id', 's.name', 's.image')
            // ->where('s.class_id', $classId)
            ->get();

        foreach ($subjects as $subject) {

            $results[$subject->name]['term_marks'] = DB::table('students as s')
                ->select( 'r.score')
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
    public function getSubjectResults($studentId)
    {
        $results = [];
    
        // Fetch all subjects and their corresponding classes
        $subjects = DB::table('subjects as s')
            ->join('classes as c', 's.class_id', '=', 'c.id')
            ->select('s.id as subject_id', 's.name as subject_name', 'c.name as class_name', 's.image')
            ->get();
    
        // Iterate over each subject to fetch the student's score for active tests
        foreach ($subjects as $subject) {
    
            // Fetch the active test for the current subject
            $activeTest = DB::table('term_tests as t')
                ->select('t.id')
                ->where('t.subject_id', $subject->subject_id)
                ->where('t.status', 1)
                ->first();
    
            if ($activeTest) {
                // Fetch the student's score for the active test
                $score = DB::table('term_test_results as r')
                    ->select('r.score')
                    ->where('r.student_id', $studentId)
                    ->where('r.test_id', $activeTest->id)
                    ->first();
    
                // Store the class name, subject name, and the student's score in the results array
                $results[$subject->class_name][$subject->subject_name] = $score ? $score->score : 0;
            } else {
                // If no active test is found, set the score to 0
                $results[$subject->class_name][$subject->subject_name] = 0;
            }
        }
    
        return $results;
    }
    
    

    public function getClassRank($studentId)
    {
        // Fetch all classes
        $classes = DB::table('classes')
            ->select('id', 'name')
            ->get();
    
        // Initialize an array to hold the results
        $results = [];
    
        // Iterate through each class to fetch the test results and calculate rank
        foreach ($classes as $class) {
            // Fetch the test results for the current class
            $classResults = DB::table('term_test_results as r')
                ->select('r.student_id', DB::raw('SUM(r.score) as total_score'))
                ->join('term_tests as t', 't.id', '=', 'r.test_id')
                ->where('t.class_id', $class->id)
                ->where('t.status', 1)
                ->groupBy('r.student_id')
                ->get();
    
            // Initialize an array to hold the total scores for ranking
            $totalScores = [];
    
            // Merge class results into the total scores
            foreach ($classResults as $result) {
                $totalScores[$result->student_id] = $result->total_score;
            }
    
            // Sort the total scores in descending order and calculate ranks
            arsort($totalScores);
            $rank = 1;
            $foundRank = false;
            foreach ($totalScores as $studentIdKey => $score) {
                if ((string)$studentIdKey === (string)$studentId) {
                    $results[$class->name] = $rank;
                    $foundRank = true;
                    break;
                }
                $rank++;
            }
    
            // If the student's rank is not found, set it to 0
            if (!$foundRank) {
                $results[$class->name] = 0;
            }
        }
    
        return $results;
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

        $totalSubjectScore = $this->getTermTestTotalResult($studentId);
 

        // $totalScore =  $firstTermScore + $secondTermScore + $thirdTermScore;

        $marks = [
           
            'total' => $totalSubjectScore,
        ];

        return $marks;
    }

    public function getTermTestTotalMarks($studentId)
    {
        $total_marks = [];
        $termTestService = new TermTestService();

        $firstTermTotalMarks = $termTestService->getTermTestTotalMarks($studentId);
        // $secondTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::SECOND_TERM);
        // $thirdTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::THIRD_TERM);

        $totalScore =  $firstTermTotalMarks ;

        $marks = [
            'first_term_total_marks' => $firstTermTotalMarks !== 0 ? $firstTermTotalMarks : null,
            // 'second_term_total_marks' => $secondTermTotalMarks !== 0 ? $secondTermTotalMarks : null,
            // 'third_term_total_marks' => $thirdTermTotalMarks !== 0 ? $thirdTermTotalMarks : null,
            'total' => $totalScore,
        ];

        return $marks;
    }


    public function getTermTestTotalResult($studentId)
    {
        return DB::table('students as s')
            ->select(DB::raw('SUM(r.score) as total_score'))
            ->leftJoin('term_test_results as r', 'r.student_id', 's.id')
            ->leftJoin('term_tests as t', 't.id', 'r.test_id')
            ->leftJoin('subjects as sub', 'sub.id', 't.subject_id')
            ->where('s.id', $studentId)
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
    public function getAverageAssessmentScore($studentId)
    {
        // Fetch all subjects and their corresponding classes
        $subjects = DB::table('subjects as s')
            ->join('classes as c', 's.class_id', '=', 'c.id')
            ->select('s.id as subject_id', 's.name as subject_name', 'c.name as class_name')
            ->get();
    
        // Initialize an array to hold the results
        $results = [];
    
        // Iterate over each subject to calculate the average score
        foreach ($subjects as $subject) {
            // Fetch the average score for the current subject
            $avgScore = DB::table('assessment_results as ar')
                ->join('assessments as a', 'ar.assessment_id', '=', 'a.id')
                ->select(DB::raw('AVG(ar.score) as avg_score'))
                ->where('ar.student_id', $studentId)
                ->where('a.subject_id', $subject->subject_id)
                ->first();
    
            // Store the class name, subject name, and the average score in the results array
            $results[$subject->class_name][$subject->subject_name] = $avgScore ? $avgScore->avg_score : 0;
        }
    
        return $results;
    }

    
    // public function getAverageAssessmentScore($studentId){
    //     $assessementResults = DB::table('assessment_results')
    //             ->join('assessments', 'assessment_results.assessment_id', '=', 'assessments.id')
    //             ->join('subjects', 'assessments.subject_id', '=', 'subjects.id')
    //             ->select('subjects.name as subject_name', DB::raw('AVG(assessment_results.score) as avg_score'))
    //             ->where('assessment_results.student_id', $studentId)
    //             ->groupBy('subjects.name')
    //             ->get();
    //     return $assessementResults;
    // }
}
