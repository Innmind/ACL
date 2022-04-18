<?php
declare(strict_types = 1);

namespace Innmind\ACL;

use Innmind\ACL\Exception\DomainException;
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class User
{
    private string $value;

    private function __construct(string $value)
    {
        $value = Str::of($value);

        if (!$value->matches('~^\S+$~') || $value->contains(':')) {
            throw new DomainException($value->toString());
        }

        $this->value = $value->toString();
    }

    /**
     * @psalm-pure
     */
    public static function of(string $value): self
    {
        return new self($value);
    }

    public function equals(self $user): bool
    {
        return $this->value === $user->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
