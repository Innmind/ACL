<?php
declare(strict_types = 1);

namespace Tests\Innmind\ACL;

use Innmind\ACL\{
    ACL,
    Entries,
    Mode,
    User,
    Group,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class ACLTest extends TestCase
{
    use TestTrait;

    public function testStringCast()
    {
        $this
            ->minimumEvaluationRatio(0.3)
            ->forAll(
                Generator\string(),
                Generator\string(),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            )
            ->when(static function($user, $group): bool {
                return (bool) preg_match('~^\S+$~', $user) &&
                    (bool) preg_match('~^\S+$~', $group) &&
                    strpos($user, ':') === false &&
                    strpos($group, ':') === false;
            })
            ->then(function($user, $group, $userEntries, $groupEntries, $otherEntries) {
                $userEntries = new Entries(...$userEntries);
                $groupEntries = new Entries(...$groupEntries);
                $otherEntries = new Entries(...$otherEntries);

                $acl = new ACL(
                    new User($user),
                    new Group($group),
                    $userEntries,
                    $groupEntries,
                    $otherEntries
                );

                $this->assertSame(
                    $this->format($userEntries, $groupEntries, $otherEntries, $user, $group),
                    $acl->toString()
                );
            });
    }

    public function testOf()
    {
        $this
            ->minimumEvaluationRatio(0.3)
            ->forAll(
                Generator\string(),
                Generator\string(),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            )
            ->when(static function($user, $group): bool {
                return (bool) preg_match('~^\S+$~', $user) &&
                    (bool) preg_match('~^\S+$~', $group) &&
                    strpos($user, ':') === false &&
                    strpos($group, ':') === false;
            })
            ->then(function($user, $group, $userEntries, $groupEntries, $otherEntries) {
                $userEntries = new Entries(...$userEntries);
                $groupEntries = new Entries(...$groupEntries);
                $otherEntries = new Entries(...$otherEntries);

                $acl = new ACL(
                    new User($user),
                    new Group($group),
                    $userEntries,
                    $groupEntries,
                    $otherEntries
                );

                $acl2 = ACL::of($acl->toString());

                $this->assertNotSame($acl, $acl2);
                $this->assertSame(
                    $acl->toString(),
                    $acl2->toString()
                );
            });
    }

    public function testAllowsWhenOtherEntriesAllowsIt()
    {
        $this
            ->forAll(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            ->then(function($mode) {
                $acl = new ACL(
                    new User('foo'),
                    new Group('bar'),
                    new Entries,
                    new Entries,
                    new Entries($mode)
                );

                $this->assertTrue($acl->allows(
                    new User('baz'),
                    new Group('baz'),
                    $mode
                ));
            });
    }

    public function testDoesNotAllowWhenOtherEntriesDoesNotAllowIt()
    {
        $this
            ->forAll(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            ->then(function($mode) {
                $acl = new ACL(
                    new User('foo'),
                    new Group('bar'),
                    new Entries,
                    new Entries,
                    new Entries
                );

                $this->assertFalse($acl->allows(
                    new User('baz'),
                    new Group('baz'),
                    $mode
                ));
            });
    }

    public function testDoesNotAllowWhenGroupEntriesAllowsItButNotInTheSameGroup()
    {
        $this
            ->forAll(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            ->then(function($mode) {
                $acl = new ACL(
                    new User('foo'),
                    new Group('bar'),
                    new Entries,
                    new Entries($mode),
                    new Entries
                );

                $this->assertFalse($acl->allows(
                    new User('baz'),
                    new Group('baz'),
                    $mode
                ));
            });
    }

    public function testDoesNotAllowWhenInGroupButGroupEntriesDoesNotAllowIt()
    {
        $this
            ->forAll(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            ->then(function($mode) {
                $acl = new ACL(
                    new User('foo'),
                    new Group('bar'),
                    new Entries,
                    new Entries,
                    new Entries
                );

                $this->assertFalse($acl->allows(
                    new User('baz'),
                    new Group('bar'),
                    $mode
                ));
            });
    }

    public function testAllowsWhenInGroupAndGroupEntriesAllowsIt()
    {
        $this
            ->forAll(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            ->then(function($mode) {
                $acl = new ACL(
                    new User('foo'),
                    new Group('bar'),
                    new Entries,
                    new Entries($mode),
                    new Entries
                );

                $this->assertTrue($acl->allows(
                    new User('baz'),
                    new Group('bar'),
                    $mode
                ));
            });
    }

    public function testDoesNotAllowWhenUserEntriesAllowsItButNotTheSameUser()
    {
        $this
            ->forAll(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            ->then(function($mode) {
                $acl = new ACL(
                    new User('foo'),
                    new Group('bar'),
                    new Entries($mode),
                    new Entries,
                    new Entries
                );

                $this->assertFalse($acl->allows(
                    new User('baz'),
                    new Group('baz'),
                    $mode
                ));
            });
    }

    public function testDoesNotAllowWhenSameUserButUserEntriesDoesNotAllowIt()
    {
        $this
            ->forAll(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            ->then(function($mode) {
                $acl = new ACL(
                    new User('foo'),
                    new Group('bar'),
                    new Entries,
                    new Entries,
                    new Entries
                );

                $this->assertFalse($acl->allows(
                    new User('foo'),
                    new Group('baz'),
                    $mode
                ));
            });
    }

    public function testAllowsWhenSameUserAndUserEntriesAllowsIt()
    {
        $this
            ->forAll(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            ->then(function($mode) {
                $acl = new ACL(
                    new User('foo'),
                    new Group('bar'),
                    new Entries($mode),
                    new Entries,
                    new Entries
                );

                $this->assertTrue($acl->allows(
                    new User('foo'),
                    new Group('baz'),
                    $mode
                ));
            });
    }

    public function testAddModeToUser()
    {
        $this
            ->minimumEvaluationRatio(0.3)
            ->forAll(
                Generator\string(),
                Generator\string(),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            )
            ->when(static function($user, $group): bool {
                return (bool) preg_match('~^\S+$~', $user) &&
                    (bool) preg_match('~^\S+$~', $group) &&
                    strpos($user, ':') === false &&
                    strpos($group, ':') === false;
            })
            ->then(function($user, $group, $userEntries, $groupEntries, $otherEntries, $toAdd) {
                $expectedUser = new Entries(...$userEntries, ...$toAdd);
                $userEntries = new Entries(...$userEntries);
                $groupEntries = new Entries(...$groupEntries);
                $otherEntries = new Entries(...$otherEntries);

                $acl = new ACL(
                    new User($user),
                    new Group($group),
                    $userEntries,
                    $groupEntries,
                    $otherEntries
                );

                $acl2 = $acl->addUser(...$toAdd);

                $this->assertInstanceOf(ACL::class, $acl2);
                $this->assertNotSame($acl, $acl2);
                $this->assertSame(
                    $this->format($userEntries, $groupEntries, $otherEntries, $user, $group),
                    $acl->toString()
                );
                $this->assertSame(
                    $this->format($expectedUser, $groupEntries, $otherEntries, $user, $group),
                    $acl2->toString()
                );
            });
    }

    public function testAddModeToGroup()
    {
        $this
            ->minimumEvaluationRatio(0.3)
            ->forAll(
                Generator\string(),
                Generator\string(),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            )
            ->when(static function($user, $group): bool {
                return (bool) preg_match('~^\S+$~', $user) &&
                    (bool) preg_match('~^\S+$~', $group) &&
                    strpos($user, ':') === false &&
                    strpos($group, ':') === false;
            })
            ->then(function($user, $group, $userEntries, $groupEntries, $otherEntries, $toAdd) {
                $expectedGroup = new Entries(...$groupEntries, ...$toAdd);
                $userEntries = new Entries(...$userEntries);
                $groupEntries = new Entries(...$groupEntries);
                $otherEntries = new Entries(...$otherEntries);

                $acl = new ACL(
                    new User($user),
                    new Group($group),
                    $userEntries,
                    $groupEntries,
                    $otherEntries
                );

                $acl2 = $acl->addGroup(...$toAdd);

                $this->assertInstanceOf(ACL::class, $acl2);
                $this->assertNotSame($acl, $acl2);
                $this->assertSame(
                    $this->format($userEntries, $groupEntries, $otherEntries, $user, $group),
                    $acl->toString()
                );
                $this->assertSame(
                    $this->format($userEntries, $expectedGroup, $otherEntries, $user, $group),
                    $acl2->toString()
                );
            });
    }

    public function testAddModeToOther()
    {
        $this
            ->minimumEvaluationRatio(0.3)
            ->forAll(
                Generator\string(),
                Generator\string(),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            )
            ->when(static function($user, $group): bool {
                return (bool) preg_match('~^\S+$~', $user) &&
                    (bool) preg_match('~^\S+$~', $group) &&
                    strpos($user, ':') === false &&
                    strpos($group, ':') === false;
            })
            ->then(function($user, $group, $userEntries, $groupEntries, $otherEntries, $toAdd) {
                $expectedOther = new Entries(...$otherEntries, ...$toAdd);
                $userEntries = new Entries(...$userEntries);
                $groupEntries = new Entries(...$groupEntries);
                $otherEntries = new Entries(...$otherEntries);

                $acl = new ACL(
                    new User($user),
                    new Group($group),
                    $userEntries,
                    $groupEntries,
                    $otherEntries
                );

                $acl2 = $acl->addOther(...$toAdd);

                $this->assertInstanceOf(ACL::class, $acl2);
                $this->assertNotSame($acl, $acl2);
                $this->assertSame(
                    $this->format($userEntries, $groupEntries, $otherEntries, $user, $group),
                    $acl->toString()
                );
                $this->assertSame(
                    $this->format($userEntries, $groupEntries, $expectedOther, $user, $group),
                    $acl2->toString()
                );
            });
    }

    public function testRemoveModeFromUser()
    {
        $this
            ->minimumEvaluationRatio(0.3)
            ->forAll(
                Generator\string(),
                Generator\string(),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            )
            ->when(static function($user, $group): bool {
                return (bool) preg_match('~^\S+$~', $user) &&
                    (bool) preg_match('~^\S+$~', $group) &&
                    strpos($user, ':') === false &&
                    strpos($group, ':') === false;
            })
            ->then(function($user, $group, $userEntries, $groupEntries, $otherEntries, $toRemove) {
                $expectedUser = new Entries(...$this->diff($userEntries, $toRemove));
                $userEntries = new Entries(...$userEntries);
                $groupEntries = new Entries(...$groupEntries);
                $otherEntries = new Entries(...$otherEntries);

                $acl = new ACL(
                    new User($user),
                    new Group($group),
                    $userEntries,
                    $groupEntries,
                    $otherEntries
                );

                $acl2 = $acl->removeUser(...$toRemove);

                $this->assertInstanceOf(ACL::class, $acl2);
                $this->assertNotSame($acl, $acl2);
                $this->assertSame(
                    $this->format($userEntries, $groupEntries, $otherEntries, $user, $group),
                    $acl->toString()
                );
                $this->assertSame(
                    $this->format($expectedUser, $groupEntries, $otherEntries, $user, $group),
                    $acl2->toString()
                );
            });
    }

    public function testRemoveModeFromGroup()
    {
        $this
            ->minimumEvaluationRatio(0.3)
            ->forAll(
                Generator\string(),
                Generator\string(),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            )
            ->when(static function($user, $group): bool {
                return (bool) preg_match('~^\S+$~', $user) &&
                    (bool) preg_match('~^\S+$~', $group) &&
                    strpos($user, ':') === false &&
                    strpos($group, ':') === false;
            })
            ->then(function($user, $group, $userEntries, $groupEntries, $otherEntries, $toRemove) {
                $expectedGroup = new Entries(...$this->diff($groupEntries, $toRemove));
                $userEntries = new Entries(...$userEntries);
                $groupEntries = new Entries(...$groupEntries);
                $otherEntries = new Entries(...$otherEntries);

                $acl = new ACL(
                    new User($user),
                    new Group($group),
                    $userEntries,
                    $groupEntries,
                    $otherEntries
                );

                $acl2 = $acl->removeGroup(...$toRemove);

                $this->assertInstanceOf(ACL::class, $acl2);
                $this->assertNotSame($acl, $acl2);
                $this->assertSame(
                    $this->format($userEntries, $groupEntries, $otherEntries, $user, $group),
                    $acl->toString()
                );
                $this->assertSame(
                    $this->format($userEntries, $expectedGroup, $otherEntries, $user, $group),
                    $acl2->toString()
                );
            });
    }

    public function testRemoveModeFromOther()
    {
        $this
            ->minimumEvaluationRatio(0.3)
            ->forAll(
                Generator\string(),
                Generator\string(),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute())),
                Generator\seq(Generator\elements(Mode::read(), Mode::write(), Mode::execute()))
            )
            ->when(static function($user, $group): bool {
                return (bool) preg_match('~^\S+$~', $user) &&
                    (bool) preg_match('~^\S+$~', $group) &&
                    strpos($user, ':') === false &&
                    strpos($group, ':') === false;
            })
            ->then(function($user, $group, $userEntries, $groupEntries, $otherEntries, $toRemove) {
                $expectedOther = new Entries(...$this->diff($otherEntries, $toRemove));
                $userEntries = new Entries(...$userEntries);
                $groupEntries = new Entries(...$groupEntries);
                $otherEntries = new Entries(...$otherEntries);

                $acl = new ACL(
                    new User($user),
                    new Group($group),
                    $userEntries,
                    $groupEntries,
                    $otherEntries
                );

                $acl2 = $acl->removeOther(...$toRemove);

                $this->assertInstanceOf(ACL::class, $acl2);
                $this->assertNotSame($acl, $acl2);
                $this->assertSame(
                    $this->format($userEntries, $groupEntries, $otherEntries, $user, $group),
                    $acl->toString()
                );
                $this->assertSame(
                    $this->format($userEntries, $groupEntries, $expectedOther, $user, $group),
                    $acl2->toString()
                );
            });
    }

    private function format(
        Entries $userEntries,
        Entries $groupEntries,
        Entries $otherEntries,
        string $user,
        string $group
    ): string
    {
        return \sprintf(
            '%s%s%s %s:%s',
            $userEntries->toString(),
            $groupEntries->toString(),
            $otherEntries->toString(),
            $user,
            $group,
        );
    }

    private function diff(array $entries, array $toRemove): array
    {
        return array_filter(
            $entries,
            fn($entry) => !in_array($entry, $toRemove, true),
        );
    }
}
