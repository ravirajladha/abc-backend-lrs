<?php

namespace App\Services\Recruiter;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    public function getRecruiterDashboardItems($recruiterId)
    {
        // Log::info("getRecruiterDashboardItems",$recruiterId);
        // Count the number of job tests
        $testsCount = DB::table('job_tests')->count();
        Log::info("getRecruiterDashboardItems22222");
        
        // Count the number of jobs for the given recruiter
        $jobsCount = DB::table('jobs')->count();
        // $jobsCount = DB::table('jobs')->where('recruiter_id', $recruiterId)->count();
        
        // Get the job IDs for the given recruiter
        $jobIds = DB::table('jobs')
            ->pluck('id');
        // $jobIds = DB::table('jobs')
        //     ->where('recruiter_id', $recruiterId)
        //     ->pluck('id');
        
        // Count job applications for the retrieved job IDs
        $jobApplicationsCount = DB::table('job_applications')
            ->whereIn('job_id', $jobIds)
            ->count();
    
        // Prepare the response array with all counts
        $res = [
            'tests_count' => $testsCount,
            'jobs_count' => $jobsCount,
            'job_applications_count' => $jobApplicationsCount,
        ];
    
        return $res;
    }
    
}
