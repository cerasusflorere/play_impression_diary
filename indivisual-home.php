<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>ホーム</title>
</meta>
</head>
     
<?php 
    session_start();
    //クロスサイトリクエストフォージェリ（CSRF）対策
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];
    //クリックジャッキング対策
    header('X-FRAME-OPTIONS: SAMEORIGIN'); 
            
    function h($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
             
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

    $userid = isset($_SESSION['id']) ? $_SESSION['id'] : NULL;
    $performance = [];
    $count_results = 0;
         
    try{
        $stmt_home = $mysqli -> prepare("SELECT id, performance FROM impression WHERE userid=?");
        $stmt_home -> bind_param('i', $userid);
        $stmt_home -> execute();
        $stmt_home->bind_result($id, $performance);    
        // 値を取得します 
        $stmt_home->fetch();

        while($stmt_home -> fetch()){
            if(isset($performance)){
                $count_results++;
                $performance[$count_results] = $row['performance'];
            }
        }
    }catch(PDOException $e){
        //トランザクション取り消し
        $pdo -> rollBack();
        $errors['error'] = "もう一度やり直してください。";
        print('Error:'.$e->getMessage());
    }
     
    /** 
    * 追加する（btn_add）を押した後の処理
    */
    if(isset($_POST['btn_add'])){
        unset($_SESSION['id']);
        header("Location:m6-indivisual-subject-add.php");
        exit();
    }
?>
    
<body>
    <?php if($count_results > 0): ?>
        <?php for($i=1; $i<=$count_results; $i++){ ?>
            <form action="m6-indivisual-subject-show.php" method="post" name="<?php echo $performance[$i]; ?>">
                <p><?php echo $i.":"; ?>
                <a href="m6-indivisual-subject-show.php" onClick="<?php echo 'document.'.$performance[$i].'.submit();return false;' ?>"><?php echo $performance[$i]; ?></a></p>
                <input type=hidden name="performance_name" value="<?php echo $performance[$i]; ?>">
            </form>
        <?php } ?>
    <?php else :
            echo "データがありません。<br>";
        endif; ?>
    <form action="" method="post">
        <input type="submit" name="btn_add" value="追加する">
        <p><a href = "m6-logout.php">ログアウトはこちら</a></p>
        <p><a href = "m6-withdrow.php">退会する</a></p>
    </form>
</body>
        
</html>  
