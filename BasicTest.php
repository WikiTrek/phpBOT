<?php
require '../boz-mw/autoload-with-laser-cannon.php';

// load the Italian Wikipedia
//$wiki = wiki('itwiki');
$wiki = wiki('wikitrek');

// search this specific title
$search_title = "Pagina principale";

$response =
    $wiki->fetch([
        'action' => 'query',
        'prop'   => 'info',
        'titles' => $search_title,
    ]);

// give me the first damn page - whatever it is
$pages = (array) $response->query->pages;
$page = array_pop($pages);

// show interesting information
var_dump($page->title);
var_dump($page->pageid);
