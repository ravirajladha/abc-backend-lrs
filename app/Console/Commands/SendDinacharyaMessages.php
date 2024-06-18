<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Jobs\SendDinacharyaMessageJob;
class SendDinacharyaMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:dinacharya-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp messages to parents with a random image and quote for their student';
    /**
     * Execute the console command.
     */

     public function __construct()
     {
         parent::__construct();
     }


    public function handle()
    {
        $students = Student::where('student_type', 0)->get();

        foreach ($students as $student) {
            // Dispatch the job for each student
            SendDinacharyaMessageJob::dispatch($student);
        }

        $this->info('WhatsApp messages have been dispatched for all students.');
    }
}
