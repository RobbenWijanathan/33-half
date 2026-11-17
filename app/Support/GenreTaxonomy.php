<?php

namespace App\Support;

use Illuminate\Support\Str;

class GenreTaxonomy
{
    private const GROUPS = [
        'Rock' => ['Alternative', 'Alternative Rock', 'Art Rock', 'Experimental Rock', 'Folk Rock', 'Funk Rock', 'Glam Rock', 'Gothic Rock', 'Grunge', 'Hard Rock', 'Indie', 'Indie Rock', 'Jazz Rock', 'Math Rock', 'Neo-Psychedelia', 'Post-Punk', 'Post-Rock', 'Progressive Rock', 'Proto-Punk', 'Psychedelic Rock', 'Symphonic Prog', 'Shoegaze'],
        'Pop' => ['Art Pop', 'Baroque Pop', 'Dream Pop', 'Ethereal Wave', 'Glitch Pop', 'Jangle Pop', 'New Wave', 'Noise Pop', 'Pop Rock', 'Progressive Pop', 'Psychedelic Pop'],
        'Electronic' => ['Ambient Techno', 'Downtempo', 'Experimental Electronic', 'French House', 'IDM', 'Industrial', 'Trip-Hop'],
        'Hip-Hop' => ['Abstract Hip-Hop', 'Boom Bap', 'Chipmunk Soul', 'Conscious Hip-Hop', 'East Coast Hip-Hop', 'Experimental Hip-Hop', 'Hardcore Hip-Hop', 'Industrial Hip-Hop', 'Jazz Rap', 'Pop Rap', 'Progressive Rap', 'West Coast Hip-Hop'],
        'Jazz' => ['Avant-Garde Jazz', 'Cool Jazz', 'Jazz Fusion', 'Modal Jazz', 'Spiritual Jazz', 'Third Stream'],
        'Soul' => ['Alternative R&B', 'Neo-Soul', 'Pop Soul', 'Progressive Soul'],
        'Funk' => ['Disco'],
        'Folk' => ['Contemporary Folk', 'Indie Folk', 'Psychedelic Folk', 'Singer-Songwriter'],
        'Metal' => ['Heavy Metal'],
        'Ambient' => ['Drone'],
        'Reggae' => ['Dub'],
        'Experimental' => ['Lo-Fi'],
    ];

    public static function parent(string $genre): string
    {
        $slug = Str::slug($genre);

        foreach (self::GROUPS as $parent => $aliases) {
            if ($slug === Str::slug($parent)) {
                return $parent;
            }

            foreach ($aliases as $alias) {
                if ($slug === Str::slug($alias)) {
                    return $parent;
                }
            }
        }

        return $genre;
    }

    public static function normalize(iterable $genres): array
    {
        return collect($genres)->map(self::parent(...))->unique(fn ($genre) => Str::slug($genre))
            ->take(3)->values()->all();
    }
}
