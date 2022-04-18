<?php
declare(strict_types = 1);

namespace Innmind\ACL;

use Innmind\Immutable\Sequence;

/**
 * @psalm-immutable
 */
enum Mode
{
    case read;
    case write;
    case execute;

    public static function of(string $mode): ?self
    {
        return match ($mode) {
            'r' => self::read,
            'w' => self::write,
            'x' => self::execute,
            '-' => null,
        };
    }

    /**
     * @return Sequence<self>
     */
    public static function all(): Sequence
    {
        return Sequence::of(
            self::read,
            self::write,
            self::execute,
        );
    }

    public function toString(): string
    {
        return match ($this) {
            self::read => 'r',
            self::write => 'w',
            self::execute => 'x',
        };
    }
}
