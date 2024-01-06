<?php

use wb\Statement;

require 'boz-mw/autoload-with-laser-cannon.php';

//enable debug mode
//bozmw_debug();

$datatrek = wiki("datatrek");

config_wizard('configDT.php');

// Process all the pages, one per line, in CSV file
$row = 1;
if (($handle = fopen("data/HTData.csv", "r")) !== FALSE) {
    $datatrek->login();
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //Skip header
        if ($row != 1) {
            /*
            Example of data string
            Timestamp DB|Pagina tag |Istante|Pagina ID|Sezione ID|ItemID
            2016-06-19|dekkerthomas|2018-05-20T16:20:52|1086|179|Q9319
            */
            print("Tag: " . $data[1] . "\n");

            // Set P79 (database timestamp) as the proper value with precision "days"
            $statement = new \wb\StatementTime("P79", $data[0], 11);

            $qualifier = new \wb\SnakString("P81", $data[1]);
            $statement->addQualifier($qualifier);

            $qualifier = new \wb\SnakString("P84", $data[2]);
            $statement->addQualifier($qualifier);

            $qualifier = new \wb\SnakString("P85", $data[3]);
            $statement->addQualifier($qualifier);

            $qualifier = new \wb\SnakString("P86", $data[4]);
            $statement->addQualifier($qualifier);

            // this object registers your proposed changes
            $hypertrekData = $datatrek->createDataModel();
            $hypertrekData->addClaim($statement);

            //Dump for debug purposes
            //var_dump($hypertrekData->get());

            //$datatrek->login();

            // this tries to save all your proposed changes in the Wikidata Sandbox
            $hypertrekData->editEntity([
                'id' => $data[5],
                'summary' => "Set HyperTrek structured data",
                'bot' => true,
            ]);
        }
        $row++;
    }
    fclose($handle);

    print("Done. " . $row - 2 . " rows processed.\n");
}
