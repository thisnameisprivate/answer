<?php


// �հ�����ʵ��

function getPrintStrFunc () {
    $func = function ($str) {
        echo $str;
    };
    return $func;
}
$printStrFunc = getPrintStrFunc();
$printStrFunc('Some Thing!');

// ��������������������, ��������

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

// ���ӱհ����������Ĺؼ���: use

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

// �����������Ƿ���Ըı������ĵı���.. ������

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
// �ó�����, use ֻ�Ǹ�����һ��ԭ�����ĸ���;�����޷�������

// ��Ҫ�ı�ԭ�ڴ��ַ�ı���, �ڱ���ǰ���&���ü���.
function getMoney3 () {
    $rmb = 1;
    $func = function () use (&$rmb) {
        echo $rmb;
        $rmb++;
    };
    $func();
    echo $rmb;
}
// ����������������ظ���磬���������ᱣ��use�����õı�������������ܵõ���Щ�����������γɡ��հ������������ܻ������һЩ��


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