<?php

include_once('config.php');
include_once('schema.php');

include('models/Event.php');
include('models/EventTime.php');

$database = new fDatabase('mysql', $DBDB, $DBUSER, $DBPASS, $DBHOST);
fORMDatabase::attach($database);

$schema = updateSchema($database);
fORMSchema::attach($schema);


include('fixdb.php');
fixDb();
