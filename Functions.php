<?php

require_once 'define.php';

class Functions
{
    /**
     * エスケープ処理
     * @param string $string
     * @return string 
     */
    public function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * CSRF対策 トークンを生成
     * @param void
     * @return string $csrf_token
     */
    public function setToken()
    {
        $csrf_token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrf_token;

        return $csrf_token;
    }

    /**
     * CSRF対策 トークンをチェック
     * @param string $token
     * @param string $session_token
     * @return void
     */
    public function checkToken($token, $session_token)
    {
        return ($token === $session_token);
    }

    /**
     * データベースへの接続
     * @param void
     * @return
     */
    public function connectDb()
    {
        $dsn = 'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST . ';charset=utf8';

        try {

            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            return $pdo;
        } catch (PDOException $e) {

            echo '接続エラー' . $e->getMessage();
        }
    }

    /**
     * データベースにユーザー情報を登録
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function createUser($name, $email, $password)
    {
        $result = false;

        $sql = 'INSERT INTO users (name, email, password) VALUES (?, ?, ?)';

        try {

            $stmt = $this->connectDb()->prepare($sql);
            $stmt->bindValue(1, $name);
            $stmt->bindValue(2, $email);
            $stmt->bindValue(3, password_hash($password, PASSWORD_DEFAULT));
            $result = $stmt->execute();

            return $result;
        } catch (\Exception $e) {

            return $result;
        }
    }

    /**
     * ログイン処理を行う
     * @param string $email
     * @param string $password
     * @return bool $result
     */
    public function login($email, $password)
    {
        $result = false;
        $user = $this->getUserByEmail($email);

        if (!$user) {
            $_SESSION['msg'] = '※メールアドレスまたはパスワードが一致しませんでした';
            return $result;
        }

        if (password_verify($password, $user['password'])) {

            session_regenerate_id(true);
            $_SESSION['login_user'] = $user;
            $result = true;
            return $result;
        } else {

            $_SESSION['msg'] = '※メールアドレスまたはパスワードが一致しませんでした';
            return $result;
        }
    }

    /**
     * データベースから該当するユーザーを取得
     * @param string $email
     * @return array|bool $user|false
     */
    public function getUserByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = ?';

        try {
            $stmt = $this->connectDb()->prepare($sql);
            $stmt->bindValue(1, $email);
            $stmt->execute();
            $user = $stmt->fetch();

            return $user;
        } catch (\Exception $e) {

            return false;
        }
    }

    /**
     * ログインチェック
     * @param void
     * @return bool $result
     */
    public function checkLogin()
    {
        if (isset($_SESSION['login_user']) && $_SESSION['login_user']['id'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ログアウト処理
     * @param  void
     * @return void
     */
    public function logout()
    {
        $_SESSSION = [];
        session_destroy();
    }

    /**
     * 画像ファイルデータをデータベースに保存
     * @param string $user_id
     * @param string $file_name
     * @param string $save_path
     * @param string $caption
     * @return void
     */
    public function setUserFile($user_id, $file_name, $save_path, $caption)
    {
        $result = false;

        $sql = "INSERT INTO picture (user_id, file_name, file_path, caption) VALUES (?, ?, ?, ?)";

        try {

            $stmt = $this->connectDb()->prepare($sql);
            $stmt->bindValue(1, $user_id);
            $stmt->bindValue(2, $file_name);
            $stmt->bindValue(3, $save_path);
            $stmt->bindValue(4, $caption);
            $result = $stmt->execute();

            return $result;
        } catch (\Exception $e) {

            echo 'エラー' . $e->getMessage();
            return $result;
        }
    }

    /**
     * ユーザーが保存したファイル情報を全て取得
     * @param int $user_id
     * @return array|bool $file_data|false
     */
    public function getUserFile($user_id)
    {
        $sql = "SELECT * FROM picture WHERE user_id = ?";

        try {

            $stmt = $this->connectDb()->prepare($sql);
            $stmt->bindValue(1, $user_id);
            $stmt->execute();

            $file_data = $stmt->fetchAll();

            return $file_data;
        } catch (\Exception $e) {
            echo 'エラー' . $e->getMessage();
            return false;
        }
    }

    /**
     * 指定されたファイルをダウンロード
     * @param string $file_path
     * @return void
     */
    public function downloadUserFile($file_path)
    {
        $file_ext = (new finfo(FILEINFO_MIME_TYPE))->file($file_path);
        if (!preg_match('/\A\S+?\/\S+/', $file_ext)) {
            $file_ext = 'application/octet-stream';
        }
        // Content-Type
        header('Content-Type: ' . $file_ext);

        // ウェブブラウザが独自にMIMEタイプを判断する処理を抑止
        header('X-Content-Type-Options: nosniff');

        // ダウンロードファイルのサイズ
        header('Content-Length: ' . filesize($file_path));

        // ダウンロード時のファイル名
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');

        // keep-aliveを無効化
        header('Connection: close');

        // readfile()の前に出力バッファリングを無効化
        while (ob_get_level()) {
            ob_end_clean();
        }
        readfile($file_path);
    }

    /**
     * 削除依頼を受けたデータをデータベースから削除
     * @param int $id
     * @return bool $result
     */
    public function deleteUserFileData($id)
    {
        $result = false;
        $sql = "DELETE FROM picture WHERE id = ?";

        try {

            $stmt = $this->connectDb()->prepare($sql);
            $stmt->bindValue(1, $id);
            $result = $stmt->execute();

            return $result;
        } catch (\Exception $e) {

            echo 'エラー' . $e->getMessage();
            return $result;
        }
    }

    /**
     * 削除依頼を受けた画像ファイルを削除
     * @param string $file_path
     * @return bool $result
     */
    public function deleteUserFile($file_path)
    {
        $result = unlink($file_path);
        return $result;
    }
}
