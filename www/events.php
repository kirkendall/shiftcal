<?php
include(getcwd() . '/../app/init.php');

function load_one($id) {
	$event = Event($_GET['id']);
	if ($event) {
		fJSON::output($event->toArray());
		return;
	}
	http_response_code(404);
}


if (isset($_GET['id'])) {
	load_one($_GET['id']);
}
else {
	$json = array('events' => array());
	foreach (Event::getSome() as $event) {
	    $json['events'] []= $event->toArray();
	}

	fJSON::output($json);
}

