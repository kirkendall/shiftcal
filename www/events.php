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
foreach (EventTime::getRange($startdate,$enddate) as $eventTime) {
    $json['events'] []= $eventTime->toEventSummaryArray();
}
fJSON::output($json);
