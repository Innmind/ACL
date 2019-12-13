<?php
declare(strict_types = 1);

namespace Innmind\ACL;

use Innmind\Immutable\{
    Set,
    Sequence,
    Str,
};
use function Innmind\Immutable\unwrap;

final class Entries
{
    private $entries;

    public function __construct(Mode ...$modes)
    {
        $this->entries = Set::of(Mode::class, ...$modes);
    }

    public static function of(string $modes): self
    {
        $modes = Str::of($modes)
            ->split()
            ->reduce(
                Sequence::of('?'.Mode::class),
                static function(Sequence $modes, Str $mode): Sequence {
                    return $modes->add(Mode::of($mode->toString()));
                }
            )
            ->filter(static function(?Mode $mode): bool {
                return $mode instanceof Mode;
            });

        return new self(...unwrap($modes));
    }

    public function add(Mode ...$modes): self
    {
        return new self(...unwrap($this->entries), ...$modes);
    }

    public function remove(Mode ...$modes): self
    {
        $toRemove = Set::of(Mode::class, ...$modes);
        $entries = $this->entries->filter(static function(Mode $entry) use ($toRemove): bool {
            return !$toRemove->contains($entry);
        });

        return new self(...unwrap($entries));
    }

    public function allows(Mode $mode, Mode ...$modes): bool
    {
        return Set::of(Mode::class, $mode, ...$modes)->reduce(
            true,
            function(bool $allows, Mode $mode): bool {
                return $allows && $this->entries->contains($mode);
            }
        );
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
