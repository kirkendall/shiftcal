<?php


/*
    public function showDefaultCalendar()
    {
        // End date of calendar, today ex: 2015-11-8
        $end = date('Y-n-j');
        // Start date
        $start = date('d-m-Y', strtotime("-2 weeks"));
        return $this->showCalendar($start, $end);
    }
    public function showCalendar($start, $end)
    {
        $startDate = strtotime($start);
        $endDate = strtotime($end);

        $firstDayOfStartWeek = strtotime('-'.date('w', $startDate).' days', $startDate);
        $lastDayOfEndWeek    = strtotime('+'.(7-date('w', $endDate)).' days', $endDate);

        $events = Event::where();

        return view('calendar.all', [
            'start'     => $startDate,
            'end'       => $endDate,
            'events'    => $events,
            'firstDay'  => $firstDayOfStartWeek,
            'lastDay'   => $lastDayOfEndWeek
        ]);
    }
   */

class Event extends fActiveRecord {
	public static function getSome() {
		return fRecordSet::build(
            'Event', // class
            array(), // where
            array(), // order by
            10,      // limit
            0        // page
        );
	}

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
}

fORM::mapClassToTable('Event', 'calevent');
