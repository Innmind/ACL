<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\Mode;
use Innmind\Immutable\Sequence;
use Innmind\BlackBox\Set;

class ModeTest extends TestCase
{
    public function testRead()
    {
        $this->assertInstanceOf(Mode::class, Mode::read);
        $this->assertSame(Mode::read, Mode::read);
        $this->assertSame('r', Mode::read->toString());
        $this->assertSame(Mode::read, Mode::of('r'));
    }

    public function testWrite()
    {
        $this->assertInstanceOf(Mode::class, Mode::write);
        $this->assertSame(Mode::write, Mode::write);
        $this->assertSame('w', Mode::write->toString());
        $this->assertSame(Mode::write, Mode::of('w'));
    }

    public function testExecute()
    {
        $this->assertInstanceOf(Mode::class, Mode::execute);
        $this->assertSame(Mode::execute, Mode::execute);
        $this->assertSame('x', Mode::execute->toString());
        $this->assertSame(Mode::execute, Mode::of('x'));
    }

    public function testAll()
    {
        $this->assertInstanceOf(Sequence::class, Mode::all());
        $this->assertEquals(Mode::all(), Mode::all());
        $this->assertSame([Mode::read, Mode::write, Mode::execute], Mode::all()->toList());
    }

    public function testOfNull()
    {
        $this->assertNull(Mode::of('-'));
    }

    public function testThrowWhenBuildingModeFromUnknownString()
    {
        $this
            ->forAll(
                Set\Strings::any()->filter(static function($string): bool {
                    return !\in_array($string, ['r', 'w', 'x', '-'], true);
                }),
            )
            ->then(function($string) {
                $this->expectException(\UnhandledMatchError::class);

                Mode::of($string);
            });
    }
}
