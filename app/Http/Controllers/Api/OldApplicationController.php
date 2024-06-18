<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use App\Models\OldApplication;
use App\Models\OldApplicationRemark;

use App\Services\School\WhatsappMessageService;

class OldApplicationController extends BaseController
{
    //
    //
    /**
     * Fetch the Applications Details.
     *
     */
    public function getApplications($status)
    {
        if ($status === null) {
            $applications = OldApplication::with('oldRemarks')->get();
        } else {
            $applications = OldApplication::with('oldRemarks')->where('application_status', $status)->get();
        }

        return $this->sendResponse(['applications' => $applications]);
    }

    /**
     * Update the Applications Status.
     *
     */
    public function updateApplicationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:old_applications,id',
            'new_status' => 'required',
            'updated_by' => 'required|exists:auth,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            // Find the application by ID
            $application = OldApplication::findOrFail($request->input('application_id'));
            $application->application_status = $request->input('new_status');
            $application->application_status = $request->input('new_status');
            $application->save();
        }

        return $this->sendResponse(['application' => $application], 'Application status updated successfully');
    }
    /**
     * Update the Applications Status.
     *
     */
    public function updateApplicationWhatsappStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:old_applications,id',
            'new_status' => 'required',
            'message_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            // Find the application by ID
            $application = OldApplication::findOrFail($request->input('application_id'));
            // Update the status based on the message type
            if ($request->input('message_type') == 1) {
                $application->whatsapp_status = $request->input('new_status');
            } else {
                $application->whatsapp_status_2 = $request->input('new_status');
            }
            $application->save();
        }

        return $this->sendResponse(['application' => $application], 'Application status updated successfully');
    }
    /**
     * Store the Applications Remark in application_remarks table.
     *
     */
    public function storeApplicationRemark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:old_applications,id',
            'remark' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $applicationRemark = new OldApplicationRemark();
            $applicationRemark->application_id = $request->input('application_id');
            $applicationRemark->remark = $request->input('remark');
            $applicationRemark->save();
        }

        return $this->sendResponse(['applicationRemark' => $applicationRemark], 'Application status updated successfully');
    }

    /**
     * Fetch the application details from the Old Application table by id
     *
     */
    public function getApplicationById($applicationId)
    {
        $application = OldApplication::find($applicationId);
        return $this->sendResponse(['application' => $application], 'Application Fetched successfully');
    }

    /**
     * Store the application details from csv file in the Old Application table
     *
     */
    public function uploadOldApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            set_time_limit(7200);

            $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

            if (!empty($request->file('file')) && $csvMimes) {

                if (is_uploaded_file($request->file('file'))) {

                    $csvFile = fopen($request->file('file'), 'r');
                    fgetcsv($csvFile);

                    while (($line = fgetcsv($csvFile)) !== false) {

                        $oldApplication = new OldApplication();
                        $oldApplication->month = $line[1];
                        $oldApplication->year = $line[2];
                        $oldApplication->enquiry_date = $line[3];
                        $oldApplication->student_name = $line[4];
                        $oldApplication->enquiry_class_old = $line[5];
                        $oldApplication->enquiry_class = $line[6];
                        $oldApplication->class_expected_in_2024_25 = $line[7];
                        $oldApplication->dob = $line[8];
                        $oldApplication->f_name = $line[9];
                        $oldApplication->m_name = $line[10];
                        $f_contact = explode(' ', $line[11]);
                        $oldApplication->f_contact = $f_contact[0];
                        $m_contact = explode(' ', $line[12]);
                        $oldApplication->m_contact = $m_contact[0];
                        $oldApplication->address = $line[13];
                        $oldApplication->status = $line[14];
                        $oldApplication->heard_about_us = $line[15];
                        $oldApplication->prev_school = $line[16];
                        $oldApplication->application_date = $line[17];
                        $oldApplication->admission_date = $line[18];
                        $oldApplication->admission_enquiry_for = $line[19];
                        $oldApplication->data = $line[20];
                        $oldApplication->age_as_01_06_2023 = $line[21];
                        $oldApplication->entrance_test_date = $line[22];
                        $oldApplication->entrance_test_result = $line[23];
                        $oldApplication->remarks = $line[24];
                        $oldApplication->data_2 = $line[25];
                        $oldApplication->already_enrolled = $line[26];
                        $oldApplication->save();
                    }
                    fclose($csvFile);
                }

            }
        }

        return $this->sendResponse('Old Applications uploaded successfully');
    }


    /**
     * Send Bulk Whatsapp Messages with api for old application.
     *
     */
    public function sendBulkWhatsappMessages(Request $request, $messageType)
    {
        $whatsappMessageService = new WhatsappMessageService();

        $contacts = json_decode($request->selectedPhoneNumbers);
        $ids = json_decode($request->selectedIds);

        // Iterate over each contact
        foreach ($contacts as $contact) {
            // Call the sendWhatsappMessage function for each contact
            $whatsappMessageService->sendWhatsappMessage($contact, $messageType);
        }

        if($messageType == 'announcement_website') {
            // update whatsapp status
            OldApplication::whereIn('id', $ids)
                    ->update(['whatsapp_status' => 1]);
        } elseif($messageType == 'weclome_message1') {
            OldApplication::whereIn('id', $ids)
                    ->update(['whatsapp_status_2' => 1]);
        } elseif($messageType == 'acids_bases_andsalts_1') {
            OldApplication::whereIn('id', $ids)
                    ->update(['whatsapp_status_3' => 1]);
        } elseif($messageType == 'acids_bases_andsalts_2') {
            OldApplication::whereIn('id', $ids)
                    ->update(['whatsapp_status_4' => 1]);
        }
        return $this->sendResponse('Message Sent successfully');
    }
}
