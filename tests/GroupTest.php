<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\{
    Group,
    Exception\DomainException,
};
use Innmind\BlackBox\Set;

class GroupTest extends TestCase
{
    public function testThrowContainsAWhitespaceOrIsEmpty()
    {
        $this
            ->forAll(Set\Elements::of('', ' ', 'f o'))
            ->then(function($invalid) {
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

    public function testAcceptsAnyStringWithoutAWhitespace()
    {
        $this
            ->forAll($this->group())
            ->then(function($string) {
                $this->assertSame($string, Group::of($string)->toString());
            });
    }

    public function testEquals()
    {
        $this
            ->forAll(
                $this->group(),
                $this->group(),
            )
            ->filter(static function($string, $other): bool {
                return $string !== $other;
            })
            ->then(function($string, $other) {
                $this->assertTrue(Group::of($string)->equals(Group::of($string)));
                $this->assertFalse(Group::of($string)->equals(Group::of($other)));
            });
    }
}
