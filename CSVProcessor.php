<?php
// example from https://www.php.net/fgetcsv

$row = 1;
if (($handle = fopen("data/SpacedockShips.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        foreach ($data as $column) {
            if ($row == 1) {
                // Headers
                print("==" . $column . "==|");
            } else {
                //Data
                print ($column) . " | ";
            }
        }
        print($data[2] . "--" . $data[3]);
        $row++;
        print("\n");
        /*
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $row++;
        for ($c = 0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
        }
        */
    }
    fclose($handle);
}
