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

class ACLTest extends TestCase
{
    public function testStringCast()
    {
        $this
            ->forAll(
                $this->user(),
                $this->group(),
                $this->modes(),
                $this->modes(),
                $this->modes()
            )
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
            ->forAll(
                $this->user(),
                $this->group(),
                $this->modes(),
                $this->modes(),
                $this->modes()
            )
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
            ->forAll($this->mode())
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
            ->forAll($this->mode())
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
            ->forAll($this->mode())
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
            ->forAll($this->mode())
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
            ->forAll($this->mode())
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
            ->forAll($this->mode())
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
            ->forAll($this->mode())
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
            ->forAll($this->mode())
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
            ->forAll(
                $this->user(),
                $this->group(),
                $this->modes(),
                $this->modes(),
                $this->modes(),
                $this->modes()
            )
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
            ->forAll(
                $this->user(),
                $this->group(),
                $this->modes(),
                $this->modes(),
                $this->modes(),
                $this->modes()
            )
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
            ->forAll(
                $this->user(),
                $this->group(),
                $this->modes(),
                $this->modes(),
                $this->modes(),
                $this->modes()
            )
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
            ->forAll(
                $this->user(),
                $this->group(),
                $this->modes(),
                $this->modes(),
                $this->modes(),
                $this->modes()
            )
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
            ->forAll(
                $this->user(),
                $this->group(),
                $this->modes(),
                $this->modes(),
                $this->modes(),
                $this->modes()
            )
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
            ->forAll(
                $this->user(),
                $this->group(),
                $this->modes(),
                $this->modes(),
                $this->modes(),
                $this->modes()
            )
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
    ): string {
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
        return \array_filter(
            $entries,
            static fn($entry) => !\in_array($entry, $toRemove, true),
        );
    }
}
