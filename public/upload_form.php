<?php

// セッション開始
session_start();

// Functionクラスをロード
require_once '../Functions.php';
$functions = new Functions();

// ログイン前であればindex.phpへ遷移する
$result = $functions->checkLogin();
if (!$result) {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php');
    return;
}

// ログイン情報があればセッション情報を変数$login_userに入れる
$login_user = $_SESSION['login_user'];

// エラー変数の定義
$err_msg = [];
$save_result = null;

// アップロードしたファイルのバリデーション
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 各々の変数定義
    $userFile = $_FILES['img'];
    $userFile_name = basename($userFile['name']);
    $tmp_path = $userFile['tmp_name'];
    $userFile_err = $userFile['error'];
    $userFile_size = $userFile['size'];
    $upload_dir = '../images/';
    $save_path = $upload_dir . $userFile_name;

    // キャプションの取得
    $caption = filter_input(INPUT_POST, 'caption', FILTER_SANITIZE_SPECIAL_CHARS);

    // 140文字以下かを判定
    if (mb_strlen($caption) > 140) {
        array_push($err_msg, 'キャプションは140文字以内にして下さい');
    }

    // ファイルのサイズが1MB以下かを判定
    if ($userFile_size > 1048576 || $userFile_err == 2) {
        array_push($err_msg, 'ファイルの大きさは1MB以下にして下さい');
    }

    // 拡張子は画像形式かを判定
    $allow_ext = array('jpeg', 'jpg', 'png');
    $userFile_ext = pathinfo($userFile_name, PATHINFO_EXTENSION);
    if (!in_array(strtolower($userFile_ext), $allow_ext)) {
        array_push($err_msg, '画像形式のファイルをアップロードして下さい');
    }

    if (count($err_msg) === 0) {
        // ファイルがそもそも存在するかを判定
        if (is_uploaded_file($tmp_path)) {
            if (move_uploaded_file($tmp_path, $save_path)) {
                // DBに保存
                $result = $functions->setUserFile($login_user['id'], $userFile_name, $save_path, $caption);
                if ($result) {
                    $save_result = '画像ファイル「' . $userFile_name . '」を Picture Drive 内にアップロードしました';
                } else {
                    $save_result = 'Picture Drive への画像の保存に失敗しました';
                }
            } else {
                $save_result = '画像ファイルのアップロードに失敗しました';
            }
        } else {
            $save_result = '画像ファイルが選択されていません';
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
    <h2>マイページ</h2>
    <p><a href="./logout.php">ログアウト</a></p>
    <p>ようこそ, <?php echo $functions->e($login_user['name']); ?>さん!</p>
    <p>以下のフォームにてファイルを指定することでアップロードが可能です</p>
    <p>※ファイルの最大サイズ: 1MB</p>

    <?php if ($save_result) : ?>
        <p><?php echo $save_result; ?></p>
    <?php endif; ?>

    <?php if (count($err_msg) > 0) : ?>
        <ul>
            <?php foreach ($err_msg as $value) : ?>
                <li><?php echo $value; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form enctype="multipart/form-data" action="" method="POST">
        <p><input type="hidden" name="MAX_FILE_SIZE" value="1048576"></p>
        <p><input name="img" type="file" accept="image/*"></p>
        <p><textarea name="caption" placeholder="キャプション(140文字以下)" id="caption"></textarea></p>
        <p><input type="submit" value="アップロード"></p>
    </form>

    <p><a href="./mypage.php">画像一覧へ戻る</a></p>

</body>

</html>
