<?php

namespace App\Support;

class RecordGrades
{
    private const GRADES = [
        'M' => ['Mint', 'No visible or audible flaws.', 'No creases, splits, or visible wear.'],
        'NM' => ['Near Mint', 'Looks almost new and should play without noticeable noise.', 'Almost new, with little or no visible wear.'],
        'VG+' => ['Very Good Plus', 'Light marks or signs of play that have little effect on listening.', 'Light edge, corner, or ring wear.'],
        'VG' => ['Very Good', 'Noticeable marks and surface noise, especially in quieter passages.', 'Visible shelf wear, creases, or small seam splits are possible.'],
        'G' => ['Good', 'Plays through, with pronounced noise and wear.', 'Heavy wear, larger splits, or writing are possible.'],
        'F' => ['Fair', 'Heavy damage. Playback may skip or repeat.', 'Badly worn, torn, or split.'],
        'P' => ['Poor', 'Severely damaged and may be unplayable.', 'Severely damaged and may no longer protect the record.'],
    ];

    public static function labels(): array
    {
        $labels = [];

        foreach (self::GRADES as $code => [$name]) {
            $labels[$code] = "$name ($code)";
        }

        return $labels;
    }

    public static function description(?string $code, string $condition, string $part): array
    {
        if (isset(self::GRADES[$code])) {
            [$name, $record, $sleeve] = self::GRADES[$code];

            return ["$name ($code)", $part === 'record' ? $record : $sleeve];
        }

        if ($code) {
            return [$code, 'Ask the store about this condition grade.'];
        }

        return $condition === 'new'
            ? ['New', 'Listed as new without a secondhand grade.']
            : ['Not graded', 'A condition grade has yet to be assigned.'];
    }
}
