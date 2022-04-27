<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>ユーザー名通知 -観劇感想日記</title>
</meta>
<body>
<?php
    session_start();
    //クロスサイトリクエストフォージェリ（CSRF）対策
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];
    //クリックジャッキング対策
    header('X-FRAME-OPTIONS: SAMEORIGIN');     
    
    function h($str){
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // envファイル使用のため
    require '../vendor/autoload.php';
    // .envを使用する
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
    // .envファイルで定義したHOST等を変数に代入
    $DSN = $_ENV["DSN"];
    $USER = $_ENV["USER"];
    $PASS = $_ENV["PASS"];

    $errors = [];

    // DB接続設定
    try {
        $pdo = new PDO($DSN, $USER, $PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    } catch (PDOException $e) {
        $errors['DB_error'] = '接続失敗';
        exit();
    }       
         
    if(isset($_POST["submit"])){
        //メールアドレス空欄の場合
        if(empty($_POST["email"])){
            $errors["email"] = "メールアドレスが未入力です。";
        }else{
            //POSTされたデータを変数に入れる
            $email = isset($_POST['email']) ? $_POST['email'] : NULL;
   
            //メールアドレス構文チェック
            if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)){
	            $errors['mail_check'] = "メールアドレスの形式が正しくありません。";
            }
            
            //DB確認
            $sql = $pdo ->  prepare("SELECT * FROM user WHERE email=:email LIMIT 1");
            $sql -> bindParam(':email', $email, PDO::PARAM_STR);
            $sql -> execute();
            $results = $sql->fetch();   
            
            if (count($errors) === 0 && $results['id'] != NULL){
                $url = "http://localhost/play_impression_diary/m6-login-form.php";
                $receivernamearray = explode("@", $email);
                $receivername = $receivernamearray[0];
                $_SESSION['email'] = $email;
                     
                // データベースに登録する
                try{
                         
                    $_SESSION['body'] = $receivername."様<br>ご利用くださりありがとうございます。<br>あなたのユーザ名は<br>".$results['username']."<br>です。<br>ログインはこちら<br>".$url;
                      
                    require_once 'phpmailer/send_test.php';
                     
                    $message = "メールをお送りしました。ユーザー名をご確認ください。";     
                         
                    //データベースの接続切断
                    $stmtpre = NULL;
             
                    //セッション変数をすべて解除
                    $_SESSION = array();
            
                    //セッションクッキーの削除
                    if (isset($_COOKIE["PHPSESSID"])) {
			            setcookie("PHPSESSID", '', time() - 1800, '/');
                    }
		     
                    //セッションを破棄する
		            session_destroy();
            }catch (PDOException $e){
                print('Error:'.$e->getMessage());
                die();
            }  
        }
        }
    }
?>
    
 <h1>ユーザー名を登録されているメールアドレス宛に送信致します。</h1>
 <p>すでにご登録済みの方は<a href="m6-login-form.php">こちら</a></p>
    
     <!-- 登録完了画面 -->
         <?php if(isset($_POST['submit']) && count($errors) === 0): ?>
             <p><?=$message?></p>
         <?php endif; ?>
         
     <!-- 登録画面 -->
         <?php if(count($errors) > 0): ?>
             <?php
                 foreach($errors as $value){
                 echo "<p class='error'>".$value."</p>";
             }
             ?>
         <?php endif; ?>
         <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
             <p>メールアドレス：<input type="text" name="email" size="50" value="<?php if(!empty($_POST['email'])){echo $_POST['email'];} ?>"></p> 
             <input type="hidden" name="token" value="<?=$token?>">
             <input type="submit" name="submit" value="送信">
         </form>
</body>
</html>