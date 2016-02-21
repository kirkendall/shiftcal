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

    private function getEvent() {
        if ($this->getEventstatus() === 'E') {
            return $this->createEvent('exceptionid');
        }
        return $this->createEvent('id');
    }

    public function getFormattedDate() {
        return $this->getEventdate()->format('Y-m-d');
    }

    public function toEventSummaryArray() {
        $eventArray = $this->getEvent()->toArray();
        $eventArray['date'] = $this->getFormattedDate();
        return $eventArray;
    }
}

fORM::mapClassToTable('EventTime', 'caldaily');
