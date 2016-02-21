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

    public static function fromArray($input) {
        $event = null;
        if (array_key_exists('id', $input)) {
            try {
                // get event by id
                $event = new Event($input['id']);
            } catch (fExpectedException $e) {}
        }
        if ($event == null) {
            $event = new Event();
        }

        // Load existing event/create new event
        // Insert data into object
        // Save, get id back
        // Load existing dates
        // Build list of input dates
        // Iterate existing:
        //  If in input remove from list
        //  If not in input delete
        // Iterate remaining
        //  Create eventtime
        return $event;
    }

    private function getDates() {
        $eventTimes = $this->buildEventTimes('id');
        $eventDates = [];
        foreach ($eventTimes as $eventTime) {
            $eventDates []= $eventTime->getFormattedDate();
        }
        return $eventDates;
    }

    public function toDetailArray() {
        // first get the data into an array
        $detailArray = $this->toArray();
        // add all times that exist, maybe none.
        $detailArray["dates"] = $this->getDates();
        // return potentially augmented array
        return $detailArray;
    }
}
fORM::mapClassToTable('Event', 'calevent');
