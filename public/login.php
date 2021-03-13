<?php

// セッション開始
session_start();

// POSTにて受け取ったワンタイムトークンをセッション変数に入れる
$token = filter_input(INPUT_POST, 'csrf_token');

// Functionクラスをロード
require_once '../Functions.php';
$functions = new Functions();

// ログイン後であればマイページへ遷移する
$result = $functions->checkLogin();
if ($result) {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/mypage.php');
    return;
}

// エラー変数の定義
$err_msg = [];
$err_msg['msg'] = null;
$err_msg['email'] = null;
$err_msg['password'] = null;
$_SESSION['dl_del_msg'] = null;

// POSTにて送信された情報のバリデーションチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF対策
    if(!$functions->checkToken($token, $_SESSION['csrf_token'])) {
        exit('不正なリクエストのため強制終了しました');
    }
    unset($_SESSION['csrf_token']);

    $userEmail = filter_input(INPUT_POST, 'email');

    // 空要素かあるいはメールアドレスの形式に沿っていない場合はエラー表示
    if (!$userEmail) {
        $err_msg['email'] = '※メールアドレスが未入力です';
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $err_msg['email'] = '※メールアドレスの形式が異なります';
    }

    $userPass = filter_input(INPUT_POST, 'password');

    // 空要素である場合はエラー表示
    if (!$userPass) {
        $err_msg['password'] = '※パスワードが未入力です';
    }

    $err_judge = array_filter($err_msg);

    // エラーがなけれはログインするための手続きをする
    if (count($err_judge) === 0) {

        // 入力情報がデータベースに一致しているか確認
        $result = $functions->login($userEmail, $userPass);
        if (!$result) {
            $err_msg['msg'] = $_SESSION['msg'];
        } else {
            header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/mypage.php');
        }
    } 
}
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
    <h2>ログイン</h2>
    <form action="" method="POST">

        <?php if ($err_msg['msg']) : ?>
            <p><?php echo $err_msg['msg']; ?></p>
        <?php endif; ?>

        <p><label for="email">メールアドレス</label></p>
        <p><input type="email" name="email" id="email" placeholder="example@example.com" autofocus></p>

        <?php if ($err_msg['email']) : ?>
            <p><?php echo $err_msg['email']; ?></p>
        <?php endif; ?>

        <p><label for="password">パスワード</label></p>
        <p><input type="password" name="password" id="password" placeholder="8文字以上20文字以下の半角英数字"></p>

        <?php if ($err_msg['password']) : ?>
            <p><?php echo $err_msg['password']; ?></p>
        <?php endif; ?>

        <input type="hidden" name="csrf_token" value="<?php echo $functions->e($functions->setToken()); ?>">

        <p><input type="submit" value="ログイン"></p>
    </form>
    <p><a href="./index.php">TOPページへ</a></p>
</body>

</html>
