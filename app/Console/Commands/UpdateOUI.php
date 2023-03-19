<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateOUI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-o-u-i';

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
        $output = file_get_contents('https://standards-oui.ieee.org/oui/oui.csv');
        $output = explode(PHP_EOL, $output);

        if (count($output) > 2) {

            // Reset the mac_oui table
            $sqlMacOui = 'DELETE FROM mac_ouis;';
            $sqlMacOuiDel = DB::getPdo()->prepare($sqlMacOui);
            $sqlMacOuiDel->execute();

            $sqlMacOuiInc = 'ALTER TABLE mac_ouis AUTO_INCREMENT = 1;';
            $sqlMacOuiIncAlt = DB::getPdo()->prepare($sqlMacOuiInc);
            $sqlMacOuiIncAlt->execute();

            $c = 0;

            foreach ($output as $row) {
                if ($c > 0) {
                    // Skip the first row
                    // 0 = Registry
                    // 1 = Assignment
                    // 2 = Org Name
                    // 3 = Org Address

                    $csv = str_getcsv($row);

                    // If $csv doesn't have 4 indexes then we can't insert this oui, also something was likely off with the previous entry.
                    if (count($csv) == 4) {
                        $oui = $csv[1];
                        $orgName = $csv[2];
                        $orgAddress = $csv[3];
                        $sqlMacOui = 'INSERT INTO mac_ouis
                        (oui, organization_name, organization_address)
                        VALUES (
                            :oui,
                            :orgName,
                            :orgAddress
                        );';

                        $sqlMacOuiData = [
                            'oui' => $oui,
                            'orgName' => $orgName,
                            'orgAddress' => $orgAddress
                        ];
                        $sqlMacOuiIns = DB::getPdo()->prepare($sqlMacOui);
                        $sqlMacOuiIns->execute($sqlMacOuiData);
                        $id = DB::getPdo()->lastInsertId();
                    }
                }
                $c++;
            }
        }
    }
}
