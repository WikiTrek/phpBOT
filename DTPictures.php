<?php
require 'boz-mw/autoload-with-laser-cannon.php';

//enable debug mode
//bozmw_debug();

$datatrek = wiki("datatrek");

config_wizard('configDT.php');

// Process all the ships, one per line in CSV file
$row = 1;
if (($handle = fopen("data/ShipsSpaceDock2.csv", "r")) !== FALSE) {
    $datatrek->login();
    // $data array is 0-based
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //Skip header
        if ($row != 1) {
            // this object registers your proposed changes
            $shipData = $datatrek->createDataModel();

            print("Nave: " . $data[4] . "\n");

            // Set P52 (Local media) as the proper Q
            $statement = new \wb\StatementLocalMedia("P52", $data[7]);
            $shipData->addClaim($statement);
            // Set P37 (Image) as the proper Q
            $statement = new \wb\StatementString("P37", $data[7]);
            $shipData->addClaim($statement);

            //$datatrek->login();

            // this tries to save all your proposed changes in the Wikidata Sandbox
            $shipData->editEntity([
                'id' => $data[6],
                'summary' => "Set ship's picture",
            ]);
        }
        $row++;
    }
    fclose($handle);

    print("Done. " . $row - 2 . " rows processed.\n");
}
