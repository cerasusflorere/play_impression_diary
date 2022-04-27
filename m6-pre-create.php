<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>仮登録 -観劇感想日記</title>
</head>
<body>
  
<?php
    session_start();
    
    // envファイル使用のため
    require '../vendor/autoload.php';
    // .envを使用する
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
    // .envファイルで定義したHOST等を変数に代入
    $DSN = $_ENV["DSN"];
    $USER = $_ENV["USER"];
    $PASS = $_ENV["PASS"];
      

    // DB接続設定
    try {
        $pdo = new PDO($DSN, $USER, $PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    } catch (PDOException $e) {
        $errors['DB_error'] = '接続失敗';
        exit();
    }

    $sql_pre = "CREATE TABLE IF NOT EXISTS pre_user"
         ." ("
         . "id INT AUTO_INCREMENT PRIMARY KEY,"
         . "urltoken varchar(280),"
         . "email varchar(280),"
         . "date DATETIME,"
         . "flag int"
         .");";
    $stmt_pre = $pdo->query($sql_pre);
         
    $sql_user = "CREATE TABLE IF NOT EXISTS user"
         ." ("
         . "id INT AUTO_INCREMENT PRIMARY KEY,"
         . "userid INT,"
         . "email varchar(280),"
         . "username varchar(60),"
         . "password varchar(60),"
         . "status int(1),"
         . "createddate DATETIME,"
         . "updateddate DATETIME"
         .");";
    $stmt_user = $pdo->query($sql_user); 
         
    $sql_password_resets = "CREATE TABLE IF NOT EXISTS password_resets"
         ." ("
         . "id INT AUTO_INCREMENT PRIMARY KEY,"
         . "urltoken varchar(280),"
         . "email varchar(280),"
         . "date DATETIME,"
         . "flag int"
         .");";
    $stmt_password_resets = $pdo->query($sql_password_resets); 
         
    $sql_impression = "CREATE TABLE IF NOT EXISTS impression"
         ." ("
         . "id INT AUTO_INCREMENT PRIMARY KEY,"
         . "userid INT,"
         . "performance varchar(280),"
         . "theatrical_company varchar(100),"
         . "date DATE,"
         . "open_time TIME,"
         . "close_time TIME,"
         . "theater varchar(100),"
         . "seat varchar(50),"
         . "first_date DATE,"
         . "final_date DATE,"
         . "organizer varchar(100),"
         . "director varchar(100),"
         . "author varchar(100),"
         . "dance varchar(100),"
         . "music varchar(100),"
         . "lyrics varchar(100),"
         . "costume varchar(100),"
         . "illumination varchar(100),"
         . "property varchar(100),"
         . "scenario TEXT,"
         . "impression_all TEXT,"
         . "player_1 varchar(100),"
         . "player_2 varchar(100),"
         . "player_3 varchar(100),"
         . "player_4 varchar(100),"
         . "player_5 varchar(100),"
         . "player_6 varchar(100),"
         . "player_7 varchar(100),"
         . "player_8 varchar(100),"
         . "player_9 varchar(100),"
         . "player_10 varchar(100),"
         . "player_11 varchar(100),"
         . "player_12 varchar(100),"
         . "player_13 varchar(100),"
         . "player_14 varchar(100),"
         . "player_15 varchar(100),"
         . "player_16 varchar(100),"
         . "player_17 varchar(100),"
         . "player_18 varchar(100),"
         . "player_19 varchar(100),"
         . "player_20 varchar(100),"
         . "player_21 varchar(100),"
         . "player_22 varchar(100),"
         . "player_23 varchar(100),"
         . "player_24 varchar(100),"
         . "player_25 varchar(100),"
         . "player_26 varchar(100),"
         . "player_27 varchar(100),"
         . "player_28 varchar(100),"
         . "player_29 varchar(100),"
         . "player_30 varchar(100),"
         . "player_31 varchar(100),"
         . "player_32 varchar(100),"
         . "player_33 varchar(100),"
         . "player_34 varchar(100),"
         . "player_35 varchar(100),"
         . "player_36 varchar(100),"
         . "player_37 varchar(100),"
         . "player_38 varchar(100),"
         . "player_39 varchar(100),"
         . "player_40 varchar(100),"
         . "player_41 varchar(100),"
         . "player_42 varchar(100),"
         . "player_43 varchar(100),"
         . "player_44 varchar(100),"
         . "player_45 varchar(100),"
         . "player_46 varchar(100),"
         . "player_47 varchar(100),"
         . "player_48 varchar(100),"
         . "player_49 varchar(100),"
         . "player_50 varchar(100),"
         . "player_impression_1 varchar(100),"
         . "player_impression_2 varchar(100),"
         . "player_impression_3 varchar(100),"
         . "player_impression_4 varchar(100),"
         . "player_impression_5 varchar(100),"
         . "player_impression_6 varchar(100),"
         . "player_impression_7 varchar(100),"
         . "player_impression_8 varchar(100),"
         . "player_impression_9 varchar(100),"
         . "player_impression_10 varchar(100),"
         . "player_impression_11 varchar(100),"
         . "player_impression_12 varchar(100),"
         . "player_impression_13 varchar(100),"
         . "player_impression_14 varchar(100),"
         . "player_impression_15 varchar(100),"
         . "player_impression_16 varchar(100),"
         . "player_impression_17 varchar(100),"
         . "player_impression_18 varchar(100),"
         . "player_impression_19 varchar(100),"
         . "player_impression_20 varchar(100),"
         . "player_impression_21 varchar(100),"
         . "player_impression_22 varchar(100),"
         . "player_impression_23 varchar(100),"
         . "player_impression_24 varchar(100),"
         . "player_impression_25 varchar(100),"
         . "player_impression_26 varchar(100),"
         . "player_impression_27 varchar(100),"
         . "player_impression_28 varchar(100),"
         . "player_impression_29 varchar(100),"
         . "player_impression_30 varchar(100),"
         . "player_impression_31 varchar(100),"
         . "player_impression_32 varchar(100),"
         . "player_impression_33 varchar(100),"
         . "player_impression_34 varchar(100),"
         . "player_impression_35 varchar(100),"
         . "player_impression_36 varchar(100),"
         . "player_impression_37 varchar(100),"
         . "player_impression_38 varchar(100),"
         . "player_impression_39 varchar(100),"
         . "player_impression_40 varchar(100),"
         . "player_impression_41 varchar(100),"
         . "player_impression_42 varchar(100),"
         . "player_impression_43 varchar(100),"
         . "player_impression_44 varchar(100),"
         . "player_impression_45 varchar(100),"
         . "player_impression_46 varchar(100),"
         . "player_impression_47 varchar(100),"
         . "player_impression_48 varchar(100),"
         . "player_impression_49 varchar(100),"
         . "player_impression_50 varchar(100),"
         . "impression_player_1 TEXT,"
         . "impression_player_2 TEXT,"
         . "impression_player_3 TEXT,"
         . "impression_player_4 TEXT,"
         . "impression_player_5 TEXT,"
         . "impression_player_6 TEXT,"
         . "impression_player_7 TEXT,"
         . "impression_player_8 TEXT,"
         . "impression_player_9 TEXT,"
         . "impression_player_10 TEXT,"
         . "impression_player_11 TEXT,"
         . "impression_player_12 TEXT,"
         . "impression_player_13 TEXT,"
         . "impression_player_14 TEXT,"
         . "impression_player_15 TEXT,"
         . "impression_player_16 TEXT,"
         . "impression_player_17 TEXT,"
         . "impression_player_18 TEXT,"
         . "impression_player_19 TEXT,"
         . "impression_player_20 TEXT,"
         . "impression_player_21 TEXT,"
         . "impression_player_22 TEXT,"
         . "impression_player_23 TEXT,"
         . "impression_player_24 TEXT,"
         . "impression_player_25 TEXT,"
         . "impression_player_26 TEXT,"
         . "impression_player_27 TEXT,"
         . "impression_player_28 TEXT,"
         . "impression_player_29 TEXT,"
         . "impression_player_30 TEXT,"
         . "impression_player_31 TEXT,"
         . "impression_player_32 TEXT,"
         . "impression_player_33 TEXT,"
         . "impression_player_34 TEXT,"
         . "impression_player_35 TEXT,"
         . "impression_player_36 TEXT,"
         . "impression_player_37 TEXT,"
         . "impression_player_38 TEXT,"
         . "impression_player_39 TEXT,"
         . "impression_player_40 TEXT,"
         . "impression_player_41 TEXT,"
         . "impression_player_42 TEXT,"
         . "impression_player_43 TEXT,"
         . "impression_player_44 TEXT,"
         . "impression_player_45 TEXT,"
         . "impression_player_46 TEXT,"
         . "impression_player_47 TEXT,"
         . "impression_player_48 TEXT,"
         . "impression_player_49 TEXT,"
         . "impression_player_50 TEXT,"
         . "scene_impression_1 TEXT,"
         . "scene_impression_2 TEXT,"
         . "scene_impression_3 TEXT,"
         . "scene_impression_4 TEXT,"
         . "scene_impression_5 TEXT,"
         . "scene_impression_6 TEXT,"
         . "scene_impression_7 TEXT,"
         . "scene_impression_8 TEXT,"
         . "scene_impression_9 TEXT,"
         . "scene_impression_10 TEXT,"
         . "scene_impression_11 TEXT,"
         . "scene_impression_12 TEXT,"
         . "scene_impression_13 TEXT,"
         . "scene_impression_14 TEXT,"
         . "scene_impression_15 TEXT,"
         . "scene_impression_16 TEXT,"
         . "scene_impression_17 TEXT,"
         . "scene_impression_18 TEXT,"
         . "scene_impression_19 TEXT,"
         . "scene_impression_20 TEXT,"
         . "scene_impression_21 TEXT,"
         . "scene_impression_22 TEXT,"
         . "scene_impression_23 TEXT,"
         . "scene_impression_24 TEXT,"
         . "scene_impression_25 TEXT,"
         . "scene_impression_26 TEXT,"
         . "scene_impression_27 TEXT,"
         . "scene_impression_28 TEXT,"
         . "scene_impression_29 TEXT,"
         . "scene_impression_30 TEXT,"
         . "scene_impression_31 TEXT,"
         . "scene_impression_32 TEXT,"
         . "scene_impression_33 TEXT,"
         . "scene_impression_34 TEXT,"
         . "scene_impression_35 TEXT,"
         . "scene_impression_36 TEXT,"
         . "scene_impression_37 TEXT,"
         . "scene_impression_38 TEXT,"
         . "scene_impression_39 TEXT,"
         . "scene_impression_40 TEXT,"
         . "scene_impression_41 TEXT,"
         . "scene_impression_42 TEXT,"
         . "scene_impression_43 TEXT,"
         . "scene_impression_44 TEXT,"
         . "scene_impression_45 TEXT,"
         . "scene_impression_46 TEXT,"
         . "scene_impression_47 TEXT,"
         . "scene_impression_48 TEXT,"
         . "scene_impression_49 TEXT,"
         . "scene_impression_50 TEXT,"
         . "impression_scene_1 TEXT,"
         . "impression_scene_2 TEXT,"
         . "impression_scene_3 TEXT,"
         . "impression_scene_4 TEXT,"
         . "impression_scene_5 TEXT,"
         . "impression_scene_6 TEXT,"
         . "impression_scene_7 TEXT,"
         . "impression_scene_8 TEXT,"
         . "impression_scene_9 TEXT,"
         . "impression_scene_10 TEXT,"
         . "impression_scene_11 TEXT,"
         . "impression_scene_12 TEXT,"
         . "impression_scene_13 TEXT,"
         . "impression_scene_14 TEXT,"
         . "impression_scene_15 TEXT,"
         . "impression_scene_16 TEXT,"
         . "impression_scene_17 TEXT,"
         . "impression_scene_18 TEXT,"
         . "impression_scene_19 TEXT,"
         . "impression_scene_20 TEXT,"
         . "impression_scene_21 TEXT,"
         . "impression_scene_22 TEXT,"
         . "impression_scene_23 TEXT,"
         . "impression_scene_24 TEXT,"
         . "impression_scene_25 TEXT,"
         . "impression_scene_26 TEXT,"
         . "impression_scene_27 TEXT,"
         . "impression_scene_28 TEXT,"
         . "impression_scene_29 TEXT,"
         . "impression_scene_30 TEXT,"
         . "impression_scene_31 TEXT,"
         . "impression_scene_32 TEXT,"
         . "impression_scene_33 TEXT,"
         . "impression_scene_34 TEXT,"
         . "impression_scene_35 TEXT,"
         . "impression_scene_36 TEXT,"
         . "impression_scene_37 TEXT,"
         . "impression_scene_38 TEXT,"
         . "impression_scene_39 TEXT,"
         . "impression_scene_40 TEXT,"
         . "impression_scene_41 TEXT,"
         . "impression_scene_42 TEXT,"
         . "impression_scene_43 TEXT,"
         . "impression_scene_44 TEXT,"
         . "impression_scene_45 TEXT,"
         . "impression_scene_46 TEXT,"
         . "impression_scene_47 TEXT,"
         . "impression_scene_48 TEXT,"
         . "impression_scene_49 TEXT,"
         . "impression_scene_50 TEXT,"
         . "impression_final TEXT,"
         . "related_performance_1 INT,"
         . "related_performance_2 INT,"
         . "related_performance_3 INT,"
         . "related_performance_4 INT,"
         . "related_performance_5 INT,"
         . "related_performance_6 INT,"
         . "related_performance_7 INT,"
         . "related_performance_8 INT,"
         . "related_performance_9 INT,"
         . "related_performance_10 INT"
         .");";
    $stmt_impression = $pdo->query($sql_impression);
    
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
                $stmt = $pdo->prepare("SELECT id FROM user WHERE email=:email LIMIT 1");
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
       
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }catch(PDOException $e){
                //トランザクション取り消し
                $pdo -> rollBack();
                $errors['error'] = "もう一度やり直してください。";
                print('Error:'.$e->getMessage());
            }
            
                 
            //user テーブルに同じメールアドレスがある場合、エラー表示
            if(isset($result['id'])){
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
                    $stmtpre = $pdo -> prepare("INSERT  INTO pre_user (urltoken, email, date, flag) VALUES (:urltoken, :email, :date, :flag)");
                    $stmtpre -> bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
                    $stmtpre -> bindParam(':email', $email, PDO::PARAM_STR);
                    $stmtpre -> bindParam(':date', $newdate, PDO::PARAM_STR);
                    $stmtpre -> bindParam(':flag', $flag, PDO::PARAM_INT);
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

<div class='pre-create-area'>
    <table class='pre-create-label'>
        <tr>
            <th colspan='3' class='pre-create-label-th'>
                <div class='pre-create-label-headding'>
                    新規登録票<br>
                    すでに登録済みの方は<a href="m6-login-form.php">こちら</a>
                </div>                
            </th>
        </tr>
    
        <!-- 登録完了画面 -->
        <?php if(isset($_POST['submit']) && count($errors) === 0): ?>
            <tr>
                <td><?=$message?></td>
            </tr>
        <?php endif; ?>
         
        <!-- 登録画面 -->
        <?php if(count($errors) > 0): ?>
            <?php foreach($errors as $value){
                echo "<p class='error'>".$value."</p>";
            } ?>
         
        <?php else: ?>
            <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
                <tr>
                    <td>メールアドレス：</td>
                    <td>
                        <input type="text" name="email" size="50" value="<?php if(!empty($_POST['email'])){echo $_POST['email'];} ?>">
                        <input type="hidden" name="token" value="<?=$token?>">
                    </td>
                    <td>
                        <input type="submit" name="submit" value="送信">
                    </td>                   
                </tr>               
            </form>
        <?php endif; ?>
    </table>
    
</div>

</body>
</html>
