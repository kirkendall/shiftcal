<?php
	# This view generates an iCalendar formatted ouput for a
  # specific event. (https://en.wikipedia.org/wiki/ICalendar)
  # Most browsers and smartphones should recognize this format
  # and prompt the user to automatically add the event to
  # his or her calendar. 

	include("include/common.php");
	header("Content-type: text/calendar");
	header("Cache-control: private");
  
  # get id for event we want to display
  $event_id = $_REQUEST['eventID'];
  if (!$event_id) die();
  
  # connect to the database
	$conn = mysql_connect(DBHOST, DBUSER, DBPASSWORD) or die(mysql_error());
	mysql_select_db(DBDATABASE, $conn);

	$result = mysql_query("SELECT calevent.*, caldaily.* FROM calevent, caldaily WHERE caldaily.id = calevent.id AND calevent.id = \"${event_id}\"", $conn) or die(mysql_error());

  $event = mysql_fetch_object($result);
  
	# Generate the iCal formatted output for  this specific entry.
  # Using DURATION field instead of DTEND. Not always specified
  # and open-ended events with an evenduration of 0 may span
  # the entire day when added to a calendar.

  
  ?>BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTART:<?php echo date('Ymd', strtotime($event->eventdate)) . 'T' . date('His', strtotime($event->eventtime)) . 'Z'; ?>
DURATION:<?php echo $event->duration ?>
DESCRIPTION:<?php echo $event->descr; ?>
URL:http://shift2bikes.org
LOCATION:<?php echo $event->address; ?>
UID:event<?php echo $event->id; ?>@shift2bikes.org
STATUS:CONFIRMED
SUMMARY:<?php echo $event->title; ?>
END:VEVENT
END:VCALENDAR

