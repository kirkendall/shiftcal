<?php
include(getcwd() . '/../app/init.php');

$json = array('events' => array());
foreach (EventTime::get2Weeks() as $event) {
    $json['events'] []= $event->toArray();
}

fJSON::output($json);
