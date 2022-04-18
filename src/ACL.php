<?php
declare(strict_types = 1);

namespace Innmind\ACL;

use Innmind\Immutable\Str;

final class ACL
{
    private User $user;
    private Group $group;
    private Entries $userEntries;
    private Entries $groupEntries;
    private Entries $otherEntries;

    public function __construct(
        User $user,
        Group $group,
        Entries $userEntries,
        Entries $groupEntries,
        Entries $otherEntries,
    ) {
        $this->user = $user;
        $this->group = $group;
        $this->userEntries = $userEntries;
        $this->groupEntries = $groupEntries;
        $this->otherEntries = $otherEntries;
    }

    public static function of(string $string): self
    {
        $string = Str::of($string);
        [$userEntries, $groupEntries, $otherEntries] = $string->take(9)->chunk(3)->toList();
        [$user, $group] = $string->drop(10)->split(':')->toList();

        return new self(
            User::of($user->toString()),
            Group::of($group->toString()),
            Entries::of($userEntries->toString()),
            Entries::of($groupEntries->toString()),
            Entries::of($otherEntries->toString()),
        );
    }

    public function addUser(Mode ...$modes): self
    {
        return new self(
            $this->user,
            $this->group,
            $this->userEntries->add(...$modes),
            $this->groupEntries,
            $this->otherEntries,
        );
    }

    public function addGroup(Mode ...$modes): self
    {
        return new self(
            $this->user,
            $this->group,
            $this->userEntries,
            $this->groupEntries->add(...$modes),
            $this->otherEntries,
        );
    }

    public function addOther(Mode ...$modes): self
    {
        return new self(
            $this->user,
            $this->group,
            $this->userEntries,
            $this->groupEntries,
            $this->otherEntries->add(...$modes),
        );
    }

    /**
     * @no-named-arguments
     */
    public function removeUser(Mode ...$modes): self
    {
        return new self(
            $this->user,
            $this->group,
            $this->userEntries->remove(...$modes),
            $this->groupEntries,
            $this->otherEntries,
        );
    }

    /**
     * @no-named-arguments
     */
    public function removeGroup(Mode ...$modes): self
    {
        return new self(
            $this->user,
            $this->group,
            $this->userEntries,
            $this->groupEntries->remove(...$modes),
            $this->otherEntries,
        );
    }

    /**
     * @no-named-arguments
     */
    public function removeOther(Mode ...$modes): self
    {
        return new self(
            $this->user,
            $this->group,
            $this->userEntries,
            $this->groupEntries,
            $this->otherEntries->remove(...$modes),
        );
    }

    public function allows(User $user, Group $group, Mode $mode, Mode ...$modes): bool
    {
        if ($this->otherEntries->allows($mode, ...$modes)) {
            return true;
        }

        if ($this->group->equals($group) && $this->groupEntries->allows($mode, ...$modes)) {
            return true;
        }

        if ($this->user->equals($user) && $this->userEntries->allows($mode, ...$modes)) {
            return true;
        }

        return false;
    }

    public function toString(): string
    {
        return \sprintf(
            '%s%s%s %s:%s',
            $this->userEntries->toString(),
            $this->groupEntries->toString(),
            $this->otherEntries->toString(),
            $this->user->toString(),
            $this->group->toString(),
        );
    }
}
