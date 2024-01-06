<?php
require 'boz-mw/autoload-with-laser-cannon.php';

//$wiki = itwiki();
$wiki = wiki('wikitrek');

$request =
    $wiki->createQuery([
        'action'  => 'query',
        'list'    => 'categorymembers',
        'cmtitle' => 'Categoria:Flotta Stellare - Classe Excelsior',
    ]);

// automatic query continuation
foreach ($request as $response) {
    print("Request");

    // loop each category member
    $pages = $response->query->categorymembers ?? [];
    foreach ($pages as $page) {
        //print("Page");

        // do something
        print("=============================================================\n");
        print($page->title . "\n=============================================================\n");
        var_dump($page->pageid);
        var_dump($page->ns);
        var_dump($page->title);
        print_r($page);
    }
}
