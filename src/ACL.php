<?php
declare(strict_types = 1);

namespace Innmind\ACL;

final class ACL
{
    private $user;
    private $group;
    private $userEntries;
    private $groupEntries;
    private $otherEntries;

    public function __construct(
        User $user,
        Group $group,
        Entries $userEntries,
        Entries $groupEntries,
        Entries $otherEntries
    ) {
        $this->user = $user;
        $this->group = $group;
        $this->userEntries = $userEntries;
        $this->groupEntries = $groupEntries;
        $this->otherEntries = $otherEntries;
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

    public function __toString(): string
    {
        return "{$this->userEntries}{$this->groupEntries}{$this->otherEntries} {$this->user}:{$this->group}";
    }
}
