<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\{
    Entries,
    Mode,
};
use PHPUnit\Framework\TestCase;

class EntriesTest extends TestCase
{
    /**
     * @dataProvider modes
     */
    public function testStringCast($modes, $expected)
    {
        shuffle($modes);

        $this->assertSame(
            $expected,
            (string) new Entries(...$modes)
        );
    }

    public function modes(): array
    {
        return [
            [
                [Mode::read(), Mode::write(), Mode::execute()],
                'rwx',
            ],
            [
                [Mode::write(), Mode::execute()],
                '-wx',
            ],
            [
                [Mode::execute()],
                '--x',
            ],
            [
                [],
                '---',
            ],
            [
                [Mode::read(), Mode::execute()],
                'r-x',
            ],
            [
                [Mode::read(), Mode::write()],
                'rw-',
            ],
            [
                [Mode::read()],
                'r--',
            ],
            [
                [Mode::write()],
                '-w-',
            ],
        ];
    }
}
