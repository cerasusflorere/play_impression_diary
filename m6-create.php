<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>本登録 -観劇感想日記</title>
</head>

<?php
    session_start();
    //クロスサイトリクエストフォージェリ（CSRF）対策
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];
    //クリックジャッキング対策
    header('X-FRAME-OPTIONS: SAMEORIGIN');

    function h($str) {
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
      

    // DB接続設定
    try {
        $pdo = new PDO($DSN, $USER, $PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    } catch (PDOException $e) {
        $errors['DB_error'] = '接続失敗';
        exit();
    }
         
    // 成功・エラーメッセージの初期化
    $errors = array();
        
    if(empty($_GET)){
        header("Location:m6-pre-create.php");
        exit();
    }else{ 
        $urltoken = isset($_GET["urltoken"]) ? $_GET["urltoken"] : NULL;
      
        //メール入力判定
        if ($urltoken === ''){
            $errors['urltoken'] = "トークンがありません。";
        }else{
            $flag = 0;
            $date = date('Y-m-d H:i:s',strtotime("- 24hours"));
            //flagが0の未登録者 or 仮登録日から24時間以内  
            $stmt_confirm = $pdo->prepare("SELECT email FROM pre_user WHERE urltoken=:urltoken AND flag=:flag  AND date>=:date LIMIT 1");
            $stmt_confirm->bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
            $stmt_confirm->bindValue(':flag', $flag, PDO::PARAM_INT);
            $stmt_confirm->bindParam(':date', $date, PDO::PARAM_STR);
		    $stmt_confirm->execute();
            $count_confirm = $stmt_confirm -> rowCount();
			 
	    	//24時間以内に仮登録され、本登録されていないトークンの場合
		    if($count_confirm == 1){
                $result = $stmt_confirm -> fetch();
			    $_SESSION['email'] = $result["email"];
			}else{
			    $errors['urltoken_timeover'] = "このURLはご利用できません。<br>有効期限が過ぎたかURLが間違えている可能性がございます。<br>もう一度登録をやりなおして下さい。";
		   	}
        }
    }    
    /**
     * 確認する(btn_confirm)押した後の処理
    */
      
    if(isset($_POST['btn_confirm'])){
        if(empty($_POST)){
            header("Location: m6-pre-create.php");
            exit();
        }else{
            //POSTされたデータを変数に入れる
            $username = isset($_POST['username']) ? $_POST['username']:NULL;
            $password = isset($_POST['password']) ? $_POST['password']:NULL;
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password']:NULL;
  
            //アカウント入力判定
            //パスワード入力判定
            if($username == "" || $password == "" || $confirm_password == ""){
                if($username == ""){
                $errors['username'] = "ユーザー名が入力されていません。";
                }else{
                    // ユーザー名が使用可能か
                    $stmt_confirm_username = $pdo->prepare("SELECT username FROM user WHERE urltoken=:urltoken LIMIT 1");
                    $stmt_confirm_username -> bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
		            $stmt_confirm_username -> execute();
                    $count_confirm_username = $stmt_confirm -> rowCount();
			 
	    	        //ユーザー名があった
		            if($count_confirm_username == 1){
                        $errors['username'] = "このユーザー名は既に使用されています。別の名前にしてください。";
			        }
                }
                if(($password === "") || ($confirm_password === "")){
                    if($password === ""){
                        $errors['password'] = "パスワードが入力されていません。";
                    }
                    if($confirm_password === ""){
                        $errors['confirm_password'] = "パスワード（確認）が入力されていません。";
                    }
                }
            }else{
                if(!empty($errors['username'])){
                    unset($errors['username']);
                }
                if(!empty($errors['password'])){
                    unset($errors['password']);
                }
                if(!empty($errors['confirm_password'])){
                    unset($errors['confirm_password']);
                }
            }
            if($username != ""){
                $_SESSION['username'] = $username;
            }
            if(count($errors) === 0 && ($password != "") && ($confirm_password != "")){
                if($password == $confirm_password){
                    $password_hide = str_repeat('*',strlen($password));
                    $_SESSION['password'] = $password;
                    $_SESSION['password_hide'] = $password_hide;
                }else{
                    $errors['password_confirm'] = "パスワードが一致しません。";
                }
            }
        }
    }
         
    /**
     * 登録(btn_submit)を押した後の処理
    */
    if(isset($_POST['btn_submit'])){
        //パスワードのハッシュ化
        $password_hash = password_hash($_SESSION['password'], PASSWORD_DEFAULT);
             
        //ここでデータベースに登録する
        try{
            $newdate = date('Y-m-d H:i:s');
            $newstatus = 1;
            $stmt_registerate = $pdo -> prepare("INSERT INTO user (email, username, password, status, createddate, updateddate) VALUES (:email, :username, :password, :status, :createddate, :updateddate)");
            $stmt_registerate -> bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
            $stmt_registerate -> bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
            $stmt_registerate -> bindParam(':password', $password_hash, PDO::PARAM_STR);
            $stmt_registerate -> bindValue(':status', 1, PDO::PARAM_INT);
            $stmt_registerate -> bindParam(':createddate', $newdate, PDO::PARAM_STR);
            $stmt_registerate -> bindParam(':updateddate', $newdate, PDO::PARAM_STR);
            $stmt_registerate -> execute();
             
            //pre_userのflagを１にする（トークンの無効化）
            $stmt_pre = $pdo -> prepare("UPDATE pre_user SET flag=1 WHERE email=:email");
            $stmt_pre -> bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
            $stmt_pre -> execute();
             
            //データベースの接続切断
            $stmt_registerate = NULL;
            $stmt_pre = NULL;
             
            //セッション変数をすべて解除
            $_SESSION = array();
          
            //セッションクッキーの削除
            if (isset($_COOKIE["PHPSESSID"])) {
	     	    setcookie("PHPSESSID", '', time() - 1800, '/');
		    }
		     
		    //セッションを破棄する
		    session_destroy();
		         
        }catch(PDOException $e){
            //トランザクション取り消し
            $pdo -> rollBack();
            $errors['error'] = "もう一度やり直してください。";
            print('Error:'.$e->getMessage());
        }
    }            
?>
    
<body>
    <div class='area create-area'>
        <div class='create-label'>
            <div class='create-label-headding-area'>
                <div class='create-label-headding-area-line'></div>
                <div class='create-label-headding-number-area'>
                    <div class='create-label-headding-number'>
                        No.   1
                    </div>
                </div>
                <div class='create-label-headding'>
                    新規登録カード
                </div>
                <div class='create-label-headding'>
                    ユーザー名及びパスワードを入力してください
                </div>                
            </div>
            <table>
                <!-- page3 完了画面 -->
                <?php if(isset($_POST['btn_submit']) && count($errors) == 0): ?>
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
                        <td>
                            本登録されました。
                        </td>
                        <td></td>                    
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            ログインは<a href="m6-login-form.php">こちら</a>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                       <td></td>
                    </tr>
                </tbody>
                

                <!-- page2 確認画面 -->
                <?php elseif(isset($_POST['btn_confirm']) && count($errors) == 0): ?>
                    <thead>
                        <tr>
                            <th>項目</th>
                            <th>記入欄</th>
                            <th>ボタン</th>
                        </tr>
                    </thead>
                    
                <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>?urltoken=<?php print $urltoken; ?>" method="post">
                    <tbody>
                        <tr>
                            <td>メールアドレス</td>
                            <td>
                                <?=h($_SESSION['email'])?>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>ユーザー名</td>
                            <td>
                                <?= h($_SESSION['username'])?>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>パスワード</td>
                            <td>
                                <?=h($_SESSION['password_hide'])?>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <input type="submit" name="btn_back" value="&#xf104; 戻る" class='create-label-botton fas'>
                                <input type="hidden" name="token" value="<?=$_POST['token']?>">
                            </td>
                            <td></td>
                            <td>
                                <input type="submit" name="btn_submit" value="登録 &#xf105;" class='create-label-botton fas'>
                            </td>
                        </tr>
                    </tbody>
                    
                </form>
   
   
                <!-- page1 登録画面 -->
                <?php elseif(count($errors) > 0):?>
                <thead>
                    <th></th>
                    <th>エラーメッセージ</th>
                    <th></th>
                </thead>
                <tbod>
                    <?php foreach($errors as $value){ ?>
                    <tr>
                        <td></td>
                        <td><?=$value?></td>
                        <td></td>
                    </tr>
                    <?php } ?>
                </tbod>    
                
                <?php elseif(!isset($errors['urltoken_timeover'])): ?>
                    <thead>
                        <tr>
                            <th>項目</th>
                            <th>記入欄</th>
                            <th>ボタン</th>
                        </tr>
                    </thead>
                    
                <tbody>
                    <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>?urltoken=<?php print $urltoken; ?>" method="post">
                        <tr>
                            <td>メールアドレス</td>
                            <td>
                                <?=h($_SESSION['email'])?>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>ユーザー名</td>
                            <td>
                                 <input type="text" name="username" class='create-label-text' value="<?php if( !empty($_SESSION['username']) ){ echo h($_SESSION['username']); } ?>" required>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>パスワード</td>
                            <td>
                                 <input type="password" name="password" class='create-label-text' required>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>パスワード（確認）</td>
                            <td>
                                <input type="password" name="confirm_password" class='create-label-text' required>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <input type="hidden" name="token" value="<?=$token?>">
                                <input type="submit" name="btn_confirm" value="確認 &#xf105;" class='create-label-botton fas'>
                            </td>
                        </tr>
                    </form>
                </tbody>
	            <?php endif; ?>
            </table>
            <div class='create-label-footer'>
                <div class='create-label-footer-message'>
                    ⓒ観劇感想日記
                </div>
            </div>
        </div>
    </div>
   
</body>
</html>