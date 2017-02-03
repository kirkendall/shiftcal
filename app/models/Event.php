<?php

class Event extends fActiveRecord {
    public function toArray($include_hidden=false) {
        global $IMAGEPATH;
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
            'hideemail' => $this->getHideemail(),
            'length' => NULL,
            //'length' => $this->getLength(),
            'timedetails' => $this->getTimedetails(),
            'locdetails' => $this->getLocdetails(),
            'eventduration' => $this->getEventduration() != null && $this->getEventduration() > 0 ? $this->getEventduration() : null,
            'weburl' => $this->getWeburl(),
            'shareable' => $this->getShareable(),
            'webname' => $this->getWebname(),
            'image' => $this->getImage() != null ? $IMAGEPATH . '/' . $this->getImage() : null,
            'audience' => $this->getAudience(),
            //'printevent' => $this->getPrintevent(),
            'tinytitle' => $this->getTinytitle(),
            'printdescr' => $this->getPrintdescr(),
            'datestype' => $this->getDatestype(),
            'area' => $this->getArea(),
            //'printcontact' => $this->getPrintcontact()
        );

        $details['email']   = $this->getHideemail() == 0   || $include_hidden ? $this->getEmail() : null;
        $details['phone']   = $this->getHidephone() == 0   || $include_hidden ? $this->getPhone() : null;
        $details['contact'] = $this->getHidecontact() == 0 || $include_hidden ? $this->getContact() : null;

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
            $event->setHidden(1);
        }

        // These are marked as required
        $event->setTitle(get($input['title'], 'Title missing'));
        $event->setLocname(get($input['venue'], 'Venue missing'));
        $event->setAddress(get($input['address'], 'Address missing'));
        $event->setName(get($input['organizer'], 'Organizer missing'));
        $event->setEmail(get($input['email'], 'Email missing'));

        // These are optional
        $event->setHideemail(get($input['hideemail'], 0));
        $event->setPhone(get($input['phone'], ''));
        $event->setHidephone(get($input['hidephone'], 0));
        $event->setContact(get($input['contact'], ''));
        $event->setHidecontact(get($input['hidecontact'], 0));
        $event->setDescr(get($input['details'], ''));
        $event->setEventtime(get($input['time'], ''));
        $event->setHighlight(0);
        $event->setTimedetails(get($input['timedetails'], ''));
        $event->setLocdetails(get($input['locdetails'], ''));
        $event->setEventduration(get($input['eventduration'], 0));
        $event->setWeburl(get($input['weburl'], ''));
        $event->setWebname(get($input['webname'], ''));
        $event->setAudience(get($input['audience'], ''));
        $event->setTinytitle(get($input['tinytitle'], ''));
        $event->setPrintdescr(get($input['printdescr'], ''));
        $event->setDates(get($input['datestring'], '')); // string field 'dates' needed for legacy admin calendar
        $event->setDatestype(get($input['datestype'], 'O'));
        $event->setArea(get($input['area'], 'P')); // default to 'P'ortland
        //$event->setPrintcontact(get($input['printcontact'], ''));
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

    public function toDetailArray($include_hidden=false) {
        // first get the data into an array
        $detailArray = $this->toArray($include_hidden);
        // add all times that exist, maybe none.
        $detailArray["dates"] = $this->getDates(); // Return the actual dates, not the hacky string
        // return potentially augmented array
        return $detailArray;
    }

    public function secretValid($secret) {
        return $this->getPassword() == $secret;
    }

    private function generateSecret() {
        $this->setPassword(md5(drupal_random_bytes(32)));
    }

    public function emailSecret() {
        global $PROTOCOL, $HOST, $PATH;
        $base = $PROTOCOL . $HOST . $PATH;
	    $headers = 'From: bikefun@shift2bikes.org' . "\r\n" .  'Reply-To: bikefun@shift2bikes.org' . "\r\n";
	    $message = "Dear " . $this->getName() . ", \r\n\r\nThank you for adding your event, " . $this->getTitle() . ", to the Shift Calendar. To activate and manage it, you must visit $base#editEvent/" . $this->getId() . "/" .$this->getPassword() . "\r\n\r\nBike on!\r\n\r\n-Shift";
        mail($this->getEmail(), "Shift2Bike Event Secret URL: " . $this->getTitle(), $message, $headers);
    }

    private function getShareable() {
    	$caldaily_id = $this->getId();
    	return "#event-" . $caldaily_id;
    }
    
    public function unhide() {
        if ($this->getHidden() != 0) {
            $this->setHidden(0);
            $this->store();
        }
    }
}

fORM::mapClassToTable('Event', 'calevent');
