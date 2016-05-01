<?php
/**
 * Imports some events from the real calendar for testing
 */
include('init.php');
const URL = 'http://shift2bikes.org/betacal/www/events.php';
$endDate = new DateTime('+3 days');
$ch = curl_init(URL . "?enddate=" . $endDate->format('Y-m-d'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$eventJson = curl_exec($ch);
curl_close($ch);
$decoded = json_decode($eventJson, true);
$eventArrays = $decoded['events'];
foreach ($eventArrays as $eventArray) {
    $event = Event::fromArray($eventArray);
    $event->store();
    $dates = array(
        DateTime::createFromFormat('Y-m-d', $eventArray['date'])
    );
    EventTime::matchEventTimesToDates($event, $dates);
}
$num = count($eventArrays);
print("Imported $num events from shift2bikes.org\n");