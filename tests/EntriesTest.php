<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\{
    Entries,
    Mode,
};
use Innmind\BlackBox\PHPUnit\BlackBox\Proof;
use PHPUnit\Framework\Attributes\DataProvider;

class EntriesTest extends TestCase
{
    #[DataProvider('modesProvider')]
    public function testStringCast($modes, $expected)
    {
        \shuffle($modes);

        $this->assertSame(
            $expected,
            Entries::from(...$modes)->toString(),
        );
    }

    #[DataProvider('modesProvider')]
    public function testOnlyOneModePerKindIsKept($modes, $expected)
    {
        $this->assertSame(
            $expected,
            Entries::from(...$modes, ...$modes)->toString(),
        );
    }

    public function testDoNotAllowByDefault(): Proof
    {
        return $this
            ->forAll($this->mode())
            ->prove(function($mode) {
                $this->assertFalse(Entries::from()->allows($mode));
            });
    }

    public function testAllowTheSpecifiedEntry(): Proof
    {
        return $this
            ->forAll($this->mode())
            ->prove(function($mode) {
                $this->assertTrue(Entries::from($mode)->allows($mode));
            });
    }

    public function testDoNotAllowIfModeIsMissing()
    {
        $entries = Entries::from(Mode::read);

        $this->assertFalse($entries->allows(Mode::write));
        $this->assertFalse($entries->allows(Mode::execute));
    }

    public function testDoNotAllowIfOneModeIsMissing()
    {
        $entries = Entries::from(Mode::read);

        $this->assertFalse($entries->allows(Mode::read, Mode::write));
        $this->assertFalse($entries->allows(Mode::read, Mode::execute));
    }

    public function testAllowAsLongAsTheTestedModeIsInEntries(): Proof
    {
        return $this
            ->forAll($this->mode())
            ->prove(function($mode) {
                $entries = Entries::from(Mode::read, Mode::write, Mode::execute);

                $this->assertTrue($entries->allows($mode));
            });
    }

    #[DataProvider('modesProvider')]
    public function testOf($_, $modes)
    {
        $entries = Entries::of($modes);

        $this->assertInstanceOf(Entries::class, $entries);
        $this->assertSame($modes, $entries->toString());
    }

    public function testAddMode(): Proof
    {
        return $this
            ->forAll(
                $this->modes(),
                $this->modes(),
            )
            ->prove(function($initial, $toAdd) {
                $entries = Entries::from(...$initial);
                $entries2 = $entries->add(...$toAdd);

                $this->assertInstanceOf(Entries::class, $entries2);
                $this->assertNotSame($entries, $entries2);
                $this->assertSame(
                    Entries::from(...$initial)->toString(),
                    $entries->toString(),
                );
                $this->assertSame(
                    Entries::from(...$initial, ...$toAdd)->toString(),
                    $entries2->toString(),
                );
            });
    }

    public function testRemove(): Proof
    {
        return $this
            ->forAll(
                $this->modes(),
                $this->modes(),
            )
            ->prove(function($initial, $toRemove) {
                $entries = Entries::from(...$initial);
                $entries2 = $entries->remove(...$toRemove);

                $this->assertInstanceOf(Entries::class, $entries2);
                $this->assertNotSame($entries, $entries2);
                $this->assertSame(
                    Entries::from(...$initial)->toString(),
                    $entries->toString(),
                );
                $this->assertSame(
                    Entries::from(...$this->diff($initial, $toRemove))->toString(),
                    $entries2->toString(),
                );
            });
    }

    public static function modesProvider(): array
    {
        return [
            [
                [Mode::read, Mode::write, Mode::execute],
                'rwx',
            ],
            [
                [Mode::write, Mode::execute],
                '-wx',
            ],
            [
                [Mode::execute],
                '--x',
            ],
            [
                [],
                '---',
            ],
            [
                [Mode::read, Mode::execute],
                'r-x',
            ],
            [
                [Mode::read, Mode::write],
                'rw-',
            ],
            [
                [Mode::read],
                'r--',
            ],
            [
                [Mode::write],
                '-w-',
            ],
        ];
    }

    private function diff(array $modes, array $toRemove): array
    {
        return \array_filter(
            $modes,
            static fn($mode) => !\in_array($mode, $toRemove, true),
        );
    }
}
