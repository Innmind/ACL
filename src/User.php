<?php
declare(strict_types = 1);

namespace Innmind\ACL;

use Innmind\ACL\Exception\DomainException;
use Innmind\Immutable\Str;

final class User
{
    private $value;

    public function __construct(string $value)
    {
        $value = Str::of($value);

        if (!$value->matches('~^\S+$~') || $value->contains(':')) {
            throw new DomainException((string) $value);
        }

        $this->value = (string) $value;
    }

    public function equals(self $user): bool
    {
        return $this->value === $user->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
