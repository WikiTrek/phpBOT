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

            // Set P88 (ship class) as the proper Q
            $statement = new \wb\StatementItem("P88", $data[9]);
            $shipData->addClaim($statement);
            // Set P87 (Registry number) as the proper Q
            $statement = new \wb\StatementString("P87", $data[3]);
            $shipData->addClaim($statement);

            //$datatrek->login();

            // this tries to save all your proposed changes in the Wikidata Sandbox
            $shipData->editEntity([
                'id' => $data[6],
                'summary' => "Set ship's class and registry",
            ]);
        }
        $row++;
    }
    fclose($handle);

    print("Done. " . $row - 2 . " rows processed.\n");
}
