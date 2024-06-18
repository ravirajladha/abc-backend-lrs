<?php

namespace App\Console\Commands;

use Exception;
use FFMpeg\Format\Video\X264;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSVideoFilters;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ProcessVideoEnc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video-upload:processEnc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
{
    try {
        $lowFormat = (new X264('aac'))->setKiloBitrate(500);
        $highFormat = (new X264('aac'))->setKiloBitrate(1000);

        $this->info('Converting video1.mp4');
        $bar = $this->output->createProgressBar(100);

        FFMpeg::fromDisk('uploads')
            ->open('java.mp4')
            ->exportForHLS()
            ->withRotatingEncryptionKey(function ($filename, $contents) {
                Storage::disk('public')->put("videos/$filename", $contents);
            })
            ->addFormat($lowFormat, function (HLSVideoFilters $filters) {
                $filters->resize(1280, 720);
            })
            ->addFormat($highFormat)
            ->onProgress(function ($progress) use ($bar) {
                $bar->setProgress($progress);
            })
            ->toDisk('public')
            ->save('videos/java.m3u8');

        $bar->finish();
        $this->info('Done!');
    } catch (Exception $e) {
        $this->error('An error occurred: ' . $e->getMessage());
    }
}

}
