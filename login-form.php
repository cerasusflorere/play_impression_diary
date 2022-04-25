<!DOCTYPE html>
<html lang="ja">
 <head>
 <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>観劇感想日記</title>
 </head>
 
<?php
    session_start();
    //クロスサイトリクエストフォージェリ（CSRF）対策
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];
    //クリックジャッキング対策
    header('X-FRAME-OPTIONS: SAMEORIGIN'); 
             
    // envファイル使用のため
    require '../vendor/autoload.php';
    // .envを使用する
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
    // .envファイルで定義したHOST等を変数に代入
    $HOST = $_ENV["HOST"];
    $USER = $_ENV["USER"];
    $PASS = $_ENV["PASS"];
    $DB = $_ENV["DB"];  

    $mysqli = new mysqli($HOST, $USER, $PASS, $DB);
    if($mysqli->connect_error){
        echo $mysqli->connect_error;
        exit();
    } else {
        $mysqli->set_charset("utf8mb4");
    }
         
    // 成功・エラーメッセージの初期化
    $errors = array();
         
    // ログインボタン(btn_login)が押された後の処理
    if(isset($_POST['btn_login'])){
        $username = isset($_POST['username']) ? $_POST['username'] : NULL;
        $password = isset($_POST['password']) ? $_POST['password'] : NULL;
        
        if($username != "" && $password != ""){
            $stmt_login = $mysqli -> prepare("SELECT id, password FROM user WHERE username=? LIMIT 1");
            $stmt_login -> bind_param('s', $username);
            $stmt_login -> execute();  
            $stmt_login_double = $stmt_login;          
            $stmt_login->store_result(); // これ忘れるとnum_rowsは0
            $count_login =  $stmt_login->num_rows;
            
            if($count_login === 1){
                $stmt_login->bind_result($id, $correct_password);    
                // 値を取得します 
                $stmt_login->fetch();
                // 指定したハッシュがパスワードとあっているか
                if(password_verify($password, $correct_password)){
                    // データベースの値をセッションに保存
                    $_SESSION['userid'] = $id;
                    $_SESSION['username'] = $username;
                         
                    header("Location:m6-indivisual-home.php");
                    exit();
                }else{
                    $errors['confirm_password'] = "パスワードが一致しません。";
                }
            }else{
                $errors['confirm_username'] = "そのユーザ名は登録されていません。";
            }
        }else{
            if($username === ""){
                $errors['username'] = "ユーザ名が未入力です。";
            }
            if($password === ""){
                $errors['password'] = "パスワードが未入力です。";
            }
        }
    }
?>

<body>
    <div class='login-area'>
        <div class='login-page'>
           <div class='login-err-area'>
                <?php if(count($errors) > 0):?>
                    <?php foreach($errors as $value){
                        echo "<p class='login-err'>".$value."</p>";
                    } ?>
                <?php endif; ?>
            </div>

            <div class='app-name-area'>
                <img src='app-name.png' class='app-name' alt=<?php echo '観劇感想日記' ?>>
            </div>            
                
            <form action="" method="post" class='login-form'>
                <p class='login-input-area login-input-area-first'>
                    <i class="icon fas fa-user-circle fa-fw"></i><input type="text" name="username" class='login-input'>
                </p>
                <p class='login-input-area login-input-area-second'>
                    <i class="icon fas fa-key fa-fw"></i><input type="password" name="password" class='login-input'>
                </p>
                <input type="submit" name="btn_login" value='<?php if(count($errors) == 0 && isset($_POST['btn_login'])){echo "&#xf3c1;";} else {echo "&#xf023;";} ?>' class='login-button icon fas'>
            </form>
            <div class='login-other-area'>
                <a href="m6-pre-create.php" target="_blank" class='login-other'>初めての方はこちら</a><br>
                <a href="m6-foget-username-form.php" class='login-other'>ユーザー名をお忘れの方はこちら</a><br>
                <a href="m6-forget-password-form.php" class='login-other'>パスワードをお忘れの方はこちら</a>
            </div>
           
        </div>
    </div>
</body>
</html>
