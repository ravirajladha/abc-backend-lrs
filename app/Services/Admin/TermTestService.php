<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;

class TermTestService
{
    public function getTermTestTotalMarks($classId, $term)
    {
        return DB::table('term_tests as t')
            ->select(DB::raw('SUM(t.total_score) as total_term_marks'))
            ->leftJoin('term_test_results as r', 'r.test_id', 't.id')
            ->where('t.class_id', $classId)
            ->where('t.term_type', $term)
            ->value('total_term_marks');
    }
}
