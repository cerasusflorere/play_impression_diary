<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>パスワードリセット -観劇感想日記</title>
</meta>

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
        header("Location:m6-login-form.php");
        exit();
    }else{
        $urltoken = isset($_GET["urltoken"]) ? $_GET["urltoken"] : NULL;
	    $flag = 0;
	        
	    //メール入力判定
	    if ($urltoken === ''){
	        $errors['urltoken'] = "トークンがありません。";
	    }else{
	        $date = date('Y-m-d H:i:s',strtotime("- 24hours"));
	        // flag = 0かつ仮登録日から24時間以内
            $stmt_confirm = $pdo->prepare("SELECT userid, email FROM password_resets WHERE urltoken=:urltoken AND flag=:flag AND date>=:date LIMIT 1");
		    $stmt_confirm->bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
		    $stmt_confirm->bindValue(':flag', $flag, PDO::PARAM_INT);
            $stmt_confirm->bindParam(':date', $date, PDO::PARAM_STR);
		    $stmt_confirm->execute();
		    $count_confirm = $stmt_confirm -> rowCount();
		 
	    	//24時間以内にパスワードリセットが申請され、更新されていないトークンの場合
		    if($count_confirm ===1){
			    $result = $stmt_confirm -> fetch();
                $_SESSION['userid'] = (int)$result['userid'];
			    $_SESSION['email'] = $result["email"];
		    }else{
			     $errors['urltoken_timeover'] = "このURLはご利用できません。有効期限が過ぎたかURLが間違えている可能性がございます。もう一度登録をやりなおして下さい。";
	   	    }
        }
    }
         
    /**
     * 確認する(btn_confirm)押した後の処理
     */
      
    if(isset($_POST['btn_confirm'])){
        if(empty($_POST)){
            header("Location: m6-login-form.php");
            exit();
        }else{
            //POSTされたデータを変数に入れる
            $password = isset($_POST['password']) ? $_POST['password']:NULL;
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password']:NULL;
                 
            //アカウント入力判定
            //パスワード入力判定
            if($password == "" || $confirm_password == ""){
                if(($password === "") || ($confirm_password === "")){
                    if($password === ""){
                        $errors['password'] = "パスワードが入力されていません。";
                    }
                    if($confirm_password === ""){
                        $errors['confirm_password'] = "パスワード（確認）が入力されていません。";
                    }
                }
            }else{
                if(!empty($errors['password'])){
                    unset($errors['password']);
                }
                if(!empty($errors['confirm_password'])){
                    unset($errors['confirm_password']);
                }

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
        $userid = (int)$_SESSION['userid'];
        $email = $_SESSION['email'];
      
        //ここでデータベースに登録する
        try{
            $updateddate = date('Y-m-d H:i:s');
            $stmt_registerate = $pdo -> prepare("UPDATE user SET password = :password, updateddate = :updateddate WHERE id = :id AND email = :email LIMIT 1");
            $stmt_registerate -> bindParam(':password', $password_hash, PDO::PARAM_STR);
            $stmt_registerate -> bindParam(':updateddate', $updateddate, PDO::PARAM_STR);
            $stmt_registerate -> bindValue(':id', $userid, PDO::PARAM_INT);
            $stmt_registerate -> bindParam(':email', $email, PDO::PARAM_STR);
            $stmt_registerate -> execute();
             
            //password_resetsのflagを１にする（トークンの無効化）
            $stmt_password = $pdo -> prepare("UPDATE password_resets SET flag=1 WHERE userid = :userid AND email = :email");
            $stmt_password -> bindValue(':userid', $userid, PDO::PARAM_INT);
            $stmt_password -> bindParam(':email', $email, PDO::PARAM_STR);
            $stmt_password -> execute();
             
            //データベースの接続切断
            $stmt_registerate = NULL;
            $stmt_password = NULL;
             
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
                        No.   2
                    </div>
                </div>
                <div class='create-label-headding'>
                    パスワードリセットカード
                </div>
                <div class='create-label-headding'>
                    新しいパスワードを入力してください
                </div>                
            </div>

            <table>
                <!-- page3 完了画面 -->
                <?php if(isset($_POST['btn_submit']) && count($errors) === 0): ?>
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
                            パスワードをリセットできました。
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
                </tbody>
                
                <!-- page2 確認画面 -->
                <?php elseif(isset($_POST['btn_confirm']) && count($errors) === 0): ?>
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
                            <td>パスワード</td>
                            <td>
                                <?=h($password_hide)?>
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
                <?php if(count($errors) > 0):?>
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
        </div>
    </div>
</body>
</head>
</html>
