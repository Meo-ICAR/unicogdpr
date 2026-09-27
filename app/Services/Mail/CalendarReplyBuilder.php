<?php

namespace App\Services\Mail;

use RuntimeException;

/**
 * Costruisce una risposta iCalendar (METHOD:REPLY) a partire dall'invito
 * originale (METHOD:REQUEST), così il client di calendario dell'organizzatore
 * (es. Google Calendar) registra l'accettazione/rifiuto come farebbe con una
 * risposta inviata dal client email.
 */
class CalendarReplyBuilder
{
    /** @var array<int, string> Proprietà del VEVENT originale da riportare identiche nella risposta. */
    private const CARRIED_OVER_PROPERTIES = ['DTSTART', 'DTEND', 'SUMMARY', 'ORGANIZER', 'SEQUENCE'];

    public function buildReply(string $originalIcs, string $attendeeEmail, ?string $attendeeName, string $partstat = 'ACCEPTED'): string
    {
        $properties = $this->extractEventProperties($originalIcs);

        if (! isset($properties['UID'])) {
            throw new RuntimeException('Impossibile generare la risposta: il file .ics non contiene un UID.');
        }

        $attendeeParams = $attendeeName ? sprintf(';CN="%s"', str_replace('"', '', $attendeeName)) : '';

        $lines = [
            'BEGIN:VCALENDAR',
            'PRODID:-//UnicoGDPR//Calendar Reply 1.0//IT',
            'VERSION:2.0',
            'METHOD:REPLY',
            'BEGIN:VEVENT',
            $properties['UID'],
            'DTSTAMP:'.gmdate('Ymd\THis\Z'),
        ];

        foreach (self::CARRIED_OVER_PROPERTIES as $property) {
            if (isset($properties[$property])) {
                $lines[] = $properties[$property];
            }
        }

        $lines[] = "ATTENDEE{$attendeeParams};PARTSTAT={$partstat};ROLE=REQ-PARTICIPANT:mailto:{$attendeeEmail}";
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * @return array<string, string> mappa NOME-PROPRIETA => riga grezza (nome + parametri + valore)
     */
    private function extractEventProperties(string $ics): array
    {
        $unfolded = preg_replace('/\n[ \t]/', '', str_replace(["\r\n", "\r"], "\n", $ics)) ?? $ics;

        $properties = [];

        foreach (explode("\n", $unfolded) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $name = strtoupper(strtok($line, ';:') ?: '');

            if (in_array($name, ['UID', ...self::CARRIED_OVER_PROPERTIES], true)) {
                $properties[$name] = $line;
            }
        }

        return $properties;
    }
}
