# ACL

[![Build Status](https://github.com/innmind/acl/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/acl/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/acl/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/acl)
[![Type Coverage](https://shepherd.dev/github/innmind/acl/coverage.svg)](https://shepherd.dev/github/innmind/acl)

Small library to reproduce the logic of the unix filesystem access control list.

## Installation

```sh
composer require innmind/acl
```

## Usage

```php
use Innmind\ACL\{
    ACL,
    User,
    Group,
    Mode,
};

$acl = ACL::of('r---w---x user:group');

$acl->allows(new User('foo'), new Group('bar'), Mode::read()); // false
$acl->allows(new User('foo'), new Group('bar'), Mode::write()); // false
$acl->allows(new User('foo'), new Group('bar'), Mode::execute()); // true
$acl->allows(new User('foo'), new Group('group'), Mode::read()); // false
$acl->allows(new User('foo'), new Group('group'), Mode::write()); // true
$acl->allows(new User('foo'), new Group('group'), Mode::execute()); // true
$acl->allows(new User('user'), new Group('bar'), Mode::read()); // true
$acl->allows(new User('user'), new Group('bar'), Mode::write()); // false
$acl->allows(new User('user'), new Group('bar'), Mode::execute()); // true
$acl->allows(new User('user'), new Group('group'), Mode::read()); // true
$acl->allows(new User('user'), new Group('group'), Mode::write()); // true
$acl->allows(new User('user'), new Group('group'), Mode::execute()); // true
$acl->toString(); // outputs "r---w---x user:group"

$otherAcl = $acl->addUser(Mode::write());
$acl->toString(); // outputs "r---w---x user:group"
$otherAcl->toString(); // outputs "rw--w---x user:group"
```

The goal is to reproduce the logic of the filesystem ACL but at the application level so it can be persisted in a user entity and being completely decoupled from the real filesystem.
