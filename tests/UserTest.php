<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\{
    User,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class UserTest extends TestCase
{
    use TestTrait;

    public function testThrowContainsAWhitespaceOrIsEmpty()
    {
        $this
            ->forAll(Generator\elements('', ' ', 'f o'))
            ->then(function($invalid) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($invalid);

                new User($invalid);
            });
    }

    public function testAcceptsAnyStringWithoutAWhitespace()
    {
        $this
            ->forAll(Generator\string())
            ->when(static function($string): bool {
                return (bool) preg_match('~^\S+$~', $string);
            })
            ->then(function($string) {
                $this->assertSame($string, (string) new User($string));
            });
    }
}
