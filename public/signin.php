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
$err_msg['name'] = null;
$err_msg['email'] = null;
$err_msg['password'] = null;
$err_msg['password_conf'] = null;

// POSTにて送信された情報のバリデーションチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF対策
    if(!$functions->checkToken($token, $_SESSION['csrf_token'])) {
        exit('不正なリクエストのため強制終了しました');
    }
    unset($_SESSION['csrf_token']);

    $userName = filter_input(INPUT_POST, 'name');

    // 空要素かあるいは40文字以上である場合はエラー表示
    if (!$userName) {
        $err_msg['name'] = '※氏名が未入力です';
    } elseif (mb_strlen($userName) > 40) {
        $err_msg['name'] = '※氏名は40文字以内にして下さい';
    }

    $userEmail = filter_input(INPUT_POST, 'email');

    // 空要素かあるいはメールアドレスの形式に沿っていない場合はエラー表示
    if (!$userEmail) {
        $err_msg['email'] = '※メールアドレスが未入力です';
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $err_msg['email'] = '※メールアドレスの形式と異なっています';
    }

    $userPass = filter_input(INPUT_POST, 'password');

    // 空要素かあるいは形式に沿っていない場合はエラー表示
    if (!$userPass) {
        $err_msg['password'] = '※パスワードが未入力です';
    } elseif (!preg_match("/\A[a-z\d]{8,20}+\z/i", $userPass)) {
        $err_msg['password'] = '※パスワードは8文字以上20文字以内の半角英数字にして下さい';
    }

    $userPass_conf = filter_input(INPUT_POST, 'password_conf');

    // パスワードと確認用パスワードが一致していない場合はエラー表示
    if ($userPass_conf !== $userPass) {
        $err_msg['password_conf'] = '※パスワードと確認用のパスワードが一致しませんでした';
    }

    $err_judge = array_filter($err_msg);

    // エラーがなけれは登録情報をデータベースに保存
    if (count($err_judge) === 0) {
        $hasCreated = $functions->createUser($userName, $userEmail, $userPass);
        // 過去に登録されているかどうか判別
        if ($hasCreated) {
            $_SESSION['hasCreated'] = true;
            header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/register.php');
        } else {
            $err_msg['msg'] = '※入力されたメールアドレスはすでに登録済みです 問題がある場合は管理人にまでお問い合わせください';
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
    <h2>ユーザー登録</h2>

    <?php if ($err_msg['msg']) : ?>
        <p><?php echo $err_msg['msg']; ?></p>
    <?php endif; ?>

    <form action="" method="POST">

        <p><label for="name">氏名</label></p>
        <p><input type="name" name="name" id="name" placeholder="example" autofocus></p>

        <?php if ($err_msg['name']) : ?>
            <p><?php echo $err_msg['name']; ?></p>
        <?php endif; ?>

        <p><label for="email">メールアドレス</label></p>
        <p><input type="email" name="email" id="email" placeholder="example@example.com" ></p>

        <?php if ($err_msg['email']) : ?>
            <p><?php echo $err_msg['email']; ?></p>
        <?php endif; ?>

        <p><label for="password">パスワード</label></p>
        <p><input type="password" name="password" id="password" placeholder="8文字以上20文字以内の半角英数字"></p>

        <?php if ($err_msg['password']) : ?>
            <p><?php echo $err_msg['password']; ?></p>
        <?php endif; ?>

        <p><label for="password_conf">確認用パスワード</label></p>
        <p><input type="password" name="password_conf" id="password_conf" placeholder="8文字以上20文字以内の半角英数字"></p>

        <?php if ($err_msg['password_conf']) : ?>
            <p><?php echo $err_msg['password_conf']; ?></p>
        <?php endif; ?>

        <input type="hidden" name="csrf_token" value="<?php echo $functions->e($functions->setToken()); ?>">

        <p><input type="submit" value="新規登録"></p>
    </form>
    <p><a href="./index.php">TOPページへ</a></p>
</body>

</html>
