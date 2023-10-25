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
        // get cURL resource
        $ch = curl_init();
        //$ids = [1660896, 2127647, 1596898,1691718 ,1635441, 2349196, 1891815, 1586356, 619899, 1640734, 2622054, 1627201, 845993, 3272378, 3272379];
        $ids = Manifest::where('item_name', '=', null)->take(20)->get()->groupBy('item');
        
        //dd($ids);
        //$ids = [1660896];
        $results = [];
        foreach($ids as $key => $val){
            // set url
            $extract_rules = urlencode('{
                "title" : {
                    "selector": "h1",
                    "output": "text"
                },
                "images":{
                    "selector":"#productImage",
                    "output":{
                        "src":"img@src",
                    }
                }
            }');

            curl_setopt($ch, CURLOPT_URL, 'https://app.scrapingbee.com/api/v1/?api_key=ACZW8WWZ3SNFB1DYK7RD31I381GEDUXEGHMRBQZC1X90CVLO5H8X2KAT4PI92I1W8TNDAUKCINXLTHHB&url=https://www.costco.ca/CatalogSearch?keyword='.$key.'&extract_rules=' . $extract_rules);

            // set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);



            // send the request and save response to $response
            $response = curl_exec($ch);

            // stop if fails
            if (!$response) {
              die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            }

            echo 'HTTP Status Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
            echo 'Response Body: ' . $response . PHP_EOL;
            $res = json_decode($response,true);
            if($res['title'] != ""){
                Manifest::where('item', $key)->update(['description' => $res['title'],'item_name' => json_encode($res['title']), 'images' => json_encode($res['images']['src'])]);
            }else{
                Manifest::where('item', $key)->update(['item_name' => json_encode('not_available'), 'images' => json_encode('not_available')]);
            }
            $results[] = $res;
            // close curl resource to free up system resources
        }
        curl_close($ch);
    }
}
