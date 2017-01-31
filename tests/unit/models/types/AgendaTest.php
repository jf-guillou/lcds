<?php

namespace tests\models\types;

use app\models\types\Agenda;

class AgendaTest extends \Codeception\Test\Unit
{
    public $emptyIcal = <<<'EO1'
BEGIN:VCALENDAR
METHOD:REQUEST
PRODID:-//ADE/version 6.0
VERSION:2.0
CALSCALE:GREGORIAN
END:VCALENDAR
EO1;

    public $oldIcal = <<<'EO2'
BEGIN:VCALENDAR
METHOD:REQUEST
PRODID:-//ADE/version 6.0
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTAMP:20000101T100000Z
DTSTART:20000101T100000Z
DTEND:20000101T120000Z
SUMMARY:OLD EVENT
LOCATION:OLD LOCATION
DESCRIPTION:\nOLD DESC
UID:ADE60456d706c6f6973647574656d7073323031362d323031372d33303430362d302d
 30
CREATED:19700101T000000Z
LAST-MODIFIED:20000101T100000Z
SEQUENCE:1703151061
END:VEVENT
END:VCALENDAR
EO2;

    public $validIcal = <<<'EO3'
BEGIN:VCALENDAR
METHOD:REQUEST
PRODID:-//ADE/version 6.0
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTAMP:20000101T100000Z
DTSTART:%TODAY%T100000Z
DTEND:%TODAY%T120000Z
SUMMARY:VALID EVENT
LOCATION:VALID LOCATION
DESCRIPTION:\nVALID DESC
UID:ADE60456d706c6f6973647574656d7073323031362d323031372d33303430362d302d
 30
CREATED:19700101T000000Z
LAST-MODIFIED:20000101T100000Z
SEQUENCE:1703151061
END:VEVENT
END:VCALENDAR
EO3;

    public $nonOverlapIcal = <<<'EO3'
BEGIN:VCALENDAR
METHOD:REQUEST
PRODID:-//ADE/version 6.0
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTAMP:20000101T100000Z
DTSTART:%TODAY%T100000Z
DTEND:%TODAY%T120000Z
SUMMARY:NONOVERLAP1 EVENT
LOCATION:NONOVERLAP1 LOCATION
DESCRIPTION:\nNONOVERLAP1 DESC
UID:ADE60456d706c6f6973647574656d7073323031362d323031372d33303430362d302d
 30
CREATED:19700101T000000Z
LAST-MODIFIED:20000101T100000Z
SEQUENCE:1703151061
END:VEVENT
BEGIN:VEVENT
DTSTAMP:20000101T100000Z
DTSTART:%TODAY%T120000Z
DTEND:%TODAY%T140000Z
SUMMARY:NONOVERLAP2 EVENT
LOCATION:NONOVERLAP2 LOCATION
DESCRIPTION:\NONOVERLAP2 DESC
UID:ADE60456d706c6f6973647574656d7073323031362d323031372d33303430362d303d
 30
CREATED:19700101T000000Z
LAST-MODIFIED:20000101T100000Z
SEQUENCE:1703151062
END:VEVENT
END:VCALENDAR
EO3;

    public $overlapIcal = <<<'EO3'
BEGIN:VCALENDAR
METHOD:REQUEST
PRODID:-//ADE/version 6.0
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTAMP:20000101T100000Z
DTSTART:%TODAY%T100000Z
DTEND:%TODAY%T120000Z
SUMMARY:OVERLAP1 EVENT
LOCATION:OVERLAP1 LOCATION
DESCRIPTION:\nOVERLAP1 DESC
UID:ADE60456d706c6f6973647574656d7073323031362d323031372d33303430362d302d
 30
CREATED:19700101T000000Z
LAST-MODIFIED:20000101T100000Z
SEQUENCE:1703151061
END:VEVENT
BEGIN:VEVENT
DTSTAMP:20000101T100000Z
DTSTART:%TODAY%T110000Z
DTEND:%TODAY%T130000Z
SUMMARY:OVERLAP2 EVENT
LOCATION:OVERLAP2 LOCATION
DESCRIPTION:\nOVERLAP2 DESC
UID:ADE60456d706c6f6973647574656d7073323031362d323031372d33303430362d303d
 30
CREATED:19700101T000000Z
LAST-MODIFIED:20000101T100000Z
SEQUENCE:1703151062
END:VEVENT
END:VCALENDAR
EO3;

    public function testGenAgenda()
    {
        $a = new Agenda();

        expect_not($a->genAgenda(''));
    }

    public function testParseIcal()
    {
        $a = new Agenda();

        expect_not($a->parseIcal(''));

        expect_not($a->parseIcal($this->emptyIcal));

        expect_not($a->parseIcal($this->oldIcal));

        $validIcal = str_replace('%TODAY%', (new \DateTime())->format('Ymd'), $this->validIcal);

        expect_that($a->parseIcal($validIcal));
    }

    public function testBlockize()
    {
        $a = new Agenda();

        $nonOverlapIcal = str_replace('%TODAY%', (new \DateTime())->format('Ymd'), $this->nonOverlapIcal);
        $agenda = $a->parseIcal($nonOverlapIcal);
        expect_that($agenda);

        $events = $a->blockize($agenda);
        expect_that($events);
        foreach ($events as $blocks) {
            foreach ($blocks as $e) {
                expect($e['overlaps'])->lessThan(2);
            }
        }

        $overlapIcal = str_replace('%TODAY%', (new \DateTime())->format('Ymd'), $this->overlapIcal);
        $agenda = $a->parseIcal($overlapIcal);
        expect_that($agenda);

        $events = $a->blockize($agenda);
        expect_that($events);
        foreach ($events as $blocks) {
            foreach ($blocks as $e) {
                expect($e['overlaps'])->equals(2);
            }
        }
    }

    public function testRender()
    {
        $a = new Agenda();

        $validIcal = str_replace('%TODAY%', (new \DateTime())->format('Ymd'), $this->validIcal);

        $agenda = $a->parseIcal($validIcal);
        expect_that($agenda);

        $events = $a->blockize($agenda);
        expect_that($events);

        $agenda['events'] = $events;

        $html = $a->render($agenda);
        expect($html)->contains((new \DateTime())->format('d/m'));
    }
}
