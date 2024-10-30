<?php
/**
 * This file is part of Wikitrek phpBOT
 * 
 * Bulk set italian information on a group of existing episodes.
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

config_wizard('../private/configDT.php');

// Process all the episodes, one per line in CSV file
$row = 1;
if (($handle = fopen("data/semanticTNG.csv", "r")) !== FALSE) {
    $datatrek->login();
    while (($CSVdata = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //Skip header
        if ($row != 1) {
            // this object registers your proposed changes
            $episodeData = $datatrek->createDataModel();

            // array is 0-based
            print("Episode: " . $CSVdata[0] . "\n");

            /**
             * Languages from Hypertrek pages
             * 
             */
            // Create an associative array with columns ordinal and properties numbers
            $languagesList = [
                15 => 'es',
                16 => 'fr',
                17 => 'it',
                /*18 => 'jp',*/
                19 => 'de',
                26 => 'pt',
            ];
            // Iterate through the array using a loop
            foreach ($languagesList as $column => $langISO) {
                if ($CSVdata[$column] != null and $CSVdata[$column] != "") {
                    $episodeData->setLabelValue($langISO, $CSVdata[$column]);
                }
            }

            // Set P192 (Japanese title) as the proper string
            $statement = new \wb\StatementString("P195", $CSVdata[18]);
            $episodeData->addClaim($statement);

            // Set P194 (VHS UK) as the proper string
            $statement = new \wb\StatementString("P194", $CSVdata[20]);
            $episodeData->addClaim($statement);

            // Set P195 (TNG Companion) as the proper string
            $statement = new \wb\StatementString("P192", $CSVdata[2]);
            $episodeData->addClaim($statement);

            // Set P101 (Publication date in italy) as the proper value
            $statement = new \wb\StatementTime("P101", "+" . $CSVdata[3], 11);
            $qualifier = new \wb\SnakString("P4", "Italia 1");
            $statement->addQualifier($qualifier);
            $episodeData->addClaim($statement);

            /**
             * Sets the "Script date" (P196) property of the episode data model to the value from the CSV data.
             * The date is expected to be in the format "YYYY-MM-DD".
             *
             * @param string $CSVdata[27] The date value from the CSV data.
             */
            if ($CSVdata[27] != null and $CSVdata[27] != "") {
                $statement = new \wb\StatementTime("P196", "+" . $CSVdata[27], 11);
                $episodeData->addClaim($statement);
            }

            // Set P193 (DVD number) as the proper string
            if ($CSVdata[5] != null and $CSVdata[5] != "") {
                $statement = new \wb\StatementString("P193", $CSVdata[5]);
                $episodeData->addClaim($statement);
            }

            /**
             * Properties from https://data.wikitrek.org/dt/index.php?title=Special:ListProperties
             * 
             * Director (P57) 
             * Sceneggiatura di (P60)
             * Storia di (P118)
             * Music by (P125)
             */
            // Create an associative array with columns ordinal and properties numbers
            $authorsList = [
                8 => 'P57',
                10 => 'P60',
                12 => 'P118',
                22 => 'P125',
            ];
            // Iterate through the array using a loop
            foreach ($authorsList as $column => $propertyNR) {
                if ($CSVdata[$column] != null and $CSVdata[$column] != "") {
                    $authors = explode(",", $CSVdata[$column]);
                    foreach ($authors as $author) {
                        $statement = new \wb\StatementItem($propertyNR, $author);
                        $episodeData->addClaim($statement);
                    }
                }
            }

            /**
             * Properties from https://data.wikitrek.org/dt/index.php?title=Special:ListProperties
             * 
             * Precedente (P7)
             * Successivo (P23)
             */
            // Create an associative array with columns ordinal and properties numbers
            $sequenceList = [
                24 => 'P7',
                25 => 'P23',
            ];
            // Iterate through the array using a loop
            foreach ($sequenceList as $column => $propertyNR) {
                if ($CSVdata[$column] != null and $CSVdata[$column] != "") {
                    $statement = new \wb\StatementItem($propertyNR, $CSVdata[$column]);
                    $episodeData->addClaim($statement);
                }
            }

            // this tries to save all your proposed changes in the Wikidata Sandbox
            // See file DataModel.php for details on the function editEntity
            $episodeData->editEntity([
                'id' => $CSVdata[1],
                'summary' => "Set episode data from semantic export",
                'bot' => true,
            ]);
        }
        $row++;
        // Fragmet to exit the cycle for a test run
        if ($row > 300) {
            break;
        }
    }
    fclose($handle);
}
print("END of run");