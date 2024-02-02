<?php
/**
 * This file is part of Wikitrek phpBOT
 * 
 * Bulk updates DataTrek items with historical data from HyperTrek using
 * information in a CSV file.
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
require '../boz-mw/autoload-with-laser-cannon.php';

//enable debug mode
//bozmw_debug();

$datatrek = wiki("datatrek");

config_wizard('../private/configDT.php');

// Process all the pages, one per line, in CSV file
$row = 1;
if (($handle = fopen("data/HTData.csv", "r")) !== FALSE) {
    $datatrek->login();
    while (($CSVdata = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //Skip header
        if ($row != 1) {
            // this object registers your proposed changes
            $PageData = $datatrek->createDataModel();

            // array is 0-based
            print("--- =/\= ---\nItem: " . $CSVdata[0] . ",");

            // query the current item
            $itemData = $datatrek->fetchSingleEntity($CSVdata[3], [
                'props' => [
                    'descriptions',
                    'labels',
                    'claims',
                ],
            ]);

            if ($CSVdata[7] != null and $CSVdata[7] != "" and $CSVdata[7] != $itemData->getLabelValue('en')) {
                print("Wrong label $langLabel: " . $itemData->getLabelValue($langLabel) . "\n");
                $episodeData->setLabelValue('en', $CSVdata[7]);
            }

            // Set P79 (database timestamp) as the proper value with precision "days"
            $statement = new \wb\StatementTime("P79", "+2016-06-19T09:03:36Z", 11);

            // Create an associative array with columns ordinal and sitelink labels
            // Iterate through the array using a loop
            /**
            * Properties from https://data.wikitrek.org/dt/index.php?title=Special:ListProperties
             * 
             * HyperTrek Pagina Tag (P81)
             * HyperTrek istante di importazione (P84)
             * HyperTrek Pagina ID (P85)
             * HyperTrek Sezione ID (P86)
             */                        
             $qualifiersList = [
                2 => 'P85',
                3 => 'P86',
                4 => 'P81',
                5 => 'P84',
            ];
            foreach ($qualifiersList as $column => $propertyNR) {
                if ($CSVdata[$column] != null and $CSVdata[$column] != "") {
                    $qualifier = new \wb\SnakString($propertyNR, $CSVdata[$column]);
                    $statement->addQualifier($qualifier);
                }
            }
            // this object registers your proposed changes
            $hypertrekData = $datatrek->createDataModel();
            $hypertrekData->addClaim($statement);

            // Set P43 (IMDB ID) as the proper string
            $statement = new \wb\StatementExternalID("P43", $CSVdata[8]);
            $hypertrekData->addClaim($statement);
            
            // Set P89 (Stardate) as the proper string
            /**
             * Check for multiple values
             * Example "51252.3,51425.4,51682.2"
             */
            if ($CSVdata[9] != null and $CSVdata[9] != "") {
                $starDates = explode(",", $CSVdata[9]);
                foreach ($starDates as $starDate) {
                    $statement = new \wb\StatementString("P89", $starDate);
                    $hypertrekData->addClaim($statement);
                }
            }

            //Dump for debug purposes
            //var_dump($hypertrekData->get());

            // Set Wikitrek sitelink
            if ($CSVdata[6] != null and $CSVdata[6] != "") {
                $sitelinks = new \wb\Sitelinks([new \wb\Sitelink("enma", $CSVdata[6])]);
                $PageData->setSitelinks($sitelinks);
            }

            // this tries to save all your proposed changes in the Wikidata Sandbox
            $hypertrekData->editEntity([
                'id' => $CSVdata[1],
                'summary' => "Set HyperTrek structured data",
                'bot' => true,
            ]);
        }
        $row++;
    }
    fclose($handle);
}
print("Done. " . $row - 2 . " rows processed.\n");