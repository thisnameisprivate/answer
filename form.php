<?php


header("content-type: text/html;charset=utf8");
$cfg = include("./config.php");
function e () {
    if (
        $_POST['sex'] == ''
        || $_POST['num'] == ''
        || $_POST['username'] == ''
        || $_POST['phone'] == ''
        || $_POST['English'] == ''
        || $_POST['Chines'] == ''
        || $_POST['Cantonese'] == ''
        || $_POST['computer'] == ''
        || $_POST['height'] == ''
        || $_POST['hobby'] == ''
        || $_POST['aim'] == ''
        || $_POST['palce'] == ''
        || $_POST['work'] == ''
        || $_POST['pay'] == ''
    ) {
        return true;
    }
}
if (e()) {
    echo "<script> alert('请将表单填好在提交~'); location='form.html'</script>";
    return;
}
$aimStr = '';
for ($i = 0; $i < count($aim); $i ++) {
    $aimStr .= $aim[$i] . "/";
}
$aimStr = substr($aimStr, 0, -1);
$sqlArr = [
    "sex" => $sex,
    "num" => $num,
    "username" => $username,
    "phone" => $phone,
    "english" => $english,
    "chinese" => $chinese,
    "catnonese" => $cantonese,
    "height" => $height,
    "hobby" => $hobby,
    "aim" => $aimStr,
    "place" => $palce,
    "work" => $work,
    "pay" => $pay,
];