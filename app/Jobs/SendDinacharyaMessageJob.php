<?php

namespace App\Jobs;

use App\Models\Quote;
use App\Models\Student;
use App\Models\StudentImage;
use App\Models\DinacharyaLog;
use App\Models\ParentModel;
use App\Models\Auth;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

use App\Services\Student\DinacharyaService;

class SendDinacharyaMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $student;
    /**
     * Create a new job instance.
     */
    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $parentId = $this->student->parent_id;
        $parent_details = ParentModel::where('id', $parentId)->first();
        $student_type = $this->student->student_type;
        // Get a random image for the student
        $image = StudentImage::where('student_auth_id', $this->student->auth_id)->where('status', true)->inRandomOrder()->first();
        $imagePath = asset('public/' . $image->image_path);
        // Get a random quote
        $quote = Quote::inRandomOrder()->where('status', true)->first();
        if ($parentId && !$student_type) {

            $parent = Auth::where('id', $parent_details->auth_id)->first();

            $dinacharyaService = new DinacharyaService();
            $reponse = $dinacharyaService->sendWhatsappMessage($parent->phone_number, $imagePath, $quote->quote);


            DinacharyaLog::create([
                'student_id' => $this->student->auth_id,
                'school_id' => $this->student->school_id,
                'parent_id' => $parent_details->auth_id,
                'image_id' => $image ? $image->id : null,
                'quote_id' => $quote ? $quote->id : null,
            ]);

            // For testing, log the details
            Log::info('WhatsApp message sent to parent', [
                'student_id' => $this->student->auth_id,
                'parent_id' => $parentId,
                'image_id' => $image ? $image->id : null,
                'quote_id' => $quote ? $quote->id : null,
                'whatsapp_reponse' => $reponse,
                'phone_number' => $parent->phone_number,
                'imagePath' => $imagePath,
                'quote' => $quote->quote,
            ]);
        }
    }
}
