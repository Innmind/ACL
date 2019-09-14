<?php
declare(strict_types = 1);

namespace Innmind\ACL;

final class Mode
{
    private static $read;
    private static $write;
    private static $execute;

    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
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

    public function __toString(): string
    {
        return $this->value;
    }
}
