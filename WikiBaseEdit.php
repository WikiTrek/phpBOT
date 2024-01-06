<?php
require 'boz-mw/autoload-with-laser-cannon.php';

//enable debug mode
bozmw_debug();

//$wikidata = wiki('wikidatawiki');
$wikidata = wiki("datatrek");

// load a dedicated configuration - it will create that if missing
config_wizard('configDT.php');

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
$statement = new \wb\StatementString("P37", "Diplomatic Orders Vulcan starbase.jpg");
$newdata->addClaim($statement);
$statement = new \wb\StatementLocalMedia("P52", "Diplomatic Orders Vulcan starbase.jpg");
$newdata->addClaim($statement);

$wikidata->login();

// this tries to save all your proposed changes in the Wikidata Sandbox
$newdata->editEntity([
    'id' => 'Q1',
    'summary' => "Test edit",
]);
