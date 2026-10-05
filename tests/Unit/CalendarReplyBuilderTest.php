<?php

namespace Tests\Unit;

use App\Services\Mail\CalendarReplyBuilder;
use RuntimeException;
use Tests\TestCase;

class CalendarReplyBuilderTest extends TestCase
{
    private const SAMPLE_ICS = <<<'ICS'
    BEGIN:VCALENDAR
    PRODID:-//Google Inc//Google Calendar 70.9054//EN
    VERSION:2.0
    METHOD:REQUEST
    BEGIN:VEVENT
    DTSTART:20260731T083000Z
    DTEND:20260731T090000Z
    DTSTAMP:20260727T105830Z
    ORGANIZER;CN=Leandro Peluso:mailto:leandro@digitalrevgroup.com
    UID:abc123@google.com
    SEQUENCE:0
    SUMMARY:Allineamento DPO
    END:VEVENT
    END:VCALENDAR
    ICS;

    public function test_reply_carries_over_event_identity_and_sets_partstat(): void
    {
        $reply = (new CalendarReplyBuilder)->buildReply(
            originalIcs: self::SAMPLE_ICS,
            attendeeEmail: 'privacy@digitalrevgroup.com',
            attendeeName: 'DIGITAL REV GROUP LTD',
            partstat: 'ACCEPTED',
        );

        $this->assertStringContainsString('METHOD:REPLY', $reply);
        $this->assertStringContainsString('UID:abc123@google.com', $reply);
        $this->assertStringContainsString('SUMMARY:Allineamento DPO', $reply);
        $this->assertStringContainsString('ORGANIZER;CN=Leandro Peluso:mailto:leandro@digitalrevgroup.com', $reply);
        $this->assertStringContainsString('DTSTART:20260731T083000Z', $reply);
        $this->assertStringContainsString(
            'ATTENDEE;CN="DIGITAL REV GROUP LTD";PARTSTAT=ACCEPTED;ROLE=REQ-PARTICIPANT:mailto:privacy@digitalrevgroup.com',
            $reply
        );
    }

    public function test_reply_reflects_declined_partstat(): void
    {
        $reply = (new CalendarReplyBuilder)->buildReply(
            originalIcs: self::SAMPLE_ICS,
            attendeeEmail: 'privacy@digitalrevgroup.com',
            attendeeName: null,
            partstat: 'DECLINED',
        );

        $this->assertStringContainsString('PARTSTAT=DECLINED', $reply);
        $this->assertStringContainsString('ATTENDEE;PARTSTAT=DECLINED', $reply);
    }

    public function test_throws_when_ics_has_no_uid(): void
    {
        $this->expectException(RuntimeException::class);

        (new CalendarReplyBuilder)->buildReply(
            originalIcs: "BEGIN:VCALENDAR\nBEGIN:VEVENT\nSUMMARY:Senza UID\nEND:VEVENT\nEND:VCALENDAR",
            attendeeEmail: 'privacy@digitalrevgroup.com',
            attendeeName: null,
        );
    }
}
