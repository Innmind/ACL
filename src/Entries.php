<?php
declare(strict_types = 1);

namespace Innmind\ACL;

use Innmind\Immutable\Set;

final class Entries
{
    private $entries;

    public function __construct(Mode ...$modes)
    {
        $this->entries = Set::of(Mode::class, ...$modes);
    }

    public function __toString(): string
    {
        return Mode::all()->reduce(
            '',
            function(string $entries, Mode $mode): string {
                return $entries.($this->entries->contains($mode) ? (string) $mode : '-');
            }
        );
    }
}
