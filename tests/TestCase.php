<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\Mode;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase as BaseTestCase,
    Set,
};

class TestCase extends BaseTestCase
{
    use BlackBox;

    protected function user(): Set
    {
        return Set::strings()
            ->madeOf(Set::strings()->chars()->alphanumerical())
            ->filter(
                static fn($user) => (bool) \preg_match('~^\S+$~', $user) &&
                    \strpos($user, ':') === false,
            );
    }

    protected function group(): Set
    {
        return Set::strings()
            ->madeOf(Set::strings()->chars()->alphanumerical())
            ->filter(
                static fn($group) => (bool) \preg_match('~^\S+$~', $group) &&
                    \strpos($group, ':') === false,
            );
    }

    protected function mode(): Set
    {
        return Set::of(Mode::read, Mode::write, Mode::execute);
    }

    protected function modes(): Set
    {
        return Set::sequence(
            $this->mode(),
        )
            ->between(0, 10) // adds no value to generate higher than 10
            ->toSet();
    }
}
