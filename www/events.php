<?php
include(getcwd() . '/../app/init.php');

if (isset($_GET['startdate']) && ($parseddate = strtotime($_GET['startdate']))) {
    $startdate = $parseddate;
} else {
    $startdate = time();
}

if (isset($_GET['enddate']) && ($parseddate = strtotime($_GET['enddate']))) {
    $enddate = $parseddate;
} else {
    $enddate = time();
}

$json = array('events' => array());
foreach (EventTime::getRange($startdate,$enddate) as $event) {
    $json['events'] []= $event->toArray();
}
fJSON::output($json);
