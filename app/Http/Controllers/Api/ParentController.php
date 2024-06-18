<?php

namespace App\Http\Controllers\Api;

use App\Models\School;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\Auth as AuthModel;

use App\Http\Constants\AuthConstants;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Services\Student\ResultService as StudentResultService;

use App\Http\Controllers\Api\BaseController;
use App\Models\OldApplication;
use App\Models\Application;


class ParentController extends BaseController
{

    /**
     * Display a listing of the parents.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParentList(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin' || $userType === 'school') {

            $parents = DB::table('parents as p')
                ->select('p.id', 'p.auth_id', 'p.school_id', 'p.name', 'p.profile_image', 'p.profession', 'p.address', 'p.pincode', 'p.city', 'p.state', 'p.dob', 'p.parent_code', 'p.relationship', 'p.remarks', 'a.email', 'a.username', 'a.phone_number', 'a.status')
                ->join('auth as a', 'p.auth_id', '=', 'a.id')
                ->where('a.status', AuthConstants::STATUS_ACTIVE)
                ->get();

            return $this->sendResponse(['parents' => $parents]);
        } else {
            return $this->sendAuthError("Not authorized to fetch parents list.");
        }
    }

    /**
     * Display the children of specified parent.
     *
     */
    public function getChildren(Request $request)
    {
        $parent = ParentModel::where('auth_id', $request->parentId)->first();
        if ($parent) {
            $children = Student::select(
                'students.auth_id as id',
                'students.id as studentId',
                'students.name',
                'auth.username'
            )
            ->join('auth', 'students.auth_id', '=', 'auth.id')
            ->where('students.parent_id', $parent->id)
            ->get();
            // $children = Student::select('auth_id as id','id as studentId', 'name')->where('parent_id', $parent->id)->get();
            return $this->sendResponse(['children' => $children], 'Children fetched successfully!');
        }
    }

    public function getStudentDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required',
        ]);

        $studentId = $request->studentId;

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $student = DB::table('students as s')
                ->leftJoin('classes as c', 's.class_id', '=', 'c.id')
                ->leftJoin('sections as sec', 's.section_id', '=', 'sec.id')
                ->leftJoin('schools as sch', 's.school_id', '=', 'sch.id')
                ->leftJoin('parents as par', 's.parent_id', '=', 'par.id')
                ->select(
                    's.*',
                    'c.name as class_name',
                    'sec.name as section_name',
                    'sch.name as school_name',
                    'par.name as parent_name',
                    'par.parent_code'
                )
                ->where('s.auth_id', $studentId)
                ->first();

            if ($student) {
                $subjects = DB::table('subjects as s')
                    ->select('s.id', 's.name', 's.image')
                    ->leftJoin('classes as c', 's.class_id', '=', 'c.id')
                    ->where('s.class_id', $student->class_id)
                    ->get();
            }

            if ($student) {
                $res = [
                    'student_id' => $student->id,
                    'student_auth_id' => $student->auth_id,
                    'student_name' => $student->name,
                    'class_id' => $student->class_id !== null ? $student->class_id : null,
                    'class_name' => $student->class_name ? $student->class_name : null,
                    'section_id' => $student->section_id !== null ? $student->section_id : null,
                    'section_name' => $student->section_name ? $student->section_name : null,
                    'school_id' => $student->school_id,
                    'school_name' => $student->school_name,
                    'subjects' => $subjects !== null ? $subjects : null,
                    'profile_image' => $student->profile_image,
                    'dob' => $student->dob,
                    'address' => $student->address,
                    'city' => $student->city,
                    'state' => $student->state,
                    'pincode' => $student->pincode,
                    'remarks' => $student->remarks,
                    'parent_id' => $student->parent_id,
                    'parent_code' => $student->parent_code,
                    'parent_name' => $student->parent_name,
                ];

                return $this->sendResponse(['student' => $res]);
            } else {
                return $this->sendResponse([], 'Failed to fetch student details.');
            }
        }
    }

    /**
     * Display the specified parent.
     *
     */
    public function getParentDetails($parentId)
    {
        $validator = Validator::make(['parent_id' => $parentId], [
            'parent_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $auth = AuthModel::find($parentId);
            $parent = ParentModel::where('auth_id', $parentId)
                ->first();

            if ($parent) {
                $auth = AuthModel::where('id', $parentId)
                    ->where('type', AuthConstants::TYPE_PARENT)
                    ->first();


                $children = DB::table('students as s')
                    ->select('s.id', 's.auth_id', 's.name', 's.class_id', 's.section_id', 'c.name as class', 'sec.name as section')
                    ->where('s.parent_id', $parent->id)
                    ->leftJoin('classes as c', 'c.id', 's.class_id')
                    ->leftJoin('sections as sec', 'sec.id', 's.section_id')
                    ->get();
            }
            if ($parent && $auth) {
                $res = [
                    'id' => $parent->id,
                    'auth_id' => $parentId,
                    'name' => $parent->name,
                    'username' => $auth->username,
                    'email' => $auth->email,
                    'phone_number' => $auth->phone_number,
                    'profile_image' => $parent->image,
                    'address' => $parent->address,
                    'city' => $parent->city,
                    'state' => $parent->state,
                    'pincode' => $parent->pincode,
                    'dob' => $parent->dob,
                    'profession' => $parent->profession,
                    'parent_code' => $parent->parent_code,
                    'relationship' => $parent->relationship,
                    'description' => $parent->remarks,
                    'children' => $children !== null ? $children : null,
                ];
                return $this->sendResponse(['parent' => $res]);
            } else {
                return $this->sendResponse([], 'Failed to fetch parent details.');
            }
        }
    }

    /**
     * Update parent details
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateParentDetails(Request $request, $parentId)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $loggedUserId = $this->getLoggedUserId();

        $auth = AuthModel::where('id', $parentId)->first();

        if ($auth) {
            $auth->update([
                'password' => bcrypt($request->password),
            ]);

            $updatedAuth = AuthModel::select('id', 'username', 'email', 'phone_number')
                ->where('id', $parentId)
                ->first();

            return $this->sendResponse(['auth' => $updatedAuth], 'Credentials updated successfully.');
        } else {
            return $this->sendError('Failed to update parent credentials', [], 404);
        }
    }

    /**
     * Fetch applications related to the parent with phone number
     */
    public function getApplications($parentPhone){
        $applications = Application::where('f_mob', $parentPhone)
        ->orWhere('m_mob', $parentPhone)
        ->get();
        $oldApplications = OldApplication::where('f_contact', $parentPhone)
        ->orWhere('m_contact', $parentPhone)
        ->get();
        $data = [
            'applications' => $applications,
            'oldApplications' => $oldApplications,
        ];
        return $this->sendResponse(['data' => $data]);

    }
}
