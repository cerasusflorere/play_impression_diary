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
    $date = date('Y年m月d日');

    // DB接続設定
    try {
        $pdo = new PDO($DSN, $USER, $PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    } catch (PDOException $e) {
        $errors['DB_error'] = '接続失敗';
        exit();
    }
         
    if(isset($_POST["submit"])){
        //メールアドレスとユーザー名空欄の場合
        if(empty($_POST["email"]) || empty($_POST["username"])){
            if(empty($_POST["email"])){
                $errors["email"] = "メールアドレスが未入力です。";
            }
            if(empty($_POST["username"])){
                $errors["username"] = "ユーザー名が未入力です。";
            }               
        }else{
            //POSTされたデータを変数に入れる
            $email = isset($_POST['email']) ? $_POST['email'] : NULL;
            $username = isset($_POST['username']) ? $_POST['username'] : NULL;
   
            //メールアドレス構文チェック
            if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)){
	            $errors['mail_check'] = "メールアドレスの形式が正しくありません。";
            }

            //DB確認 
            try{
                $stmt = $pdo->prepare("SELECT id FROM user WHERE email=:email AND username=:username LIMIT 1");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
       
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }catch(PDOException $e){
                //トランザクション取り消し
                $pdo -> rollBack();
                $errors['error'] = "もう一度やり直してください。";
                print('Error:'.$e->getMessage());
            }
                 
            // password_resetsテーブルにインサート
            if (count($errors) === 0 && !empty($result['id'])){
                $urltoken = hash('sha256',uniqid(rand(),1));
                $url = "http://localhost/play_impression_diary/m6-forget-password.php?urltoken=".$urltoken;
                $receivernames = explode("@", $email);
                $receivername = $receivernames[0];
                $_SESSION['email'] = $email;
                     
                // データベースに登録する
                try{
                    $newdate = date('Y-m-d H:i:s');
                    $flag = 0;
                    $stmt_password_resete = $pdo -> prepare("INSERT  INTO password_resets (userid, urltoken, email, date, flag) VALUES (:userid, :urltoken, :email, :date, :flag)");
                    $stmt_password_resete -> bindParam(':userid', $result['id'], PDO::PARAM_INT); 
                    $stmt_password_resete -> bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
                    $stmt_password_resete -> bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt_password_resete -> bindParam(':date', $newdate, PDO::PARAM_STR);
                    $stmt_password_resete -> bindParam(':flag', $flag, PDO::PARAM_INT);                    
                    $stmt_password_resete -> execute();
                         
                    $_SESSION['body'] = $receivername."様<br>ご利用いただきありがとうございます。<br>下記URLにて24時間以内にパスワードを再設定してください。<br>".$url;
                         
                    require_once 'phpmailer/send_test.php';
                      
                    $message = "メールをお送りしました。24時間以内にメールに記載されたURLからご変更下さい。";
                }catch (PDOException $e){
                    print('Error:'.$e->getMessage());
                    die();
                }  
            }
        }
    }
?>

<div class='area'>
    <div class='pre-create-label'>
        <div class='pre-create-label-headding-area'>
            <div class='pre-create-label-headding pre-create-label-headding-main password-resets-headding-main'>
                パスワードリセット伝票
            </div>
            <div class='pre-create-label-headding-message'>
                すでに登録済みの方は<a href="m6-login-form.php">こちら</a>
            </div>
            <div class='pre-create-label-headding-name-date-right-area'>
                <div>
                    <div class='pre-create-label-headding pre-create-label-headding-name'>
                        ゲスト 様
                    </div>
                    <div class='pre-create-label-headding-date'>
                        <?=$date?>
                    </div> 
                </div>
              
                <div class='pre-create-label-headding-right'>
                    <img src='app-name.png' class='pre-create-app-name' alt=<?='観劇感想日記'?>>
                </div>      
            </div>
        </div>
    
        <table>
            <!-- 登録完了画面 -->
            <?php if(isset($_POST['submit']) && count($errors) === 0): ?>
            <thead>
                <tr>
                    <th></th>
                    <th>メッセージ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td><?=$message?></td>
                    <td></td>
                </tr>
            </tbody>

            <?php endif; ?>
         
            <!-- 登録画面 -->
            <?php if(count($errors) > 0): ?>
            <thead>
                <tr>
                    <th></th>
                    <th>エラーメッセージ</th>
                    <th></th>
                </tr>                
            </thead>
            <tbody>
                <?php foreach($errors as $value){ ?>
                <tr>
                    <td></td>
                    <td><?=$value?></td>
                    <td></td>
                </tr>
                <?php } ?>
            </tbody>
            <?php endif; ?>

            <thead>
                <tr>
                    <th>項目</th>
                    <th>記入欄</th>
                    <th>ボタン</th>
                </tr>
            </thead>

            <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
                <tbody>
                    <tr>
                        <td>メールアドレス</td>
                        <td>
                            <input type="text" name="email" value="<?php if(!empty($_POST['email'])){echo h($_POST['email']);} ?>" class='pre-create-label-text' required>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>ユーザー名</td>
                        <td>
                        <input type="text" name="username" value="<?php if(!empty($_POST['username'])){echo h($_POST['username']);} ?>" class='pre-create-label-text' required>
                        </td>
                        <td>
                            <input type="hidden" name="token" value="<?=$token?>">
                            <input type="submit" name="submit" value="送信 &#xf105;" class='pre-create-label-botton fas'>
                        </td>
                    </tr>
                </tbody>
                
            </form>
        </table>
    </div>
</div>

</body>
</html>
