<?php

use Foo\Baz\Qux\Forest;
use Some\ClassName as ClassAlias;

enum GlobalEnum {
    case Admin;
    case Guest;
    case Moderator;
}

test('enums', function () {
    $f = function (GlobalEnum $role) {
        return $role;
    };

    $e = 'function (\GlobalEnum $role) {
        return $role;
    }';

    expect($f)->toBeCode($e);

    enum ScopedEnum {
        case Admin;
        case Guest;
        case Moderator;
    }

    $f = function (ScopedEnum $role) {
        return $role;
    };

    $e = 'function (\ScopedEnum $role) {
        return $role;
    }';

    expect($f)->toBeCode($e);
});


enum GlobalBackedEnum: string {
    case Admin = 'Administrator';
    case Guest = 'Guest';
    case Moderator = 'Moderator';
}

test('backed enums', function () {

    $f = function (GlobalBackedEnum $role) {
        return $role;
    };

    $e = 'function (\GlobalBackedEnum $role) {
        return $role;
    }';

    expect($f)->toBeCode($e);

    enum ScopedBackedEnum: string {
        case Admin = 'Administrator';
        case Guest = 'Guest';
        case Moderator = 'Moderator';
    }

    $f = function (ScopedBackedEnum $role) {
        return $role;
    };

    $e = 'function (\ScopedBackedEnum $role) {
        return $role;
    }';

    expect($f)->toBeCode($e);
});

test('array unpacking', function () {
    $f = function () {
        $array1 = ['a' => 1];

        $array2 = ['b' => 2];

        return ['a' => 0, ...$array1, ...$array2];
    };

    $e = "function () {
        \$array1 = ['a' => 1];

        \$array2 = ['b' => 2];

        return ['a' => 0, ...\$array1, ...\$array2];
    }";

    expect($f)->toBeCode($e);
});

test('new in initializers', function () {
    $f = function () {
        return new ReflectionClosurePhp81Controller();
    };

    $e = 'function () {
        return new \ReflectionClosurePhp81Controller();
    }';

    expect($f)->toBeCode($e);
});

test('readonly properties', function () {
    $f = function () {
        $controller = new ReflectionClosurePhp81Controller();

        $controller->service = 'foo';
    };

    $e = 'function () {
        $controller = new \ReflectionClosurePhp81Controller();

        $controller->service = \'foo\';
    }';

    expect($f)->toBeCode($e);
});

test('first-class callable with closures', function () {
    $f = function ($a) {
        $f = fn ($b) => $a + $b + 1;

        return $f(...);
    };

    $e = 'function ($a) {
        $f = fn ($b) => $a + $b + 1;

        return $f(...);
    }';

    expect($f)->toBeCode($e);
});

test('first-class callable with methods', function () {
    $f = (new ReflectionClosurePhp81Controller())->publicGetter(...);

    $e = 'function ()
    {
        return $this->privateGetter();
    }';

    expect($f)->toBeCode($e);

    $f = (new ReflectionClosurePhp81Controller())->publicGetterResolver(...);

    $e = 'function ()
    {
        return $this->privateGetterResolver(...);
    }';

    expect($f)->toBeCode($e);
});

test('first-class callable with static methods', function () {
    $f = ReflectionClosurePhp81Controller::publicStaticGetter(...);

    $e = 'static function ()
    {
        return static::privateStaticGetter();
    }';

    expect($f)->toBeCode($e);

    $f = ReflectionClosurePhp81Controller::publicStaticGetterResolver(...);

    $e = 'static function ()
    {
        return static::privateStaticGetterResolver(...);
    }';

    expect($f)->toBeCode($e);
});

test('first-class callable final method', function () {
    $f = (new ReflectionClosurePhp81Controller())->finalPublicGetterResolver(...);

    $e = 'function ()
    {
        return $this->privateGetterResolver(...);
    }';

    expect($f)->toBeCode($e);

    $f = ReflectionClosurePhp81Controller::finalPublicStaticGetterResolver(...);

    $e = 'static function ()
    {
        return static::privateStaticGetterResolver(...);
    }';

    expect($f)->toBeCode($e);
});

test('first-class callable self return type', function () {
    $f = (new ReflectionClosurePhp81Controller())->getSelf(...);

    $e = 'function (self $instance): self
    {
        return $instance;
    }';

    expect($f)->toBeCode($e);
});

test('intersection types', function () {
    $f = function (ClassAlias&Forest $service): ClassAlias&Forest {
        return $service;
    };

    $e = 'function (\Some\ClassName&\Foo\Baz\Qux\Forest $service): \Some\ClassName&\Foo\Baz\Qux\Forest {
        return $service;
    }';

    expect($f)->toBeCode($e);
});

test('never type', function () {
    $f = function (): never {
        throw new RuntimeException('Something wrong happened.');
    };

    $e = 'function (): never {
        throw new \RuntimeException(\'Something wrong happened.\');
    }';

    expect($f)->toBeCode($e);
});

test('array_is_list', function () {
    $f = function () {
        return array_is_list([]);
    };

    $e = 'function () {
        return \array_is_list([]);
    }';

    expect($f)->toBeCode($e);
});

test('final class constants', function () {
    $f = function () {
        return ReflectionClosurePhp81Service::X;
    };

    $e = 'function () {
    return ReflectionClosurePhp81Service::X;
};';

    expect($f)->toBeCode($e);
})->skip('Constants in anonymous classes is not supported.');

class ReflectionClosurePhp81Service
{
}

class ReflectionClosurePhp81Controller
{
    public function __construct(
        public readonly ReflectionClosurePhp81Service $service = new ReflectionClosurePhp81Service(),
    ) {
        // ..
    }

    public function publicGetter()
    {
        return $this->privateGetter();
    }

    private function privateGetter()
    {
        return $this->service;
    }

    public static function publicStaticGetter()
    {
        return static::privateStaticGetter();
    }

    public static function privateStaticGetter()
    {
        return (new ReflectionClosurePhp81Controller())->service;
    }

    public function publicGetterResolver()
    {
        return $this->privateGetterResolver(...);
    }

    private function privateGetterResolver()
    {
        return fn () => $this->service;
    }

    public static function publicStaticGetterResolver()
    {
        return static::privateStaticGetterResolver(...);
    }

    public static function privateStaticGetterResolver()
    {
        return fn () => (new ReflectionClosurePhp81Controller())->service;
    }

    final public function finalPublicGetterResolver()
    {
        return $this->privateGetterResolver(...);
    }

    final public static function finalPublicStaticGetterResolver()
    {
        return static::privateStaticGetterResolver(...);
    }

    public function getSelf(self $instance): self
    {
        return $instance;
    }
}
