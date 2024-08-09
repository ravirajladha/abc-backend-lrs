<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;

use App\Models\Auth as AuthModel;
use App\Models\InternshipAdmin;

use App\Http\Constants\AuthConstants;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends BaseController
{

    /**
     * Fetch dashboard items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard(Request $request, DashboardService $dashboardService)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $dashboard =  $dashboardService->getAdminDashboardItems();
            return $this->sendResponse(['dashboard' => $dashboard]);
        }
        return $this->sendAuthError("Not authorized.");
    }

    /**
     * Update admin details
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $loggedUserId = $this->getLoggedUserId();

        $auth = AuthModel::where('id', $loggedUserId)->first();

        if ($auth) {
            $auth->update([
                'password' => bcrypt($request->password),
            ]);

            $updatedAuth = AuthModel::select('id', 'username', 'email', 'phone_number')
                ->where('id', $loggedUserId)
                ->first();

            return $this->sendResponse(['auth' => $updatedAuth], 'Credentials updated successfully.');
        } else {
            return $this->sendError('Failed to update admin credentials', [], 404);
        }
    }



    /**
     * Store school
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeInternshipAdminDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|max:255|unique:auth',
            'phone_number' => 'required|string|min:10|max:10',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $auth = AuthModel::create([
                'username' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('abc123'),
                'phone_number' => $request->phone_number,
                'type' => AuthConstants::TYPE_INTERNSHIP_ADMIN,
                'status' => AuthConstants::STATUS_ACTIVE,
            ]);

            if ($auth) {
                $internshipAdmin = InternshipAdmin::create([
                    'auth_id' => $auth->id,
                    'name' => $request->name,
                    'accreditation_no' => $request->accreditation_no,
                    'logo' => $request->logo,
                    'year_of_establishment' => $request->year_of_establishment,
                    'phone_number' => $request->phone_number,
                    'address' => $request->address,
                    'pincode' => $request->pincode,
                    'city' => $request->city,
                    'state' => $request->state,
                    'student_teacher_ratio' => $request->student_teacher_ratio,
                    'website_url' => $request->website_url,
                    'description' => $request->description,
                ]);
            }
            if ($auth && $internshipAdmin) {
                return $this->sendResponse([], 'Internship Admin added successfully');
            }
        }
    }

    /**
     * Display a listing of the schools.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInternshipAdminsList(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $res = DB::table('auth as a')
                ->select('s.*', 'a.email', 'a.phone_number')
                ->leftJoin('schools as s', 's.auth_id', '=', 'a.id')
                ->where('a.type', AuthConstants::TYPE_INTERNSHIP_ADMIN)
                ->where('a.status', AuthConstants::STATUS_ACTIVE)->get();
            $internshipAdmins = InternshipAdmin::get();
            return $this->sendResponse(['internshipAdmins' => $res]);
        } else {
            return $this->sendAuthError("Not authorized fetch Internship Admins list.");
        }
    }
      /**
     * Display a listing of the public schools.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function getPublicSchoolsList(Request $request)
    // {
    //     $userType = $request->attributes->get('type');
    //     if ($userType === 'admin') {
    //         $res = DB::table('auth as a')
    //             ->select('s.*', 'a.email', 'a.phone_number')
    //             ->leftJoin('schools as s', 's.auth_id', '=', 'a.id')
    //             ->where('a.type', AuthConstants::TYPE_SCHOOL)
    //             ->where('a.status', AuthConstants::STATUS_ACTIVE)
    //             ->where('s.school_type', AuthConstants::STATUS_DISABLED)->get();
    //         $schools = School::get();
    //         return $this->sendResponse(['schools' => $res]);
    //     } else {
    //         return $this->sendAuthError("Not authorized fetch schools list.");
    //     }
    // }
      /**
     * Display a listing of the private schools.
     
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function getPrivateSchoolsList(Request $request)
    // {
    //     $userType = $request->attributes->get('type');
    //     if ($userType === 'admin') {
    //         $res = DB::table('auth as a')
    //             ->select('s.*', 'a.email', 'a.phone_number')
    //             ->leftJoin('schools as s', 's.auth_id', '=', 'a.id')
    //             ->where('a.type', AuthConstants::TYPE_SCHOOL)
    //             ->where('a.status', AuthConstants::STATUS_ACTIVE)
    //             ->where('s.school_type', AuthConstants::STATUS_ACTIVE)->get();
    //         $schools = School::get();
    //         return $this->sendResponse(['schools' => $res]);
    //     } else {
    //         return $this->sendAuthError("Not authorized fetch schools list.");
    //     }
    // }
    public function play()
    {
        $filePath = public_path('videos/video_sample.mp4');

        if (!file_exists($filePath)) {
            abort(404);
        }
    
        // If you want to directly serve the video file, you can use:
        return response()->file($filePath);

        // If you want to directly serve the video file, you can use:
        // return response()->file($filePath);
    }
}
