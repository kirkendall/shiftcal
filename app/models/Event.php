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
            'time' => strval($this->getEventtime()),
            'length' => NULL
        );
    }

    public function getTimes() {
        $events = $this->buildEventTimes('id');
        $eventTimes = [];
        foreach ($events as $event) {
            $eventTimes []= $event->getEventdate()->format('Y-m-d'); // + $this->getEventtime();
        }
        return $eventTimes;
    }

    public function toDetailArray() {
        // first get the data into an array
        $detailArray = $this->toArray();
        // add all times that exist, maybe none.
        $detailArray["times"] = $this->getTimes();
        // return potentially augmented array
        $this->toArray();
        return $detailArray;
    }
}
fORM::mapClassToTable('Event', 'calevent');
