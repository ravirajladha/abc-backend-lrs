<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectReport;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class ProjectReportController extends BaseController
{

    /**
     * Display a listing of all ProjectReports.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectReportList()
    {
        $projectReports = DB::table('project_reports as p')
            ->select('p.id', 'p.title', 'p.description', 'c.name as course_name', 'p.image', 'p.subject_id', 'p.course_id', 'subject.name as subject_name', 'p.chapter_id')
            ->leftJoin('courses as c', 'p.course_id', 'c.id')
            ->leftJoin('subjects as subject', 'p.subject_id', 'subject.id')
            ->get();

        return $this->sendResponse(['projectReports' => $projectReports]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProjectReportDetails(Request $request)
    {
        // return $this->sendError($request->all());
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'course' => 'required|exists:courses,id',
            'subject' => 'required|exists:subjects,id',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {

            $projectReport = new ProjectReport();

            $projectReport->title = $request->title;
            $projectReport->description = $request->description;
            $projectReport->course_id = $request->course;
            $projectReport->subject_id = $request->subject;

            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $projectReport->image = $request->file('image')->move(('uploads/images/project-report'), $filename);
            } else {
                $projectReport->image = null;
            }

            $projectReport->save();

            return $this->sendResponse([], 'project Report created successfully');
        }
    }

    public function getProjectReport($projectReportId)
    {
        $validator = Validator::make(['projectReportId' => $projectReportId], [
            'projectReportId' => 'required|exists:project_reports,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReport = ProjectReport::with([
                'modules:id,project_report_id,title',
                'modules.sections:id,project_report_module_id,title',
                'modules.sections.elements', // Add other element fields here
            ])
                ->select('id', 'title', 'image')
                ->find($projectReportId);

            return $this->sendResponse(['projectReport' => $projectReport]);
        }
    }

      /**
     * Update the specified Project report in the database.
     *
     * @param  int  $projectReportId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProjectReportDetails($projectReportId, Request $request)
    {
        $validator = Validator::make(array_merge(['projectReportId' => $projectReportId], $request->all()), [
            'projectReportId' => 'required|exists:project_reports,id',
            'title' => 'required',
            'description' => 'required',
            'course' => 'required|exists:courses,id',
            'subject' => 'required|exists:subjects,id',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $projectReport = ProjectReport::find($projectReportId);

            if (!$projectReport) {
                return $this->sendError('project Report not found');
            }

            $projectReport->title = $request->title;
            $projectReport->description = $request->description;
            $projectReport->course_id = $request->course;
            $projectReport->subject_id = $request->subject;

            if (!empty($request->file('image'))) {
                $extension = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $projectReport->image = $request->file('image')->move(('uploads/images/project-report'), $filename);
            }

            $projectReport->save();

            return $this->sendResponse([], 'project Report updated successfully');
        }
    }
    /**
     * Display the details of the specified resource.
     *
     * @param  int  $projectReportId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectReportDetails($projectReportId)
    {
        $validator = Validator::make(['projectReportId' => $projectReportId], [
            'projectReportId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $project_report = DB::table('project_reports as e')
                ->select('e.title as project_report_title', 'e.description as project_report_description', 'course.name as course_name', 'e.image as project_report_image', 'e.course_id', 'e.subject_id', 's.name as subject_name')
                ->leftJoin('subjects as s', 'e.subject_id', 's.id')
                ->leftJoin('courses as course', 'e.course_id', 'course.id')
                ->where('e.id', $projectReportId)
                ->first();

            return $this->sendResponse(['project_report' => $project_report]);
        }
    }

      /**
     * Remove the specified Project report from the database.
     *
     * @param  int  $projectReportId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProjectReportDetails($projectReportId)
    {
        $projectReport = ProjectReport::find($projectReportId);

        if (!$projectReport) {
            return $this->sendError('Project Report not found');
        }

        $projectReport->delete();

        return $this->sendResponse([], 'Project Report deleted successfully');
    }
}
