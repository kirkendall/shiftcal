<?php

class EventTime extends fActiveRecord {
    public static function getRange($firstDay, $lastDay) {
        return fRecordSet::build(
            'EventTime', // class
            array(
                'eventdate>=' => $firstDay,
                'eventdate<=' => $lastDay
            ), // where
            array('eventdate' => 'asc')  // order by
        );
    }

    public static function get2Weeks() {
        $end = date('Y-n-j');
        // Start date
        $start = date('d-m-Y', strtotime("-2 weeks"));

        $startDate = strtotime($start);
        $endDate = strtotime($end);

        $firstDayOfStartWeek = strtotime('-'.date('w', $startDate).' days', $startDate);
        $lastDayOfEndWeek    = strtotime('+'.(7-date('w', $endDate)).' days', $endDate);

        return EventTime::getRange($firstDayOfStartWeek, $lastDayOfEndWeek);
    }

    public function getEvent() {
        if ($this->getEventstatus() === 'E') {
            return $this->createEvent('exceptionid');
        }
        return $this->createEvent('id');
    }

    public function toArray() {
        $eventArray = $this->getEvent()->toArray();
        $eventArray['date'] = $this->getEventdate()->format('Y-m-d');
        return $eventArray;
    }
}

fORM::mapClassToTable('EventTime', 'caldaily');
