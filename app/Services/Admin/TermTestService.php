<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TermTestService
{
    // public function getTermTestTotalMarks($classId)
    // {
    //     // Log::info(['classId' => $classId]);
    //     return DB::table('term_tests as t')
    //         ->select(DB::raw('SUM(t.total_score) as total_term_marks'))
    //         ->leftJoin('term_test_results as r', 'r.test_id', 't.id')
    //         ->where('t.class_id', $classId)
    //         ->value('total_term_marks');
    // }
    public function getTermTestTotalMarks($studentId)
    {
        // Fetch all classes
        $classes = DB::table('classes')
            ->select('id')
            ->where('status', 1)
            ->get();
    
        // Initialize a variable to hold the total score
        $totalTermMarks = 0;
    
        // Iterate over each class to calculate the total term test score for the student
        foreach ($classes as $class) {
            $classTotal = DB::table('term_tests as t')
                ->select(DB::raw('SUM(t.total_score) as total_term_marks'))
                ->leftJoin('term_test_results as r', 'r.test_id', 't.id')
                ->where('r.student_id', $studentId)
                ->where('t.class_id', $class->id)
                ->value('total_term_marks');
    
            // Add the class total to the overall total
            $totalTermMarks += $classTotal ? $classTotal : 0;
        }
    
        return $totalTermMarks;
    }
    

}
