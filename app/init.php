<?php

include_once('config.php');
include('event.php');

fORMDatabase::attach(new fDatabase('mysql', $DBDB, $DBUSER, $DBPASS, $DBHOST));