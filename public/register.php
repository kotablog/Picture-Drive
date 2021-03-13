<?php

// セッション開始
session_start();

// signin.phpからの遷移でなければTOPページに遷移する
if (!isset($_SESSION['hasCreated']) || !$_SESSION['hasCreated']) {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php');
    return;
}
unset($_SESSION['hasCreated']);

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
    <h2>ユーザー登録が完了しました</h2>
    <p>早速ログインして使用してみよう!</p>
    <p><a href="./index.php">TOPページへ</a></p>
    <p><a href="./login.php">ログインフォームへ</a></p>
</body>

</html>
