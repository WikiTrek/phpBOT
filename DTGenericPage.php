<?php
/**
 * This file is part of Wikitrek phpBOT
 * 
 * Bulk creates DataTrek items from generic page information in a CSV file.
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

// Process all the pages, one per line in CSV file
$row = 1;
if (($handle = fopen("data/PagesNoID.csv", "r")) !== FALSE) {
    $datatrek->login();
    while (($CSVdata = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //Skip header
        if ($row != 1) {
            // this object registers your proposed changes
            $PageData = $datatrek->createDataModel();

            // array is 0-based
            print("--- =/\= ---\nItem: " . $CSVdata[0] . ",");

            $PageData->setLabelValue("it", $CSVdata[0]);

            // Set P14 (Instance) as the proper Q
            $statement = new \wb\StatementItem("P14", $CSVdata[1]);
            $PageData->addClaim($statement);

            // Set Wikitrek sitelink
            $sitelinks = new \wb\Sitelinks([new \wb\Sitelink("wikitrek", $CSVdata[0])]);
            $PageData->setSitelinks($sitelinks);

            // this tries to save all your proposed changes in the Wikidata Sandbox
            // See file DataModel.php for details on the function editEntity
            print($PageData->editEntity([
                'new' => "item",
                'summary' => "Nuovo elemento per pagina originariamente importata da HT",
                'bot' => true,
            ])->entity->id . "\n");
        }
        $row++;
    }
    fclose($handle);
}
print("END of run");
