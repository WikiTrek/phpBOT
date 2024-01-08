<?php
require 'boz-mw/autoload-with-laser-cannon.php';

//enable debug mode
//bozmw_debug();

$datatrek = wiki("datatrek");

config_wizard('configDT.php');

// Process all the episodes, one per line in CSV file
$row = 1;
if (($handle = fopen("data/DSC5.csv", "r")) !== FALSE) {
    $datatrek->login();
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //Skip header
        if ($row != 1) {
            // this object registers your proposed changes
            $episodeData = $datatrek->createDataModel();

            // array is 0-based
            print("Episode: " . $data[2] . "\n");

            // Set P18 (Season) as "1"
            $statement = new \wb\StatementQuantity("P18", 5, null);
            $episodeData->addClaim($statement);
            
            // Set P1 (Production number) as the proper string
            $statement = new \wb\StatementString("P1", $data[1]);
            $episodeData->addClaim($statement);

            // Set P7 (Previous) as the proper Q
            if ($data[4] != "" and $data[4] != null) {
                $statement = new \wb\StatementItem("P7", $data[4]);
                $episodeData->addClaim($statement);
            }
            // Set P23 (Next) as the proper Q
            if ($data[5] != "" and $data[5] != null) {
                $statement = new \wb\StatementItem("P23", $data[5]);
                $episodeData->addClaim($statement);
            }
            // Set P95 (Original Publication date) as the proper Q
            /*
            $statement = new \wb\StatementTime("P95", "+" . $data[8], 11);
            $episodeData->addClaim($statement);
            */

            // Set P178 (Sequence) as the proper string
            $statement = new \wb\StatementString("P178", $data[0]);
            $episodeData->addClaim($statement);

            // Set Wikitrek sitelink
            $sitelinks = new \wb\Sitelinks([new \wb\Sitelink("wikitrek", $data[2])]);
            $episodeData->setSitelinks($sitelinks);

            // Set Wikitrek and Memory Alpha sitelink
            /*
            $sitelinks = new \wb\Sitelinks([new \wb\Sitelink("wikitrek", $data[2]), new \wb\Sitelink("enma", $data[2] . " (episode)")]);
            $episodeData->setSitelinks($sitelinks);
            */

            // this tries to save all your proposed changes in the Wikidata Sandbox
            // See file DataModel.php for details on the function editEntity
            $episodeData->editEntity([
                'id' => $data[3],
                'summary' => "Set basic episode's data",
                'bot' => true,
            ]);
        }
        $row++;
    }
    fclose($handle);
}
print("END of run");
