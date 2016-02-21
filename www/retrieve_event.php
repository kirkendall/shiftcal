<?php
include(getcwd() . '/../app/init.php');

if (isset($_GET['id'])) {
    $event_id=$_GET['id'];
    try {
        // get event by id
        $event = new Event($event_id); 
    } catch (fExpectedException $e) { 
        echo $e->printMessage();
    }
    $json['events'] []= $event->toDetailArray();
    fJSON::output($json);
}
