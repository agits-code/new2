<?php

require "AmazonFeeds.php";

$db = require "bootstrap.php";

$list = AmazonFeeds::getItems('https://assoc-datafeeds-eu.amazon.com/datafeed/listFeeds');

foreach ($list as $item) {
    if ( ! (strpos($item['name'], '.xml.')) ) {
        $exists = $db->query_all(
            "SELECT * From myfiles WHERE filename='{$item['name']}' AND filedate='{$item['date']}';"
        );
        if ( ! $exists ) {

            $db->query("INSERT INTO myfiles (filename , filedate , filesize , md5 , link )
                 VALUES ('{$item['name']}','{$item['date']}','{$item['size']}','{$item['code']}','{$item['link']}');");
        }
    }

}
$db->query("DELETE FROM myfiles WHERE isread=true AND filedate < UNIX_TIMESTAMP() - 3600*24*30;");
$files = $db->selectAll('myfiles');
require "index.view.php";
die();
$downl_file = $db->query_first(
    "SELECT * FROM myfiles WHERE downloaded=false ORDER BY filedate ASC LIMIT 1;"
);
if ( $downl_file ) {
    $endCursor = AmazonFeeds::downloadFile(
        $downl_file->ID, $downl_file->filesize, $downl_file->link, $downl_file->filecursor
    ); //ok passo passo
    $now = time();
    $db->query("UPDATE myfiles SET updated= '$now' WHERE ID='$downl_file->ID';");
    $db->query("UPDATE myfiles SET filecursor='$endCursor' WHERE ID='$downl_file->ID';");
    if ( $downl_file->filesize === $endCursor ) {
        $db->query("UPDATE myfiles SET downloaded=true WHERE ID='$downl_file->ID';");
    }
} else {
    echo "nessun file da scaricare";
    $now = time();
}

//$step = ($downl_file) ? ($downl_file->ID . " : " . $downl_file->filename) : ("&#10005");


//$files = $db->selectAll('myfiles');

//$name = htmlspecialchars($_GET['name']);
//require "index.view.php";