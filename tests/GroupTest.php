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

                new Group($invalid);
            });
    }

    public function testThrowWhenContainsAColon()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('f:o');

        new Group('f:o');
    }

    public function testAcceptsAnyStringWithoutAWhitespace()
    {
        $this
            ->forAll($this->group())
            ->then(function($string) {
                $this->assertSame($string, (new Group($string))->toString());
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
                $this->assertTrue((new Group($string))->equals(new Group($string)));
                $this->assertFalse((new Group($string))->equals(new Group($other)));
            });
    }
}
