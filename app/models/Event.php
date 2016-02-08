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
}

fORM::mapClassToTable('Event', 'calevent');
