<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\{
    User,
    Exception\DomainException,
};
use Innmind\BlackBox\Set;

class UserTest extends TestCase
{
    public function testThrowContainsAWhitespaceOrIsEmpty()
    {
        $this
            ->forAll(Set\Elements::of('', ' ', 'f o'))
            ->then(function($invalid) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($invalid);

                User::of($invalid);
            });
    }

    public function testThrowWhenContainsAColon()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('f:o');

        User::of('f:o');
    }

    public function testAcceptsAnyStringWithoutAWhitespace()
    {
        $this
            ->forAll($this->user())
            ->then(function($string) {
                $this->assertSame($string, User::of($string)->toString());
            });
    }

    public function testEquals()
    {
        $this
            ->forAll(
                $this->user(),
                $this->user(),
            )
            ->filter(static function($string, $other): bool {
                return $string !== $other;
            })
            ->then(function($string, $other) {
                $this->assertTrue(User::of($string)->equals(User::of($string)));
                $this->assertFalse(User::of($string)->equals(User::of($other)));
            });
    }
}
