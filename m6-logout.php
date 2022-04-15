<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログアウト</title>
</head>
<body>
    <?php
         //クロスサイトリクエストフォージェリ（CSRF）対策
             $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
             $token = $_SESSION['token'];
         //クリックジャッキング対策
             header('X-FRAME-OPTIONS: SAMEORIGIN'); 
             
         $_SESSION = array();
         @session_destroy();
    ?>
<p>ログアウトしました。</p>
<a href = "m6-login-form.php">ログインへ</a>
</body>
</html>