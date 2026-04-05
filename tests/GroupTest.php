<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\{
    Group,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox\Proof,
    Set,
};

class GroupTest extends TestCase
{
    public function testThrowContainsAWhitespaceOrIsEmpty(): Proof
    {
        return $this
            ->forAll(Set::of('', ' ', 'f o'))
            ->prove(function($invalid) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($invalid);

                Group::of($invalid);
            });
    }

    public function testThrowWhenContainsAColon()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('f:o');

        Group::of('f:o');
    }

    public function testAcceptsAnyStringWithoutAWhitespace(): Proof
    {
        return $this
            ->forAll($this->group())
            ->prove(function($string) {
                $this->assertSame($string, Group::of($string)->toString());
            });
    }

    public function testEquals(): Proof
    {
        return $this
            ->forAll(
                $this->group(),
                $this->group(),
            )
            ->filter(static function($string, $other): bool {
                return $string !== $other;
            })
            ->prove(function($string, $other) {
                $this->assertTrue(Group::of($string)->equals(Group::of($string)));
                $this->assertFalse(Group::of($string)->equals(Group::of($other)));
            });
    }
}
