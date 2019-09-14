<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\Mode;
use Innmind\Immutable\StreamInterface;
use PHPUnit\Framework\TestCase;

class ModeTest extends TestCase
{
    public function testRead()
    {
        $this->assertInstanceOf(Mode::class, Mode::read());
        $this->assertSame(Mode::read(), Mode::read());
        $this->assertSame('r', (string) Mode::read());
    }

    public function testWrite()
    {
        $this->assertInstanceOf(Mode::class, Mode::write());
        $this->assertSame(Mode::write(), Mode::write());
        $this->assertSame('w', (string) Mode::write());
    }

    public function testExecute()
    {
        $this->assertInstanceOf(Mode::class, Mode::execute());
        $this->assertSame(Mode::execute(), Mode::execute());
        $this->assertSame('x', (string) Mode::execute());
    }

    public function testAll()
    {
        $this->assertInstanceOf(StreamInterface::class, Mode::all());
        $this->assertSame(Mode::class, (string) Mode::all()->type());
        $this->assertSame(Mode::all(), Mode::all());
        $this->assertSame([Mode::read(), Mode::write(), Mode::execute()], Mode::all()->toPrimitive());
    }
}
