<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\{
    Group,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class GroupTest extends TestCase
{
    use TestTrait;

    public function testThrowContainsAWhitespaceOrIsEmpty()
    {
        $this
            ->forAll(Generator\elements('', ' ', 'f o'))
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
            ->forAll(Generator\string())
            ->when(static function($string): bool {
                return (bool) preg_match('~^\S+$~', $string) && strpos($string, ':') === false;
            })
            ->then(function($string) {
                $this->assertSame($string, (string) new Group($string));
            });
    }
}
