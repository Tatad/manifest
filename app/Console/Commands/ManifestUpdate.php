<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Manifest;

class ManifestUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:manifest-update';

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
        ini_set('max_execution_time', 2380);
        //
        $ids = Manifest::where('item_name', '=', null)->take(15)->get()->groupBy('item');

        foreach($ids as $key => $key){
            Manifest::where('item', $key)->update(['item_name' => 'na']);
        }
    }
}
