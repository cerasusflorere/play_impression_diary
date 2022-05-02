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
    $username  = isset($_SESSION['username']) ? $_SESSION['username'] : NULL;
    $performances = [];
    $id = [];
    $count_results = 0; // useridが投稿した公演数

    if(isset($_SESSION['performance_id'])){
        unset($_SESSION['performance_id']);
    }

    if(isset($_POST['return_home_index'])){
        $_SESSION = array();
        $_SESSION['userid'] = $userid;
        $_SESSION['username'] = $username;
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
                    $performances[$count_results] = h($row['performance']);
                    $_SESSION['performances_id'][$count_results] = $row['id'];
                    $_SESSION['performances_title'][$count_results] = h($row['performance']);
                }
            }
        }
        $send_count_results = json_encode($count_results);
        $send_performances_id = json_encode($id);
        $send_performances_title = json_encode($performances);
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
   <div class='area area-home'>
        <div class='book-area'>
            <div id='book_page_area' class='book-page-area'>
                <!-- データ表示 -->
           </div>
        </div>
        <div class='bookmark-area'>
            <div class='bookmark-ribbon-area'>
                <div class='bookmark-ribbon bookmark-ribbon-left'></div>
                <div class='bookmark-ribbon bookmark-ribbon-right'></div>
            </div>            
            <ul class='bookmark'>
                <li><?=h($username)?>さん</li>
                <form action="" method="post">
                    <li><i class="fa-fw fas fa-plus"></i></i><input type="submit" name="btn_add" value="追加" class='bookmark-button'></li>
                    <li><a href="m6-profile.php"><i class="fa-fw fas fa-user-alt"></i>プロフィール</a></li>
                    <li><a href="m6-logout.php"><i class="fa-fw fas fa-sign-out-alt"></i>ログアウト</a></li>
                </form>
            </ul>
        </div>
   </div>

   <script>
        const count_results = JSON.parse(<?=$send_count_results?>);
        const performances_id_object = JSON.parse('<?=$send_performances_id?>');
        const performances_title_object = JSON.parse('<?=$send_performances_title?>');
        performances_id = Object.entries(performances_id_object);
        performances_title = Object.entries(performances_title_object);

        const listArea = document.getElementById('book_page_area');

        window.onload = showList(count_results,performances_id,performances_title);

        function showList(count_number, id, title){
            if(count_number == 0){
                // データがない場合
                const id_object = [[0, 0]];
                id = id_object;

                const title_object = [[0, 'データがありません。']];
                title = title_object;
            }

            counts = Math.floor(count_number/24); // 24個のデータで表裏1ページ、1ページ毎に表示
            for(i=0; i<counts+1; i++){
                const pageLabel = document.createElement('label');
                const pageInput = document.createElement('input')
                pageInput.type = 'checkbox';

                const before = new Promise(function(resolve, reject) {
                    const in_before = new Promise((function(res, rej) {
                        // 右側
                        const pageSpan_right = document.createElement('span');
                        pageSpan_right.classList.add("page", "page-right");
                        pageSpan_right.style.zIndex = counts-i;

                        const pageUl_right = document.createElement('ul');
                        pageUl_right.className = 'note';
                  
                        for(j=i*24; j<i*24+11; j++){
                            if(typeof id[j] != 'undefined'){
                                if(id[j][1] != 0){
                                const pageForm = document.createElement('form');
                                pageForm.action = 'm6-indivisual-subject-show.php';
                                pageForm.method = 'post';
                                pageForm.name = 'form'+id[j][1];

                                const pageLi = document.createElement('li');
                                pageLi.innerHTML = j+1+': ';
   
                                const pageA = document.createElement('a');
                                pageA.href = 'm6-indivisual-subject-show.php';
                                pageA.setAttribute('onclick', 'document.form'+id[j][1]+'.submit();return false');
                                pageA.innerHTML = title[j][1];

                                const pageInput_hidden = document.createElement('input');
                                pageInput_hidden.type = 'hidden';
                                pageInput_hidden.name = 'performance_id';
                                pageInput_hidden.value = id[j][1];

                                pageLi.appendChild(pageA);
                                pageForm.appendChild(pageLi);
                                pageForm.appendChild(pageInput_hidden);
                                pageUl_right.appendChild(pageForm);
                            }else{
                                const pageLi = document.createElement('li');
                                pageLi.innerHTML = title[j][1];
                                pageUl_right.appendChild(pageLi);
                            }
                        
                            }else{
                                const pageLi = document.createElement('li');
                                pageUl_right.appendChild(pageLi);
                            }
                        }
                        pageSpan_right.appendChild(pageUl_right);
  
                        // 左側
                        const pageSpan_left = document.createElement('span');
                        pageSpan_left.classList.add("page", "page-left");
    
                        const pageUl_left = document.createElement('ul');
                        pageUl_left.className = 'note';
                     
                        for(j=i*24+11; j<i*24+22; j++){
                            if(typeof id[j] != 'undefined'){
                                const pageForm = document.createElement('form');
                                pageForm.action = 'm6-indivisual-subject-show.php';
                                pageForm.method = 'post';
                                pageForm.name = 'form'+id[j][1];
    
                                const pageLi = document.createElement('li');
                                pageLi.innerHTML = j+1+': ';
   
                                const pageA = document.createElement('a');
                                pageA.href = 'm6-indivisual-subject-show.php';
                                pageA.setAttribute('onclick', 'document.form'+id[j][1]+'.submit();return false');
                                pageA.innerHTML = title[j][1];

                                const pageInput_hidden = document.createElement('input');
                                pageInput_hidden.type = 'hidden';
                                pageInput_hidden.name = 'performance_id';
                                pageInput_hidden.value = id[j][1];

                                pageLi.appendChild(pageA);
                                pageForm.appendChild(pageLi);
                                pageForm.appendChild(pageInput_hidden);
                                pageUl_left.appendChild(pageForm);
                            }else{
                                const pageLi = document.createElement('li');
                                pageUl_left.appendChild(pageLi);
                            }
                        }
                        pageSpan_left.appendChild(pageUl_left);
                        res([pageSpan_right, pageSpan_left]);
                    }));
                    resolve(in_before);
                });

                before.then(result => {
                   pageLabel.appendChild(pageInput);
                   pageLabel.appendChild(result[0]);
                   pageLabel.appendChild(result[1]);

                    listArea.appendChild(pageLabel);
                });
            }
        }
    </script>

</body>        
</html>
