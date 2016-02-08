<?php

class Event extends fActiveRecord {
    public function toArray() {
        /*
        id:
        title:
        (different table!) date:
        venue:
        address:
        organizer:
        email:
        details:
        length:
        */
        return array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'venue' => $this->getLocname(),
            'address' => $this->getAddress(),
            'organizer' => $this->getName(),
            'email' => $this->getEmail(),
            'details' => $this->getDescr(),
            'length' => NULL
        );
    }

    public function getTimes() {
        $events = $this->buildEventTimes();
    }

    public function toDetailArray() {
        // first get the data into an array
        $detailArray = $this->toArray();
        // add all times that exist, maybe none.
        $eventTimes = $this->getTimes();
        $timesJson = array('timestamps' => array($eventTimes));
        $detailArray["times"] = $timesJson;
        // return potentially augmented array
        $this->toArray();
    }
}
fORM::mapClassToTable('Event', 'calevent');
