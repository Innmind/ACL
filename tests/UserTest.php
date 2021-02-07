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

                new User($invalid);
            });
    }

    public function testThrowWhenContainsAColon()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('f:o');

        new User('f:o');
    }

    public function testAcceptsAnyStringWithoutAWhitespace()
    {
        $this
            ->forAll($this->user())
            ->then(function($string) {
                $this->assertSame($string, (new User($string))->toString());
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
                $this->assertTrue((new User($string))->equals(new User($string)));
                $this->assertFalse((new User($string))->equals(new User($other)));
            });
    }
}
