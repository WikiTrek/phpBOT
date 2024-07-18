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
if (($handle = fopen("data/PROItaS2.csv", "r")) !== FALSE) {
    $datatrek->login();
    while (($CSVdata = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //Skip header
        if ($row != 1) {
            // this object registers your proposed changes
            $episodeData = $datatrek->createDataModel();

            // array is 0-based
            print("Episode: " . $CSVdata[2] . "\n");

            // Set title in italian
            $episodeData->setLabelValue("it", $CSVdata[2]);

            // Set P93 (Monolingual synopsis) as proper string
            $statement = new \wb\StatementMonolingualText("P93", "it", $CSVdata[5]);
            $episodeData->addClaim($statement);
            
            // Set P162 (Synopsis) as the proper string
            $statement = new \wb\StatementString("P162", $CSVdata[5]);
            $episodeData->addClaim($statement);

            // Set P101 (Publication date in italy) as the proper value
            $statement = new \wb\StatementTime("P101", "+2024-07-01", 11);
            $qualifier = new \wb\SnakString("P4", "Netflix");
            $statement->addQualifier($qualifier);
            $episodeData->addClaim($statement);

            // this tries to save all your proposed changes in the Wikidata Sandbox
            // See file DataModel.php for details on the function editEntity
            $episodeData->editEntity([
                'id' => $CSVdata[0],
                'summary' => "Set italian title and synopsis",
                'bot' => true,
            ]);
        }
        $row++;
    }
    fclose($handle);
}
print("END of run");