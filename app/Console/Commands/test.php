<?php

namespace App\Console\Commands;

use App\Models\Archive;
use App\Models\FeaturedNews;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        \App\Helpers\ModirSmsHelper::send_failed_login('09123208185','192.168.1.1','15:50','Chrome','IOS');

        dd(FeaturedNews::getNewsByBoxTitle('top_slider'));
//        $arch = Archive::where('archive_number',4972)->orderBy('id','DESC')->first();
//dd(public_path('../public_html/media'));
//        dd($arch);
////
//        $image_path = Storage::disk('public')->path('upload/01JX737FX5VKXJYDMEYDZ0BE5P.jpg');
//
//        dd($image_path);
//        $ipAddress = request()->ip();
//        $time = now()->format('H:i'); // Get the current time
//        $browser = request()->header('User-Agent'); // Get the browser info
//        $os = php_uname('s'); // Get OS (or use a package for better detection)
//
//        dd($ipAddress,$os,$browser);
    }
}
