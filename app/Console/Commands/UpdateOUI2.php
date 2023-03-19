<?php

namespace App\Console\Commands;

use App\Models\mac_oui;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateOUI2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-o-u-i2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //  reset the table
        DB::table('mac_ouis')->truncate();
        $output = file_get_contents('https://standards-oui.ieee.org/oui/oui.csv');
        $output = explode(PHP_EOL, $output);

        $data =array();
        if (count($output) > 2) {

            $c = 0;

            foreach ($output as $row) {
                if ($c > 0) {
                    $now = Carbon::now()->toDateTimeString();
                    // Skip the first row
                    // 0 = Registry
                    // 1 = Assignment
                    // 2 = Org Name
                    // 3 = Org Address

                    $csv = str_getcsv($row);

                    // If $csv doesn't have 4 indexes then we can't insert this oui, also something was likely off with the previous entry.
                    if (count($csv) == 4) {
                        $data[] = [
                            'oui'=>$csv[1],
                            'organization_name'=> $csv[2],
                            'organization_address'=> $csv[3],
                            'created_at'=> $now,
                            'updated_at' => $now
                        ];
                    }
                }
                $c++;
            }
        }
        //  batch the inserts to work around the limits
        foreach(array_chunk($data, 10000) as $t)
        {
            mac_oui::insert($t);
        }

    }
}
