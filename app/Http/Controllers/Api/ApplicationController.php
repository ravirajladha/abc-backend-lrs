<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use App\Models\Application;
use App\Models\ApplicationRemark;

use App\Services\School\WhatsappMessageService;

class ApplicationController extends BaseController
{
    //
    /**
     * Fetch the Applications Details.
     *
     */
    public function getApplications($status)
    {
        if ($status === null) {
            $applications = Application::with('remarks')->get();
        } else {
            $applications = Application::with('remarks')->where('application_status', $status)->get();
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
            'application_id' => 'required|exists:applications,id',
            'new_status' => 'required',
            'updated_by' => 'required|exists:auth,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            // Find the application by ID
            $application = Application::findOrFail($request->input('application_id'));
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
            'application_id' => 'required|exists:applications,id',
            'new_status' => 'required',
            'message_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            // Find the application by ID
            $application = Application::findOrFail($request->input('application_id'));
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
            'application_id' => 'required|exists:applications,id',
            'remark' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $applicationRemark = new ApplicationRemark();
            $applicationRemark->application_id = $request->input('application_id');
            $applicationRemark->remark = $request->input('remark');
            $applicationRemark->save();
        }

        return $this->sendResponse(['applicationRemark' => $applicationRemark], 'Application status updated successfully');
    }

    /**
     * Store the Applications Remark in application_remarks table.
     *
     */
    public function getApplicationById($applicationId)
    {
        $application = Application::find($applicationId);
        return $this->sendResponse(['application' => $application], 'Application Fetched successfully');
    }

    /**
     * Send single Whatsapp Messages with api for application.
     *
     */
    public function sendWhatsappMessage($contact, $messageType){
        $whatsappMessageService = new WhatsappMessageService();
        $whatsappMessageService->sendWhatsappMessage($contact, $messageType);
        return $this->sendResponse('Message Sent successfully');
    }

    /**
     * Send Bulk Whatsapp Messages with api for application.
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
            $application = Application::whereIn('id', $ids);
            Application::whereIn('id', $ids)
                    ->update(['whatsapp_status' => 1]);
        } elseif($messageType == 'weclome_message1') {
            $application = Application::whereIn('id', $ids);
            Application::whereIn('id', $ids)
                    ->update(['whatsapp_status_2' => 1]);
        } elseif($messageType == 'acids_bases_andsalts_1') {
            Application::whereIn('id', $ids)
                    ->update(['whatsapp_status_3' => 1]);
        } elseif($messageType == 'acids_bases_andsalts_2') {
            Application::whereIn('id', $ids)
                    ->update(['whatsapp_status_4' => 1]);
        }

        return $this->sendResponse('Message Sent successfully');
    }



}
