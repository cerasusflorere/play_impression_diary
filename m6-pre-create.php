<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>仮登録画面</title>
</head>
  
<?php
         
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
         
    session_start();
         
    //エラーメッセージの初期化
    $errors = array();
         
    if(isset($_POST["submit"])){
        //メールアドレス空欄の場合
        if(empty($_POST["email"])){
            $errors['email'] = "メールアドレスが未入力です。";
        }else{
            //POSTされたデータを変数に入れる
            $email = isset($_POST['email']) ? $_POST['email'] : NULL;
            
            //メールアドレス構文チェック
            if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)){
	            $errors['mail_check'] = "メールアドレスの形式が正しくありません。";
            }
                 
            //DB確認 
            try{
                $result = $mysqli->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
                $result -> bind_param('s', $email);
       
                $result -> execute();
                foreach($result as $row){
                    $id = $row;
                }
                $result->close();
            }catch(PDOException $e){
                //トランザクション取り消し
                $pdo -> rollBack();
                $errors['error'] = "もう一度やり直してください。";
                print('Error:'.$e->getMessage());
            }
            
                 
            //user テーブルに同じメールアドレスがある場合、エラー表示
            if(isset($id)){
			    $errors['user_check'] = "このメールアドレスはすでに利用されております。";
            }
                 
            //エラーがない場合、pre_userテーブルにインサート
            if (count($errors) === 0){
                $urltoken = hash('sha256',uniqid(rand(),1));
                $url = "http://localhost/play_impression_diary/m6-create.php?urltoken=".$urltoken;
                $receivernamearray = explode("@", $email);
                $receivername = $receivernamearray[0];
                $_SESSION['email'] = $email;
                     
                // データベースに登録する
                try{
                    $newdate = date('Y-m-d H:i:s');
                    $flag = 0;
                    $stmtpre = $mysqli -> prepare("INSERT  INTO pre_user (urltoken, email, date, flag) VALUES (?, ?, ?, ?)");
                    $stmtpre -> bind_param('sssi', $urltoken, $email, $newdate, $flag);
                    $stmtpre -> execute();
                         
                    $_SESSION['body'] = $receivername."様<br>この度はご利用くださりありがとうございます。<br>下記URLにて24時間以内に本登録をお済ませください。<br>".$url;
                      
                    require_once 'phpmailer/send_test.php';
                     
                    $message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。";     
                         
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

 <h1>ようこそ、新規登録をお願いします。</h1>
 <p>すでに登録済みの方は<a href="m6-login-form.php">こちら</a></p>
    
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