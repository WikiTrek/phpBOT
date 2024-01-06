<?php

require 'boz-mw/autoload-with-laser-cannon.php';

// load a dedicated configuration - it will create that if missing
config_wizard('configWikidata.php');

$wikidata = wiki('wikidatawiki');

// this object registers your proposed changes
$newdata = $wikidata->createDataModel();

$timeObject = new \DateTime();
$timeObject->setTimezone(new \DateTimeZone('UTC'));
$timeObject->setDate(2016, 6, 19);
$timeObject->setTime(9, 3, 36);

$timeString = "+2016-06-19T09:03:36Z";

// Set P571 (inception date)
$statement = new \wb\StatementTime("P571", $timeString, 11);
$newdata->addClaim($statement);

// Set P2002 (Twitter external ID)
/* $statement = new \wb\StatementExternalID("P2002", "testaccount");
$newdata->addClaim($statement); */

/*
$newdata->setLabelValue('en', "New label");
$newdata->setDescriptionValue('en', "New Description");
*/
$wikidata->login();

// this tries to save all your proposed changes in the Wikidata Sandbox
$newdata->editEntity([
    'id' => 'Q4115189',
    'summary' => "Test time precision with boz-mw",
]);

print("Done.\n");
