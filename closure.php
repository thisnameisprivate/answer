<?php


// 闭包函数实现

function getPrintStrFunc () {
    $func = function ($str) {
        echo $str;
    };
    return $func;
}
$printStrFunc = getPrintStrFunc();
$printStrFunc('Some Thing!');

// 匿名函数当做参数传递, 并调用它

function callFunc ($func) {
    $func('Some Thing.');
}
$printStrFunc = function ($str) {
    echo $str;
};
callFunc($printStrFunc);
callFunc(function ($str) {
    echo $str;
});

// 连接闭包和外界变量的关键字: use

function getMoney () {
    $rmb = 1;
    $dollar = 6;
    $func = function () use ($rmb) {
        echo $rmb;
        echo $dollar;
    };
    $func();
}
getMoney();

// 匿名函数中是否可以改变上下文的变量.. 不可以

function getMoney2 () {
    $rmb = 1;
    $func = function () use ($rmb) {
        echo $rmb;
        $rmb ++;
    };
    $func();
    echo $rmb;
}
getMoney1();
// 得出结论, use 只是复制了一个原变量的副本;所以无法跟更改

// 若要改变原内存地址的变量, 在变量前面加&引用即可.
function getMoney3 () {
    $rmb = 1;
    $func = function () use (&$rmb) {
        echo $rmb;
        $rmb++;
    };
    $func();
    echo $rmb;
}
// 如果将匿名函数返回给外界，匿名函数会保存use所引用的变量，而外界则不能得到这些变量，这样形成‘闭包’这个概念可能会更清晰一些。


function getMoneyFunc () {
    $rmb = 1;
    $func = function () use (&$rmb) {
        echo $rmb;
        $rmb ++;
    };
    return func;
}
$getMoeny = getMoneyFunc();
$getMoeny();
$getMoeny();
$getMoeny();
