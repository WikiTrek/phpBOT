<?php
/**
 * This file is part of Wikitrek phpBOT
 * 
 * Bulk creates DataTrek items for episodes from tabular information in a CSV file.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
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
require '../boz-mw/autoload-with-laser-cannon.php';

//enable debug mode
//bozmw_debug();

$datatrek = wiki("datatrek");
$previousItem = "";

config_wizard('../private/configDT.php');

// Process all the pages, one per line in CSV file
$row = 1;
if (($handle = fopen("data/LD5Create.csv", "r")) !== FALSE) {
    $datatrek->login();
    while (($CSVdata = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //Skip header
        if ($row != 1) {
            // this object registers your proposed changes
            $episodeData = $datatrek->createDataModel();

            // array is 0-based
            print("--- =/\= ---\nItem: " . $CSVdata[0] . ",");

            $episodeData->setLabelValue("en", $CSVdata[0]);
            $episodeData->setLabelValue("fr", $CSVdata[4]);

            // Set P14 (Instance) as the proper Q
            $statement = new \wb\StatementItem("P14", $CSVdata[1]);
            $episodeData->addClaim($statement);

            // Set Wikitrek sitelink
            $sitelinks = new \wb\Sitelinks([new \wb\Sitelink("wikitrek", $CSVdata[0])]);
            $episodeData->setSitelinks($sitelinks);

            // Set P18 (Season) as proper  number
            $statement = new \wb\StatementQuantity("P18", $CSVdata[8], null);
            $episodeData->addClaim($statement);

            // Set P178 (Position) as the proper number
            $statement = new \wb\StatementQuantity("P178", $CSVdata[6], null);
            $episodeData->addClaim($statement);

            // Set P1 (Production number) as the proper string
            $statement = new \wb\StatementString("P1", $CSVdata[5]);
            $episodeData->addClaim($statement);

            // Set P95 (Original Publication date) to the proper date            
            if ($CSVdata[7] != null and $CSVdata[7] != "") {
                $statement = new \wb\StatementTime("P95", "+" . $CSVdata[7], 11);
                $qualifier = new \wb\SnakString("P4", "Paramount+");
                $statement->addQualifier($qualifier);
                $episodeData->addClaim($statement);
            }

            // Set P7 (Previous) as the proper Q
            if ($previousItem != "") {
                $statement = new \wb\StatementItem("P7", $previousItem);
                $episodeData->addClaim($statement);
            }

            // Set P32 (Duration) as the proper quantity
            if ($CSVdata[9] != null and $CSVdata[9] != "") {
            $statement = new \wb\StatementQuantity("P32", $CSVdata[9], null);
            $episodeData->addClaim($statement);
            }

            // this tries to save all your proposed changes in the Wikidata Sandbox
            // See file DataModel.php for details on the function editEntity
            $previousItem = $episodeData->editEntity([
                'new' => "item",
                'summary' => "Nuovo episodio da importazione massiva",
                'bot' => true,
            ])->entity->id;
            print($previousItem . "\n");
        }
        $row++;
    }
    fclose($handle);
}
print("END of run");
