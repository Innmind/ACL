# ACL

| `develop` |
|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/ACL/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/ACL/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/ACL/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/ACL/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/ACL/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/ACL/build-status/develop) |

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
(string) $acl; // outputs "r---w---x user:group"
```

The goal is to reproduce the logic of the filesystem ACL but at the application level so it can be persisted in a user entity and being completely decoupled from the real filesystem.
