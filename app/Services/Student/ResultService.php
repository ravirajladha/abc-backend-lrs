<?php

namespace App\Services\Student;

use Illuminate\Support\Facades\DB;
use App\Services\Admin\TestService;

use Illuminate\Support\Facades\Log;
use App\Http\Constants\TermTestConstants;

//changed

class ResultService
{

    public function getCourseResults($studentId)
    {
        $results = [];
    
        // Fetch all subjects and their corresponding classes
        $courses = DB::table('courses as c')
            ->join('subjects as s', 'c.course_id', '=', 's.id')
            ->select('c.id as course_id', 'c.name as course_name', 's.name as subject_name', 'c.image')
            ->get();
    
        // Iterate over each subject to fetch the student's score for active tests
        foreach ($courses as $course) {
    
            // Fetch the active test for the current subject
            $activeTest = DB::table('tests as t')
                ->select('t.id')
                ->where('t.course_id', $course->course_id)
                ->where('t.status', 1)
                ->first();
    
            if ($activeTest) {
                // Fetch the student's score for the active test
                $score = DB::table('test_results as r')
                    ->select('r.score')
                    ->where('r.student_id', $studentId)
                    ->where('r.test_id', $activeTest->id)
                    ->first();
    
                // Store the class name, subject name, and the student's score in the results array
                $results[$course->course_name][$course->course_name] = $score ? $score->score : 0;
            } else {
                // If no active test is found, set the score to 0
                $results[$course->course_name][$course->subject_name] = 0;
            }
        }
    
        return $results;
    }
    
    

    public function getSubjectRank($studentId)
    {
        // Fetch all subjects
        $subjects = DB::table('subjects')
            ->select('id', 'name')
            ->get();
    
        // Initialize an array to hold the results
        $results = [];
    
        // Iterate through each subject to fetch the test results and calculate rank
        foreach ($subjects as $subject) {
            // Fetch the test results for the current class
            $subjectResults = DB::table('test_results as r')
                ->select('r.student_id', DB::raw('SUM(r.score) as total_score'))
                ->join('tests as t', 't.id', '=', 'r.test_id')
                ->where('t.subject_id', $subject->id)
                ->where('t.status', 1)
                ->groupBy('r.student_id')
                ->get();
    
            // Initialize an array to hold the total scores for ranking
            $totalScores = [];
    
            // Merge class results into the total scores
            foreach ($subjectResults as $result) {
                $totalScores[$result->student_id] = $result->total_score;
            }
    
            // Sort the total scores in descending order and calculate ranks
            arsort($totalScores);
            $rank = 1;
            $foundRank = false;
            foreach ($totalScores as $studentIdKey => $score) {
                if ((string)$studentIdKey === (string)$studentId) {
                    $results[$subject->name] = $rank;
                    $foundRank = true;
                    break;
                }
                $rank++;
            }
    
            // If the student's rank is not found, set it to 0
            if (!$foundRank) {
                $results[$subject->name] = 0;
            }
        }
    
        return $results;
    }
    

    // public function getSectionRank($studentId, $sectionId)
    // {
    //     $section_results = $this->getSectionMarks($sectionId);
    //     foreach ($section_results as $result) {
    //         if ((string)$result->student_id === (string)$studentId) {
    //             return $result->rank;
    //         }
    //     }
    // }

    public function getTotalMarks($studentId)
    {
        $marks = [];

        $totalCourseScore = $this->getTestTotalResult($studentId);

     Log::info(['totalCourseScore' => $totalCourseScore]);

        // $totalScore =  $firstTermScore + $secondTermScore + $thirdTermScore;

        $marks = [
           
            'total' => $totalCourseScore,
        ];

        return $marks;
    }

    public function getTestTotalMarks($studentId)
    {
        $total_marks = [];
        $TestService = new TestService();

        $firstTotalMarks = $TestService->getTestTotalMarks($studentId);
        // $secondTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::SECOND_TERM);
        // $thirdTermTotalMarks = $termTestService->getTermTestTotalMarks($classId, TermTestConstants::THIRD_TERM);

        $totalScore =  $firstTotalMarks ;

        $marks = [
            'first_total_marks' => $firstTotalMarks !== 0 ? $firstTotalMarks : null,
            // 'second_term_total_marks' => $secondTermTotalMarks !== 0 ? $secondTermTotalMarks : null,
            // 'third_term_total_marks' => $thirdTermTotalMarks !== 0 ? $thirdTermTotalMarks : null,
            'total' => $totalScore,
        ];

        return $marks;
    }

    public function getTestTotalResult($studentId)
    {

        DB::enableQueryLog();

        $result =  DB::table('students as s')
            ->select(DB::raw('SUM(r.score) as total_score'))
            ->leftJoin('test_results as r', 'r.student_id', 's.auth_id')
            ->leftJoin('tests as t', 't.id', 'r.test_id')
            ->leftJoin('courses as cou', 'cou.id', 't.course_id')
            ->where('s.auth_id', $studentId)
            ->value('total_score');
            $queries = DB::getQueryLog();

            Log::info('executed query', ['query' => $queries]);
            Log::info('Total Score Result:', ['total_score' => $result]);

            return $result;
    }

    public function getCourseMarks($subjectId)
    {
        $course_results =  DB::table('test_results as r')
            ->select(
                'r.student_id',
                DB::raw('SUM(r.score) as total_marks'),
            )
            ->leftJoin('tests as t', 't.id', 'r.test_id')
            ->leftJoin('subjects as s', 's.id', 't.subject_id')
            ->leftJoin('courses as cou', 'cou.id', 't.course_id')
            ->where('s.id', $subjectId)
            ->groupBy('r.student_id')
            ->get();

        $rank = 1;
        foreach ($course_results as $key => $value) {
            $course_results[$key]->rank = $rank++;
        }

        return $course_results;
    }

   
    public function getAverageAssessmentScore($studentId)
    {
        // Fetch all courses and their corresponding classes
        $courses = DB::table('courses as cou')
            ->join('subjects as sub', 'cou.subject_id', '=', 'cou.id')
            ->select('cou.id as course_id', 'cou.name as course_name', 'sub.name as subject_name')
            ->get();
    
        // Initialize an array to hold the results
        $results = [];
    
        // Iterate over each course to calculate the average score
        foreach ($courses as $course) {
            // Fetch the average score for the current subject
            $avgScore = DB::table('assessment_results as ar')
                ->join('assessments as a', 'ar.assessment_id', '=', 'a.id')
                ->select(DB::raw('AVG(ar.score) as avg_score'))
                ->where('ar.student_id', $studentId)
                ->where('a.course_id', $course->course_id)
                ->first();
    
            // Store the class name, subject name, and the average score in the results array
            $results[$course->subject_name][$course->subject_name] = $avgScore ? $avgScore->avg_score : 0;
        }
    
        return $results;
    }
 
    

}
