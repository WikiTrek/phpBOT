<?php
require 'boz-mw/autoload-with-laser-cannon.php';

$wikidata = wiki('datatrek');
//$wikidata = wikidata();

// query the Wikidata Sandbox
$data = $wikidata->fetchSingleEntity('Q1', [
    'props' => [
        'descriptions',
        'labels',
        'claims',
    ],
]);

// get descriptions and labels
$italian_description  = $data->getDescriptionValue('it');
$italian_label        = $data->getLabelValue('it');

// look all the Claims
var_dump($data->getClaimsGrouped());
