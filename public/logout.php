<?php

// セッション開始
session_start();

// Functionクラスをロード
require_once '../Functions.php';
$functions = new Functions();

// ログアウト後であればTOPページに遷移する
$result = $functions->checkLogin();
if(!$result) {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php');
    return;
}

// ログアウト手続き
$functions->logout();

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
<h2>ログアウト</h2>
    <p>ログアウト完了しました</p>
    <p><a href="./index.php">TOPページへ</a></p>
</body>

</html>
