<?php

namespace App\Http\Controllers\Api;

use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Application;
use App\Models\Auth as AuthModel;

use App\Http\Constants\AuthConstants;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Services\School\ResultService;
use App\Services\School\DashboardService;

use App\Http\Controllers\Api\BaseController;

class SchoolController extends BaseController
{
    /**
     * Fetch dashboard items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard(Request $request, DashboardService $dashboardService)
    {
        $schoolId = School::where('auth_id',  $this->getLoggedUserId())->value('id');
        $userType = $request->attributes->get('type');
        if ($userType === 'school') {
            $dashboard =  $dashboardService->getSchoolDashboardItems($schoolId);
            return $this->sendResponse(['dashboard' => $dashboard]);
        }
        return $this->sendAuthError("Not authorized.");
    }

    /**
     * Display a details of a school.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getSchoolDetailsBySchoolId($schoolId)
    {
        $res = [];
        $validator = Validator::make(['schoolId' => $schoolId], [
            'schoolId' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $auth = AuthModel::where('id', $schoolId)->where('type', AuthConstants::TYPE_SCHOOL)->first();
            $school = School::where('auth_id', $schoolId)->first();
            if ($auth && $school) {
                $res = [
                    'id' => $school->id,
                    'auth_id' => $school->auth_id,
                    'email' => $auth->email,
                    'name' => $school->name,
                    'accreditation_no' => $school->accreditation_no,
                    'logo' => $school->logo,
                    'year_of_establishment' => $school->year_of_establishment,
                    'legal_name' => $school->legal_name,
                    'phone_number' => $school->phone_number,
                    'address' => $school->address,
                    'pincode' => $school->pincode,
                    'city' => $school->city,
                    'state' => $school->state,
                    'website_url' => $school->website_url,
                    'office_address' => $school->office_address,
                    'description' => $school->description,
                    'image' => $school->image,
                ];
            }
        }
        if ($res !== null) {
            return $this->sendResponse(['school' => $res]);
        } else {
            return $this->sendResponse([], 'No Details Found!');
        }
    }

    /**
     * Display a details of a school.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getSchoolDetails(Request $request)
    {
        $res = [];
        $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');
        if ($schoolId === null) {
            return $this->sendResponse([], 'Failed to fetch school details');
        } else {
            $auth = AuthModel::where('id', $this->getLoggedUserId())->where('type', AuthConstants::TYPE_SCHOOL)->first();
            $school = School::where('auth_id', $this->getLoggedUserId())->first();
            if ($auth && $school) {
                $res = [
                    'id' => $school->id,
                    'auth_id' => $school->auth_id,
                    'email' => $auth->email,
                    'name' => $school->name,
                    'accreditation_no' => $school->accreditation_no,
                    'logo' => $school->logo,
                    'year_of_establishment' => $school->year_of_establishment,
                    'phone_number' => $school->phone_number,
                    'address' => $school->address,
                    'pincode' => $school->pincode,
                    'city' => $school->city,
                    'state' => $school->state,
                    'website_url' => $school->website_url,
                    'office_address' => $school->office_address,
                    'description' => $school->description,
                    'image' => $school->image,
                ];
            }
        }
        if ($res !== null) {
            return $this->sendResponse(['school' => $res]);
        } else {
            return $this->sendResponse([], 'Failed to fetch school details!');
        }
    }

    /**
     * Update the specified school in storage.
     *
     */
    public function updateSchoolDetails($schoolId, Request $request)
    {
        $res = [];
        $validator = Validator::make(array_merge($request->all(), ['school_id' => $schoolId]), [
            'school_id' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:auth,email,' . $schoolId . ',id',
            'phone_number' => 'required|string|min:10|max:10',
            'establishment' => 'string|min:10',
            'school_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $auth = AuthModel::find($schoolId);
            $school = School::where('auth_id', $schoolId)->first();
            if ($auth && $school) {
                $auth->update([
                    'email' =>  $request->email,
                    'password' => $request->password !== null ? Hash::make($request->password) : $auth->password,
                    'phone_number' => $request->phone_number,
                ]);

                if ($request->hasFile('school_image')) {
                    if ($school->image) {
                        File::delete(public_path($school->image));
                    }

                    $extension = $request->file('school_image')->extension();
                    $filename = Str::random(4) . time() . '.' . $extension;
                    $school->image = $request->file('school_image')->move(('uploads/images/school'), $filename);
                }

                if ($request->hasFile('logo')) {
                    if ($school->logo) {
                        File::delete(public_path($school->logo));
                    }

                    $extension = $request->file('logo')->extension();
                    $filename = Str::random(4) . time() . '.' . $extension;
                    $school->logo = $request->file('logo')->move(('uploads/images/school'), $filename);
                }

                $school->update([
                    'name' => $request->name,
                    'year_of_establishment' => $request->year_of_establishment,
                    'accreditation_no' => $request->accreditation_no,
                    'address' => $request->address,
                    'pincode' => $request->pincode,
                    'city' => $request->city,
                    'state' => $request->state,
                    'website_url' => $request->website_url,
                    'legal_name' => $request->legal_name,
                    'office_address' => $request->office_address,
                    'description' => $request->description,
                ]);

                $res = [
                    'id' => $school->id,
                    'auth_id' => $school->auth_id,
                    'email' => $auth->email,
                    'name' => $school->name,
                    'accreditation_no' => $school->accreditation_no,
                    'logo' => $school->logo,
                    'year_of_establishment' => $school->year_of_establishment,
                    'phone_number' => $school->phone_number,
                    'address' => $school->address,
                    'pincode' => $school->pincode,
                    'city' => $school->city,
                    'state' => $school->state,
                    'website_url' => $school->website_url,
                    'office_address' => $school->office_address,
                    'description' => $school->description,
                    'image' => $school->image,
                ];
            }
        }

        return $this->sendResponse(['school' => $res], 'School updated successfully');
    }

    public function getAllStudentsResults(Request $request)
    {
        $resultService = new ResultService();

        $schoolId = School::where('auth_id', $this->getLoggedUserId())->value('id');

        $results = $resultService->getSchoolResults($schoolId, $request->classId, $request->sectionId, $request->term);

        return $this->sendResponse(['results' => $results]);
    }

    public function getSchoolTeachersBySchoolId($schoolId, Request $request)
    {
        $schoolId = School::where('auth_id', $schoolId)->value('id');

        $teachers = DB::table('teachers as t')
            ->select('t.id', 't.auth_id', 't.school_id', 't.name', 't.emp_id', 't.profile_image', 't.phone_number', 't.doj', 't.address', 't.city', 't.state', 't.pincode', 't.type', 'a.email', 'a.username', 'a.phone_number', 'a.status')
            ->join('auth as a', 't.auth_id', '=', 'a.id')
            ->where('t.school_id', $schoolId)
            ->get();

        foreach ($teachers as $teacher) {
            $teacher->teacher_subjects = DB::table('teacher_subjects as ts')
                ->select('ts.subject_id', 's.name as subject_name', 'c.name as class_name')
                ->leftJoin('subjects as s', 's.id', 'ts.subject_id')
                ->leftJoin('classes as c', 'c.id', 's.class_id')
                ->where('ts.teacher_id', $teacher->id)
                ->get();
            $teacher->teacher_classes = DB::table('teacher_classes as tc')
                ->select('tc.class_id', 'c.name as class_name')
                ->leftJoin('classes as c', 'c.id', 'tc.class_id')
                ->where('tc.teacher_id', $teacher->id)
                ->get();
        }

        return $this->sendResponse(['teachers' => $teachers]);
    }

    public function getSchoolStudentsBySchoolId($schoolId, Request $request)
    {
        $schoolId = School::where('auth_id', $schoolId)->value('id');

        $students = DB::table('students as s')
            ->select('s.*', 'a.*', 'c.name as class', 'sec.name as section', 's.id as student_id')
            ->leftJoin('auth as a', 'a.id', '=', 's.auth_id')
            ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 's.section_id')
            ->where('s.school_id', $schoolId)
            ->get();
        return $this->sendResponse(['students' => $students]);
    }

    public function getSchoolApplicationsBySchoolId($schoolId, Request $request)
    {
        $applications = Application::with('remarks')->get();
        return $this->sendResponse(['applications' => $applications]);
    }
}
