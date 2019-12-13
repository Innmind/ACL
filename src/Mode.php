<?php
declare(strict_types = 1);

namespace Innmind\ACL;

use Innmind\ACL\Exception\DomainException;
use Innmind\Immutable\Sequence;

final class Mode
{
    private static $read;
    private static $write;
    private static $execute;
    private static $all;

    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function of(string $mode): ?self
    {
        switch ($mode) {
            case 'r':
                return self::read();

            case 'w':
                return self::write();

            case 'x':
                return self::execute();

            case '-':
                return null;
        }

        throw new DomainException($mode);
    }

    public static function read(): self
    {
        return self::$read ?? self::$read = new self('r');
    }

    public static function write(): self
    {
        return self::$write ?? self::$write = new self('w');
    }

    public static function execute(): self
    {
        return self::$execute ?? self::$execute = new self('x');
    }

    public static function all(): Sequence
    {
        return self::$all ?? self::$all = Sequence::of(
            self::class,
            self::read(),
            self::write(),
            self::execute()
        );
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
