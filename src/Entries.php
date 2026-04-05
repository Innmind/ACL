<?php
declare(strict_types = 1);

namespace Innmind\ACL;

use Innmind\Immutable\{
    Set,
    Str,
    Predicate\Instance,
};

/**
 * @psalm-immutable
 */
final class Entries
{
    /**
     * @param Set<Mode> $entries
     */
    private function __construct(
        private Set $entries,
    ) {
    }

    /**
     * @no-named-arguments
     * @psalm-pure
     */
    public static function from(Mode ...$modes): self
    {
        return new self(Set::of(...$modes));
    }

    /**
     * @psalm-pure
     */
    public static function of(string $modes): self
    {
        return new self(
            Str::of($modes)
                ->split()
                ->map(static fn($mode) => Mode::of($mode->toString()))
                ->keep(Instance::of(Mode::class))
                ->toSet(),
        );
    }

    public function add(Mode ...$modes): self
    {
        return self::from(...$this->entries->toList(), ...$modes);
    }

    /**
     * @no-named-arguments
     */
    public function remove(Mode ...$modes): self
    {
        $toRemove = Set::of(...$modes);

        return new self($this->entries->diff($toRemove));
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
