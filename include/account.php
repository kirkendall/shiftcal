<?php
	# Database access constants
	define("DBHOST", "localhost");
	define("DBUSER", "root");
	define("DBPASSWORD", "");
	define("DBDATABASE", "pp");

	# Directory where SHIFT's standard header and footer are stored
	define("INCLUDES", "../includes");

	# URL of the Directory where the calendar resides
	define("CALURL", "http://localhost/shiftcal/");

	# The page to show when the user loads CALURL
	define("MAINPAGE", "view3week.php");

	# Timezone difference between the web host and Portland.
	define ("TZTWEAK", 0);

	# Email address for PP calendar crew.  This is used as the "From:"
	# address of confirmation messages.
	define("SHIFTEMAIL", "skirkendall@dsl-only.net");
?>
