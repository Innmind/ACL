<?php
declare(strict_types = 1);

namespace Innmind\ACL;

use Innmind\Immutable\{
    Set,
    Sequence,
    Str,
};

final class Entries
{
    /** @var Set<Mode> */
    private Set $entries;

    /**
     * @no-named-arguments
     */
    public function __construct(Mode ...$modes)
    {
        $this->entries = Set::of(...$modes);
    }

    public static function of(string $modes): self
    {
        /** @var list<Mode> */
        $modes = Str::of($modes)
            ->split()
            ->map(static fn($mode) => Mode::of($mode->toString()))
            ->filter(static fn(?Mode $mode) => $mode instanceof Mode)
            ->toList();

        return new self(...$modes);
    }

    public function add(Mode ...$modes): self
    {
        return new self(...$this->entries->toList(), ...$modes);
    }

    /**
     * @no-named-arguments
     */
    public function remove(Mode ...$modes): self
    {
        $toRemove = Set::of(...$modes);
        $entries = $this->entries->diff($toRemove);

        return new self(...$entries->toList());
    }

    public function allows(Mode $mode, Mode ...$modes): bool
    {
        return Set::of($mode, ...$modes)->reduce(
            true,
            function(bool $allows, Mode $mode): bool {
                return $allows && $this->entries->contains($mode);
            },
        );
    }

    public function toString(): string
    {
        return Mode::all()->reduce(
            '',
            function(string $entries, Mode $mode): string {
                return $entries.($this->entries->contains($mode) ? $mode->toString() : '-');
            },
        );
    }
}
