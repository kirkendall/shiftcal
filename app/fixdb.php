<?php

function fixDb() {
    try {
        EventTime::getRange(time(), time());
    }
    catch (fProgrammerException $e) {
        die("PK missing, please run ALTER TABLE caldaily ADD COLUMN pkid INT NOT NULL AUTO_INCREMENT PRIMARY KEY");
    }
}
