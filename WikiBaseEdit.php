<?php
/**
 * This file is part of Wikitrek phpBOT
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   PHP scripts
 * @package    phpBOT
 * @author     Luca Mauri (@lucamauri) 
 * @copyright  2023-2024 Luca Mauri
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License Version 3
 * @version    1.0.0
 * @link       https://github.com/WikiTrek/phpBOT
 */

 require '../boz-mw/autoload-with-laser-cannon.php';

//enable debug mode
bozmw_debug();

//$wikidata = wiki('wikidatawiki');
$wikidata = wiki("datatrek");

// load a dedicated configuration - it will create that if missing
config_wizard('../private/configDT.php');

// this object registers your proposed changes
$newdata = $wikidata->createDataModel();

/*
// prepare a new Wikimedia Commons category
$statement = new \wb\StatementCommonsCategory('P373', 'Test category name');
$newdata->addClaim($statement);

$newdata->setLabelValue('en', "New label");

$newdata->setDescriptionValue('en', "New Description");

$wikidata->login();

// this tries to save all your proposed changes in the Wikidata Sandbox
$newdata->editEntity([
    'id' => 'Q4115189',
]);
*/

// As a test, set P88 = Q11051 in Q1
/*
$statement = new \wb\StatementItem("P88", "Q11051");
$newdata->addClaim($statement);

$wikidata->login();
*/

// As a test, set P37 and P52 in Q1
$statement = new \wb\StatementString("P37", "VulcanConstructionYards.jpg");
$newdata->addClaim($statement);
$statement = new \wb\StatementLocalMedia("P52", "VulcanConstructionYards.jpg");
$newdata->addClaim($statement);

$wikidata->login();

// this tries to save all your proposed changes in the Wikidata Sandbox
$newdata->editEntity([
    'id' => 'Q1',
    'summary' => "Test edit",
]);
