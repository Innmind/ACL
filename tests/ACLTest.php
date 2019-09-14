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
                    "$userEntries$groupEntries$otherEntries $user:$group",
                    (string) $acl
                );
            });
    }
}
