<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Models\ReadableCourse;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class ReadableCoursesController extends BaseController
{
    /**
     * Store the readable courses
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeReadableCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course' => 'required',
            'subject' => 'required',
            'ebook' => 'required|exists:ebooks,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $readableCourses = new ReadableCourse();
            $readableCourses->course_id = $request->course;
            $readableCourses->subject_id = $request->subject;
            $readableCourses->ebook_id = $request->ebook;
            $readableCourses->project_report_id = $request->project_report;
            $readableCourses->case_study_id = $request->case_study;
            $readableCourses->save();
            return $this->sendResponse(['readableCourses' => $readableCourses], 'Readable Course Created Successfully.');
        }
    }

    /**
     * Fetch all the ebooks, project reprot, case study for class
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllReadableCourses()
    {
        $readableCourses = ReadableCourse::select(
            'readable_courses.*',
            'ebooks.title as ebook_title',
            'project_reports.title as project_report_title',
            'case_studies.title as case_study_title',
            'courses.name as course_name',
            'subjects.name as subject_name',
        )
        ->leftJoin('ebooks', 'readable_courses.ebook_id', '=', 'ebooks.id')
        ->leftJoin('project_reports', 'readable_courses.project_report_id', '=', 'project_reports.id')
        ->leftJoin('case_studies', 'readable_courses.case_study_id', '=', 'case_studies.id')
        ->leftJoin('courses', 'readable_courses.course_id', '=', 'courses.id')
        ->leftJoin('subjects', 'readable_courses.subject_id', '=', 'subjects.id')
        ->get();
        Log::info('Course' ,['Course1' => $readableCourses]);
        return $this->sendResponse(['readableCourses' => $readableCourses], 'All Readable Courses Retrieved Successfully.');
    }


    /**
     * Fetch all the ebooks, project reprot, case study for class
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReadableCoursesByClass()
    {
        // $validator = Validator::make(['calssId' => $calssId], [
        //     'calssId' => 'required',
        // ]);
        // if ($validator->fails()) {
        //     return $this->sendValidationError($validator);
        // } else {
        
            $readableCourses = ReadableCourse::select(
                'readable_courses.*',
                'ebooks.title as ebook_title',
                'ebooks.image as ebook_image',
                'project_reports.title as project_report_title',
                'case_studies.title as case_study_title',
                'courses.name as course_name',
                'subjects.name as subject_name',
            )
            ->leftJoin('ebooks', 'readable_courses.ebook_id', '=', 'ebooks.id')
            ->leftJoin('project_reports', 'readable_courses.project_report_id', '=', 'project_reports.id')
            ->leftJoin('case_studies', 'readable_courses.case_study_id', '=', 'case_studies.id')
            ->leftJoin('courses', 'readable_courses.course_id', '=', 'courses.id')
            ->leftJoin('subjects', 'readable_courses.subject_id', '=', 'subjects.id')
            ->get();
        Log::info('Course' ,['Course' => $readableCourses]);

            return $this->sendResponse(['readableCourses' => $readableCourses], 'All Readable Courses Retrieved Successfully.');
        // }
    }
}
