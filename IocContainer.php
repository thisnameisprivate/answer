<?php

class IocContainer {
    protected static $bindings;
    public static function bind ($abstract, Closure $concrete) {
        static::$bindings[$abstract] = $concrete;
    }
    public static function make ($abstract) {
        return call_user_func(static::$bindings[$abstract]);
    }
}
class talk {
    public function greet($target) {
        echo "Hello " . $target->getName();
    }
}
class A {
    public function getName () {
        return "World";
    }
}
$talk = new talk();
Container::bind('foo', function () {
    return new A;
});
$talk->greet(Container::make('foo')); // Hello, World.
$talk->greet(Container::make('foo'));
$talk->greet(Container::make('foo'));
$talk->greet(Container::make('foo'));