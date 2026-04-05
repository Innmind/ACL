<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\{
    User,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox\Proof,
    Set,
};

class UserTest extends TestCase
{
    public function testThrowContainsAWhitespaceOrIsEmpty(): Proof
    {
        return $this
            ->forAll(Set::of('', ' ', 'f o'))
            ->prove(function($invalid) {
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

    public function testAcceptsAnyStringWithoutAWhitespace(): Proof
    {
        return $this
            ->forAll($this->user())
            ->prove(function($string) {
                $this->assertSame($string, User::of($string)->toString());
            });
    }

    public function testEquals(): Proof
    {
        return $this
            ->forAll(
                $this->user(),
                $this->user(),
            )
            ->filter(static function($string, $other): bool {
                return $string !== $other;
            })
            ->prove(function($string, $other) {
                $this->assertTrue(User::of($string)->equals(User::of($string)));
                $this->assertFalse(User::of($string)->equals(User::of($other)));
            });
    }
}
