<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getAdminDashboardItems()
    {
        $schools = DB::table('schools')->count();
        $classes = DB::table('classes')->count();
        $subjects = DB::table('subjects')->count();
        $videos = DB::table('videos')->count();
        $assessments = DB::table('assessments')->count();
        $tests = DB::table('term_tests')->count();
        $elabs = DB::table('elabs')->count();
        $ebooks = DB::table('ebooks')->count();
        $mini_projects = DB::table('mini_projects')->count();
        $project_reports = DB::table('project_reports')->count();
        $case_studies = DB::table('case_studies')->count();
        $internships = DB::table('internships')->count();
        $recruiters = DB::table('recruiters')->count();
        $jobs = DB::table('jobs')->count();
        $job_tests = DB::table('job_tests')->count();
        $subscribed_students = DB::table('students')->where('is_paid', true)->count();
        $unsubscribed_students = DB::table('students')->where('is_paid', false)->count();
        $trainers = DB::table('teachers')->count();


        $res = [
            'schools' => $schools,
            'class' => $classes,
            'subjects' => $subjects,
            'videos' => $videos,
            'assessments' => $assessments,
            'tests' => $tests,
            'eLabs' => $elabs,
            'eBooks' => $ebooks,
            'mini_projects' => $mini_projects,
            'project_reports' => $project_reports,
            'case_studies' => $case_studies,
            'internships' => $internships,
            'recruiters' => $recruiters,
            'jobs' => $jobs,
            'job_tests' => $job_tests,
            'subscribed_students' => $subscribed_students,
            'unsubscribed_students' => $unsubscribed_students,
            'trainers' => $trainers,
        ];

        return $res;
    }
}
