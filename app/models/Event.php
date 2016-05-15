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
        $details = array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'venue' => $this->getLocname(),
            'address' => $this->getAddress(),
            'organizer' => $this->getName(),
            'details' => $this->getDescr(),
            'time' => strval($this->getEventtime()),
            'length' => NULL
        );

        if ($this->getHideemail() == 0) {
            $details['email'] = $this->getEmail();
        }
        else {
            $details['email'] = null;
        }

        return $details;
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
            $event->generateSecret();
        }

        // These are marked as required
        $event->setTitle(get($input['title'], 'Title missing'));
        $event->setLocname(get($input['venue'], 'Venue missing'));
        $event->setAddress(get($input['address'], 'Address missing'));
        $event->setName(get($input['organizer'], 'Organizer missing'));
        $event->setEmail(get($input['email'], 'Email missing'));

        // These are optional
        $event->setHideemail(get($input['hideemail'], 0));
        $event->setDescr(get($input['details'], ''));
        $event->setEventtime(get($input['time'], ''));
        $event->setHighlight(0);
        // Length
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

    public function secretValid($secret) {
        return $this->getSecret() == $secret;
    }

    private function generateSecret() {
        $this->setSecret(md5(drupal_random_bytes(32)));
    }

    public function emailSecret() {
        global $PROTOCOL, $HOST, $PATH;
        $base = $PROTOCOL . $HOST . $PATH;
        mail($this->getEmail(), "Edit event", "$base#editEvent/" . $this->getId() . "/" .$this->getSecret());
    }
}
fORM::mapClassToTable('Event', 'calevent');
