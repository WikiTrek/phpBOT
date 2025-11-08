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
 
function fetchPages($continue = null) {
    $url = "https://memory-alpha.fandom.com/api.php?action=query&list=allpages&aplimit=max&format=json";
    if ($continue) {
        $url .= "&apcontinue=" . urlencode($continue);
    }
    return json_decode(file_get_contents($url), true);
}

function fetchLangLinks($title) {
    $url = "https://memory-alpha.fandom.com/api.php?action=query&titles=" . urlencode($title) . "&prop=langlinks&lllimit=max&format=json";
    $data = json_decode(file_get_contents($url), true);
    
    $langlinks = [];
    if (isset($data['query']['pages'])) {
        foreach ($data['query']['pages'] as $page) {
            if (isset($page['langlinks'])) {
                foreach ($page['langlinks'] as $link) {
                    $langlinks[$link['lang']] = $link['*'];
                }
            }
        }
    }
    return $langlinks;
}

function getTotalPages() {
    $url = "https://memory-alpha.fandom.com/api.php?action=query&meta=siteinfo&siprop=statistics&format=json";
    $data = json_decode(file_get_contents($url), true);
    return $data['query']['statistics']['pages'] ?? 10000; // Fallback to estimate if API fails
}

function showProgressBar($done, $total, $size = 30) {
    $percent = (float) ($done / $total);
    $bar = floor($percent * $size);
    
    $progress = "[" . str_repeat("=", $bar) . str_repeat(" ", $size - $bar) . "]";
    printf("\r%s %d%%", $progress, $percent * 100);
    flush();
}

$csvFile = fopen("data/memory_alpha_pages.csv", "w");
fputcsv($csvFile, ["English Title", "German Title", "Italian Title"]);

$continue = null;
$totalPages = getTotalPages();
$processedPages = 0;
do {
    $response = fetchPages($continue);
    if (isset($response['query']['allpages'])) {
        $batchSize = count($response['query']['allpages']);
        foreach ($response['query']['allpages'] as $page) {
            $title = $page['title'];
            $langlinks = fetchLangLinks($title);
            
            $deTitle = $langlinks['de'] ?? "";
            $itTitle = $langlinks['it'] ?? "";
            
            fputcsv($csvFile, [$title, $deTitle, $itTitle]);
            $processedPages++;
            showProgressBar($processedPages, $totalPages);
        }
    }
    $continue = $response['continue']['apcontinue'] ?? null;
    sleep(1); // To avoid API rate limits
} while ($continue);

echo "\nExport completed: memory_alpha_pages.csv\n";
fclose($csvFile);
?>