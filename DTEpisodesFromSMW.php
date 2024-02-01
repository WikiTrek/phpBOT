<?php
/**
 * This file is part of Wikitrek phpBOT
 * 
 * Bulk imports episodes' data from a CSV file into proper fields and
 * properties in DataTrek.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.à
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPàOSE. See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   PHP scripts
 * @package    phpBOT
 * @author     Luca Mauri (https://github.com/lucamauri) 
 * @copyright  2023-2024 Luca Mauri
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License Version 3
 * @version    1.0.0
 * @link       https://github.com/WikiTrek/phpBOT
 */

use wb\DataValue;

require '../boz-mw/autoload-with-laser-cannon.php';

//enable debug mode
//bozmw_debug();

$datatrek = wiki("datatrek");

config_wizard('../private/configDT.php');

// Process all the episodes, one per line in CSV file
$row = 1;
if (($handle = fopen("data/Episodi.csv", "r")) !== FALSE) {
    $datatrek->login();
    while (($CSVdata = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //Skip header
        if ($row != 1) {
            // this object registers your proposed changes
            $episodeData = $datatrek->createDataModel();

            // array is 0-based
            print("--- =/\= ---\nEpisode: " . $CSVdata[0] . "\n");

            // query the current item
            $itemData = $datatrek->fetchSingleEntity($CSVdata[3], [
                'props' => [
                    'descriptions',
                    'labels',
                    'claims',
                ],
            ]);

            // Gets labels and edit them if necessary
            // Create an associative array with columns ordinal and labels
            $langData = [
                0 => 'en',
                1 => 'it',
                2 => 'de',
                15 => 'de',
            ];
            // Iterate through the array using a loop
            foreach ($langData as $column => $langLabel) {
                if ($CSVdata[$column] != null and $CSVdata[$column] != "" and $CSVdata[$column] != $itemData->getLabelValue($langLabel)) {
                    print("Wrong label $langLabel: " . $itemData->getLabelValue($langLabel) . "\n");
                    $episodeData->setLabelValue($langLabel, $CSVdata[$column]);
                }
                //Remove dummy description if existing
                //print("Substr: " . substr($itemData->getDescriptionValue($langLabel), 0, strlen("Page:")) . "\n");
                //if (substr($itemData->getDescriptionValue($langLabel), 0, strlen("Pagina:")) == "Pagina:" or substr($itemData->getDescriptionValue($langLabel), 0, strlen("Page:")) == "Page:") {
                if (substr($itemData->getDescriptionValue($langLabel), 0, 3) == "Pag") {
                    $episodeData->setDescriptionValue($langLabel, "");
                }
            }

            // Get P14 (Instance) and set it if necessary
            /*
            var_dump($itemData->getClaimsGrouped());
            $statement = new \wb\StatementItem("P14", $CSVdata[10]);
            $statements = new \wb\Claims([$statement]);
            $episodeData->setClaims($statements);
            */

            /*
            foreach ($itemData->getClaims() as $claim) {
                print("Claim: " . $claim->getMainsnak()->getProperty() . "\n");
                if ($claim->getMainsnak()->getProperty() == "P95") {
                    print("Setting P95\n");
                    $statement = new \wb\StatementTime("P95", "+" . $CSVdata[8], 11);
                    //$episodeData->addClaim($statement);
                    $claim->setMainsnak($statement);
                }
            }
            */

            // Set P14 (Instance of) as the proper string
            if ($CSVdata[10] != null and $CSVdata[10] != "") {
                //A value for Instance is supplied in the CSV file
                print("Setting P14\n");
                if ($itemData->hasClaimsInProperty("P14")) {
                    //Instance is there
                    if ($itemData->getClaimsInProperty("P14")[0]->getMainsnak()->getDataValue()->getValue()["id"] != $CSVdata[10]) {
                        /**
                         * For future reference ONLY
                         * List of misc previous tries:
                         * print("Claim type: " . $itemData->getClaimsInProperty("P14")[0]->getMainsnak()->getDataType() . "\n");
                         * print("Claim hash: " . $itemData->getClaimsInProperty("P14")[0]->getMainsnak()->getHash() . "\n");
                         * print("Claim ID: " . $itemData->getClaimsInProperty("P14")[0]->getID() . "\n");
                         * print("Claim DataValue: " . $itemData->getClaimsInProperty("P14")[0]->getMainsnak()->getDataValue()->getValue()["id"] . "\n");
                         * 
                         * Other tries:
                         * var_dump( $itemData->getClaimsGrouped() );
                         * 
                         * $itemData->getClaimsInProperty("P14")[0]->getMainsnak()->setDataValue(new DataValue("wikibase-entityid", $CSVdata[10]));
                         * $itemData->getClaimsInProperty("P14")[0]->setMainsnak($statement);
                         * $itemData->getClaimsInProperty("P14")[0]->setDataValue(new DataValue("wikibase-entityid", $CSVdata[10]));
                         */

                        //Existing instance differs from supplied one, delete it
                        $episodeData->removeClaim([
                            'claim' => $itemData->getClaimsInProperty("P14")[0]->getID(),
                            'summary' => "Remove existing P14 value (instance of)",
                            'bot' => true,
                        ]);
                        //Crate new one
                        $statement = new \wb\StatementItem("P14", $CSVdata[10]);
                        $episodeData->addClaim($statement);
                    }
                } else {
                    //Instance is NOT there, create it
                    $statement = new \wb\StatementItem("P14", $CSVdata[10]);
                    $episodeData->addClaim($statement);
                }
            }

            $columnsData = [
                // Set P1 (Production number) as the proper string
                5 => [
                    "number" => 'P1',
                    "type" =>'string',
                ],
                // Set P153 (Sequenza di trasmissione) as the proper string
                6 => [
                    "number" => 'P153',
                    "type" =>'string',
                ],
                // Set P18 (Season) to the proper value
                4 => [
                    "number" => 'P18',
                    "type" =>'quantity',
                ],
                // Set P178 (Position in list) to the proper value
                11 => [
                    "number" => 'P178',
                    "type" =>'quantity',
                ],
                // Set P95 (Original Publication date) to the proper date
                8 => [
                    "number" => 'P95',
                    "type" =>'time',
                ],
            ];
            // Iterate through the array using a loop
            foreach ($columnsData as $column => $propertyInfo) {
                if (!$itemData->hasClaimsInProperty($propertyInfo["number"]) and $CSVdata[$column] != null and $CSVdata[$column] != "") {
                    print("Setting " . $propertyInfo["number"] . "\n");
                    switch ($propertyInfo["type"]) {
                        case "quantity":
                            $statement = new \wb\StatementQuantity($propertyInfo["number"], $CSVdata[$column], null);
                            break;
                        case "string":
                            $statement = new \wb\StatementString($propertyInfo["number"], $CSVdata[$column]);
                            break;
                        case "time":
                            $statement = new \wb\StatementTime($propertyInfo["number"], "+" . $CSVdata[$column], 11);
                            break;
                        default:
                        print("Unexpected CASE\n");
                      }
                    $episodeData->addClaim($statement);
                }
            }

            // Create an associative array with columns ordinal and sitelink labels
            $sitelinkLabels = [
                13 => 'wikidata',
                14 => 'enma',
                15 => 'dema',
                16 => 'itma',
            ];
            $sitelinksList = array(new \wb\Sitelink("wikitrek", $CSVdata[0]));
            // Iterate through the array using a loop
            foreach ($sitelinkLabels as $column => $langLabel) {
                if ($CSVdata[$column] != null and $CSVdata[$column] != "") {
                    $sitelinksList[] = new \wb\Sitelink($langLabel, $CSVdata[$column]);
                }
            }
            // Set Wikitrek and Memory Alpha sitelink
            //$sitelinks = new \wb\Sitelinks([new \wb\Sitelink("wikitrek", $CSVdata[0]), new \wb\Sitelink("enma", $CSVdata[14]), new \wb\Sitelink("dema", $CSVdata[15])]);
            $sitelinks = new \wb\Sitelinks($sitelinksList);
            $episodeData->setSitelinks($sitelinks);

            /*
            $stringData = [
                // Set P1 (Production number) as the proper string
                5 => 'P1',
                // Set P153 (Sequenza di trasmissione) as the proper string
                6 => 'P153',
            ];
            // Iterate through the array using a loop
            foreach ($stringData as $column => $propertyNumber) {
                if (!$itemData->hasClaimsInProperty($propertyNumber) and $CSVdata[$column] != null and $CSVdata[$column] != "") {
                    print("Setting $propertyNumber\n");
                    $statement = new \wb\StatementString($propertyNumber, $CSVdata[$column]);
                    $episodeData->addClaim($statement);
                }
            }

            $quantityData = [
                // Set P18 (Season) to the proper value
                4 => 'P18',
                // Set P178 (Position in list) to the proper value
                11 => 'P178',
            ];
            // Iterate through the array using a loop
            foreach ($stringData as $column => $propertyNumber) {
                if (!$itemData->hasClaimsInProperty($propertyNumber) and $CSVdata[$column] != null and $CSVdata[$column] != "") {
                    print("Setting $propertyNumber\n");
                    $statement = new \wb\StatementQuantity($propertyNumber, $CSVdata[$column], null);
                    $episodeData->addClaim($statement);
                }
            }

            // Set P95 (Original Publication date) to the proper date            
            if (!$itemData->hasClaimsInProperty("P95") and $CSVdata[8] != null and $CSVdata[8] != "") {
                $statement = new \wb\StatementTime("P95", "+" . $CSVdata[8], 11);
                $episodeData->addClaim($statement);
            }
            */



            // look all the Claims
            //var_dump($episodeData->getClaimsGrouped());

          

            // this tries to save all your proposed changes in the given Q
            $episodeData->editEntity([
                'id' => $CSVdata[3],
                'summary' => "Set episode's data from Wikitrek SMW and Wikidata properties",
                'bot' => true,
            ]);
        }
        $row++;
    }
    fclose($handle);
}
print("============\nEND of run\n============");
