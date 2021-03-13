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

// 過去にアップロードされたファイルがあれば取得
$userFiles = $functions->getUserFile($login_user['id']);

// 削除依頼の結果のメッセージがあれば変数$delに入れる
$msg = $_SESSION['dl_del_msg'];

// 削除結果を表す変数の定義 & セッションの初期化
$dl_del_msg = null;
$_SESSION['dl_del_msg'] = null;

// POSTにて送信された情報がある場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //保存か削除かを見極め
    $download = filter_input(INPUT_POST, 'download');
    $delete = filter_input(INPUT_POST, 'delete');

    // 保存する場合の処理
    if($download === 'download') {

        // ダウンロードするファイルのファイル名・ファイルパスの取得
        $download_name = filter_input(INPUT_POST, 'download_name');
        $download_path = filter_input(INPUT_POST, 'download_path');

        // ファイルが存在しない場合は処理を中止
        if(!is_readable($download_path)) {
            $dl_del_msg = 'ファイル名「' . $download_name . '」は存在しないかエラーが発生したため、ダウンロードできませんでした';
        } else {
            // ファイルがあればダウンロード手続き
            $functions->downloadUserFile($download_path);
            return;
        }
    }

    // 削除する場合の処理
    if($delete === 'delete') {
    
        // 削除依頼を受けた画像ファイルのID・ファイル名・ファイルのパスを取得
        $delete_id = filter_input(INPUT_POST, 'delete_id');
        $delete_name = filter_input(INPUT_POST, 'delete_name');
        $delete_path = filter_input(INPUT_POST, 'delete_path');
        
        // データベースから画像情報を削除
        $infoHasDeleted = $functions->deleteUserFileData($delete_id);
    
        // 画像を保存しているフォルダから画像ファイルを削除
        if (file_exists($delete_path)) {
            $fileHasDeleted = $functions->deleteUserFile($delete_path);
            if ($infoHasDeleted && $fileHasDeleted) {
                $dl_del_msg = '画像ファイル「 ' . $delete_name . ' 」は Picture Drive から削除されました';
            } else {
                $dl_del_msg = '画像ファイル「 ' . $delete_name . ' 」の削除に失敗しました';
            }
        
        }
    }
    // 再読み込みするためにリダイレクト
    $_SESSION['dl_del_msg'] = $dl_del_msg;
    header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/mypage.php');
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
    <p><a href="./upload_form.php">ファイルをアップロードするにはこちら</a></p>

    <p><?php echo $msg; ?></p>
    <p><?php echo '画像ファイル一覧'; ?></p>

    <?php if (count($userFiles) === 0) : ?>
        <p><?php echo '表示する画像がありません 早速画像をアップロードしてみよう!' ?></p>
    <?php endif; ?>

    <?php foreach ($userFiles as $userFile) : ?>
        <img src="<?php echo $functions->e("{$userFile['file_path']}"); ?>" alt="">
        <p><?php echo "{$userFile['file_name']}"; ?></p>
        <p><?php echo "{$userFile['caption']}"; ?></p>
        <form action="" method="POST">
            <input type="hidden" name="download" value="download">
            <input type="hidden" name="download_name" value="<?php echo $functions->e("{$userFile['file_name']}") ?>">
            <input type="hidden" name="download_path" value="<?php echo $functions->e("{$userFile['file_path']}") ?>">
            <input type="submit" value="ダウンロード">
        </form>
        <form action="" method="POST">
            <input type="hidden" name="delete" value="delete">
            <input type="hidden" name="delete_id" value="<?php echo $functions->e("{$userFile['id']}") ?>">
            <input type="hidden" name="delete_name" value="<?php echo $functions->e("{$userFile['file_name']}") ?>">
            <input type="hidden" name="delete_path" value="<?php echo $functions->e("{$userFile['file_path']}") ?>">
            <input type="submit" value="削除">
        </form>
    <?php endforeach; ?>
</body>

</html>
