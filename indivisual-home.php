<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>ホーム -観劇感想日記</title>
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
    $DSN = $_ENV["DSN"];
    $USER = $_ENV["USER"];
    $PASS = $_ENV["PASS"];

    $errors = array();

    // DB接続設定
    try {
        $pdo = new PDO($DSN, $USER, $PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    } catch (PDOException $e) {
        $errors['DB_error'] = '接続失敗';
        exit();
    }

    $userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : NULL;
    $performance = [];
    $id = [];
    $count_results = 0; // useridが投稿した公演数

    if(isset($_SESSION['performance_id'])){
        unset($_SESSION['performance_id']);
    }

    if(isset($_POST['return_home_index'])){
        $_SESSION = array();
        $_SESSION['userid'] = $userid;
        $_SESSION['token'] = $token;
    }
         
    try{
        $stmt_home = $pdo -> prepare("SELECT id, performance FROM impression WHERE userid=:userid");
        $stmt_home -> bindParam(':userid', $userid, PDO::PARAM_INT);
        $stmt_home -> execute();
        $results  = $stmt_home -> fetchAll();
         
        if(is_array($results)){
            foreach($results as $row){
                if(isset($row['performance'])){
                    $count_results++;
                    $id[$count_results] = $row['id'];
                    $performance[$count_results] = h($row['performance']);
                    $_SESSION['performances_id'][$count_results] = $row['id'];
                    $_SESSION['performances_title'][$count_results] = h($row['performance']);
                }
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
                <input type=hidden name='performance_id' value="<?php echo $id[$i]; ?>">
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
