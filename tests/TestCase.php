<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\Mode;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class TestCase extends BaseTestCase
{
    use BlackBox;

    protected function user(): Set
    {
        return Set\Strings::any()->filter(static function($user) {
            return (bool) \preg_match('~^\S+$~', $user) &&
                \strpos($user, ':') === false;
        });
    }

    protected function group(): Set
    {
        return Set\Strings::any()->filter(static function($group) {
            return (bool) \preg_match('~^\S+$~', $group) &&
                \strpos($group, ':') === false;
        });
    }

    protected function mode(): Set
    {
        return Set\Elements::of(Mode::read, Mode::write, Mode::execute);
    }

    protected function modes(): Set
    {
        return Set\Sequence::of(
            $this->mode(),
            Set\Integers::between(0, 10), // adds no value to generate higher than 10
        );
    }
}
