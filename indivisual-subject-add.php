<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>追加ページ</title>
</meta>
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
    
    $sucess = "";
    $flag = 0;
    $stages_array = [];
    $numbers = 0;
         
    $userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : NULL;
         
    $sql_stages = "SELECT * FROM impression WHERE userid=:userid";
    $stmt_stages = $pdo -> prepare($sql_stages);
    $stmt_stages -> bindParam(':userid', $userid, PDO::PARAM_INT);
    $results_stages = $stmt_stages -> fetchAll();
    if(is_array($results_stages)){
        foreach($results_stages as $row_stages){
            $stages_array[$numbers] = $row_stages['performance'];
            $numbers++;
        }
        foreach($stages_array as $stages_array_key => $stages_array_val){
            $stages_array .= "<option value='".$stages_array_key;
            $stages_array .= "'>".$stages_array_val."</option>";
        }
    }

    $flag = 1;
        
    /**
     * 確認する（btn_confirm）を押した後の処理
    */
    if(isset($_POST['btn_confirm'])){
        $_SESSION['performance'] = isset($_POST['performance']) ? $_POST['performance'] : NULL;
        $_SESSION['theater'] = isset($_POST['theater']) ? $_POST['theater'] : NULL;
        $_SESSION['date'] = isset($_POST['date']) ? $_POST['date'] : NULL;
        $_SESSION['open_time'] = isset($_POST['open_time']) ? $_POST['open_time'] : NULL;
        $_SESSION['close_time'] = isset($_POST['close_time']) ? $_POST['close_time'] : NULL;
        $_SESSION['stage'] = isset($_POST['stage']) ? $_POST['stage'] : NULL;
        $_SESSION['seat'] = isset($_POST['seat']) ? $_POST['seat'] : NULL;
        $_SESSION['first_date'] = isset($_POST['first_date']) ? $_POST['first_date'] : NULL;
        $_SESSION['final_date'] = isset($_POST['final_date']) ? $_POST['final_date'] : NULL;
        $_SESSION['organizer'] = isset($_POST['organizer']) ? $_POST['organizer'] : NULL;
        $_SESSION['director'] = isset($_POST['director']) ? $_POST['director'] : NULL;
        $_SESSION['author'] = isset($_POST['author']) ? $_POST['author'] : NULL;
        $_SESSION['dance'] = isset($_POST['dance']) ? $_POST['dance'] : NULL;
        $_SESSION['music'] = isset($_POST['music']) ? $_POST['music'] : NULL;
        $_SESSION['lyrics'] = isset($_POST['lyrics']) ? $_POST['lyrics'] : NULL;
        $_SESSION['cloth'] = isset($_POST['cloth']) ? $_POST['cloth'] : NULL;
        $_SESSION['light'] = isset($_POST['light']) ? $_POST['light'] : NULL;
        $_SESSION['property'] = isset($_POST['property']) ? $_POST['property'] : NULL;
        $_SESSION['players'] = isset($_POST['players']) ? $_POST['players']: NULL;
        $_SESSION['scenario'] = isset($_POST['scenario']) ? $_POST['scenario']: NULL;
        $_SESSION['impression_all'] = isset($_POST['impression_all']) ? $_POST['impression_all'] : NULL;
        for($i = 1; $i <= 50; $i++){
            $_SESSION['player_impression_['.$i.']'] = isset($_POST['player_impression_['.$i.']']) ? $_POST['player_impression_['.$i.']'] : NULL;
            $_SESSION['impression_player_['.$i.']'] = isset($_POST['impression_player_['.$i.']']) ? $_POST['impression_player_['.$i.']'] : NULL;
            $_SESSION['impression_scene_['.$i.']'] = isset($_POST['impression_scene_['.$i.']']) ? $_POST['impression_scene_['.$i.']'] : NULL;
            if($i <= 10){
                $_SESSION['related_performances_['.$i.']'] = isset($_POST['related_performances_['.$i.']']) ? $_POST['rerated_performances_['.$i.']'] : NULL;
            }
        }
        $_SESSION['impression_final'] = isset($_POST['impression_final']) ? $_POST['impression_final'] : NULL;
    }
         
    /**
      * 登録する(btn_submit)を押した後の処理
    */
    if(isset($_POST["btn_submit"])){
        $userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : NULL;
        echo $userid;
        if($userid !== NULL){
            $performance = $_SESSION['performance'];
            $theater = $_SESSION['theater'];
            $date = $_SESSION['date'];
            $open_time = $_SESSION['open_time'];
            $close_time = $_SESSION['close_time'];
            $stage = $_SESSION['stage'];
            $seat = $_SESSION['seat'];
            $first_date = $_SESSION['first_date'];
            $final_date = $_SESSION['final_date'];
            $organizer = $_SESSION['organizer'];
            $director  = $_SESSION['director'];
            $author = $_SESSION['author'];
            $dance = $_SESSION['dance'];
            $music = $_SESSION['music'];
            $lyrics = $_SESSION['lyrics'];
            $cloth = $_SESSION['cloth'];
            $light = $_SESSION['light'];
            $property = $_SESSION['property'];
            $players = $_SESSION['players'];
            $scenario = $_SESSION['scenario'];
            $impression_all = $_SESSION['impression_all'];
            for($i = 1; $i < 51; $i++){
                $player_impression[$i] = $_SESSION["player_impression_[{$i}]"];
                $impression_player[$i] = $_SESSION["impression_player_[{$i}]"];
                $impression_scene[$i] = $_SESSION["impression_scene_[{$i}]"];
                if($i < 11){
                    $related_performances[$i] = $_SESSION["related_performances_[{$i}]"];
                }
            }
            $impression_final = $_SESSION['impression_final'];             
                 
            try{
                $sql_add = $pdo -> prepare("INSERT INTO impression 
                                            (userid, performance, theater, date, open_time, close_time, stage, seat,
                                             first_date, final_date, organizer, director, author, dance,
                                             music, lyrics, costume, illumination, property, 
                                             player_1, player_2, player_3, player_4, player_5, player_6, player_7, player_8, player_9, player_10, 
                                             player_11, player_12, player_13, player_14, player_15, player_16, player_17, player_18, player_19, player_20, 
                                             player_21, player_22, player_23, player_24, player_25, player_26, player_27, player_28, player_29, player_30, 
                                             player_31, player_32, player_33, player_34, player_35, player_36, player_37, player_38, player_39, player_40, 
                                             player_41, player_42, player_43, player_44, player_45, player_46, player_47, player_48, player_49, player_50
                                             scenario, impression_all,
                                             player_impression_1, player_impression_2, player_impression_3, player_impression_4, player_impression_5, player_impression_6, player_impression_7, player_impression_8, player_impression_9, player_impression_10, 
                                             player_impression_11, player_impression_12, player_impression_13, player_impression_14, player_impression_15, player_impression_16, player_impression_17, player_impression_18, player_impression_19, player_impression_20, 
                                             player_impression_21, player_impression_22, player_impression_23, player_impression_24, player_impression_25, player_impression_26, player_impression_27, player_impression_28, player_impression_29, player_impression_30, 
                                             player_impression_31, player_impression_32, player_impression_33, player_impression_34, player_impression_35, player_impression_36, player_impression_37, player_impression_38, player_impression_39, player_impression_40, 
                                             player_impression_41, player_impression_42, player_impression_43, player_impression_44, player_impression_45, player_impression_46, player_impression_47, player_impression_48, player_impression_49, player_impression_50
                                             impression_player_1, impression_player_2, impression_player_3, impression_player_4, impression_player_5, impression_player_6, impression_player_7, impression_player_8, impression_player_9, impression_player_10, 
                                             impression_player_11, impression_player_12, impression_player_13, impression_player_14, impression_player_15, impression_player_16, impression_player_17, impression_player_18, impression_player_19, impression_player_20, 
                                             impression_player_21, impression_player_22, impression_player_23, impression_player_24, impression_player_25, impression_player_26, impression_player_27, impression_player_28, impression_player_29, impression_player_30, 
                                             impression_player_31, impression_player_32, impression_player_33, impression_player_34, impression_player_35, impression_player_36, impression_player_37, impression_player_38, impression_player_39, impression_player_40, 
                                             impression_player_41, impression_player_42, impression_player_43, impression_player_44, impression_player_45, impression_player_46, impression_player_47, impression_player_48, impression_player_49, impression_player_50, 
                                             impression_scene_1, impression_scene_2, impression_scene_3, impression_scene_4, impression_scene_5, impression_scene_6, impression_scene_7, impression_scene_8, impression_scene_9, impression_scene_10, 
                                             impression_scene_11, impression_scene_12, impression_scene_13, impression_scene_14, impression_scene_15, impression_scene_16, impression_scene_17, impression_scene_18, impression_scene_19, impression_scene_20, 
                                             impression_scene_21, impression_scene_22, impression_scene_23, impression_scene_24, impression_scene_25, impression_scene_26, impression_scene_27, impression_scene_28, impression_scene_29, impression_scene_30, 
                                             impression_scene_31, impression_scene_32, impression_scene_33, impression_scene_34, impression_scene_35, impression_scene_36, impression_scene_37, impression_scene_38, impression_scene_39, impression_scene_40, 
                                             impression_scene_41, impression_scene_42, impression_scene_43, impression_scene_44, impression_scene_45, impression_scene_46, impression_scene_47, impression_scene_48, impression_scene_49, impression_scene_50,                                                     
                                             impression_final,
                                             related_performance_1, related_performance_2, related_performance_3, related_performance_4, related_performance_5, related_performance_6, related_performance_7, related_performance_8, related_performance_9, related_performance_10)
                                            VALUES (:userid, :performance, :theater, :date, :open_time, :close_time, :stage, :seat,
                                                    :first_date, :final_date, :organizer, :director, :author, :dance,
                                                    :music, :lyrics, :cloth, :light, :property, :players, :scenario, :impression_all,
                                                    :player_impression_1, :player_impression_2, :player_impression_3, :player_impression_4, :player_impression_5, :player_impression_6, :player_impression_7, :player_impression_8, :player_impression_9, :player_impression_10, 
                                                    :player_impression_11, :player_impression_12, :player_impression_13, :player_impression_14, :player_impression_15, :player_impression_16, :player_impression_17, :player_impression_18, :player_impression_19, :player_impression_20, 
                                                    :player_impression_21, :player_impression_22, :player_impression_23, :player_impression_24, :player_impression_25, :player_impression_26, :player_impression_27, :player_impression_28, :player_impression_29, :player_impression_30, 
                                                    :player_impression_31, :player_impression_32, :player_impression_33, :player_impression_34, :player_impression_35, :player_impression_36, :player_impression_37, :player_impression_38, :player_impression_39, :player_impression_40, 
                                                    :player_impression_41, :player_impression_42, :player_impression_43, :player_impression_44, :player_impression_45, :player_impression_46, :player_impression_47, :player_impression_48, :player_impression_49, :player_impression_50, 
                                                    :impression_player_1, :impression_player_2, :impression_player_3, :impression_player_4, :impression_player_5, :impression_player_6, :impression_player_7, :impression_player_8, :impression_player_9, :impression_player_10, 
                                                    :impression_player_11, :impression_player_12, :impression_player_13, :impression_player_14, :impression_player_15, :impression_player_16, :impression_player_17, :impression_player_18, :impression_player_19, :impression_player_20, 
                                                    :impression_player_21, :impression_player_22, :impression_player_23, :impression_player_24, :impression_player_25, :impression_player_26, :impression_player_27, :impression_player_28, :impression_player_29, :impression_player_30, 
                                                    :impression_player_31, :impression_player_32, :impression_player_33, :impression_player_34, :impression_player_35, :impression_player_36, :impression_player_37, :impression_player_38, :impression_player_39, :impression_player_40, 
                                                    :impression_player_41, :impression_player_42, :impression_player_43, :impression_player_44, :impression_player_45, :impression_player_46, :impression_player_47, :impression_player_48, :impression_player_49, :impression_player_50, 
                                                    :impression_scene_1, :impression_scene_2, :impression_scene_3, :impression_scene_4, :impression_scene_5, :impression_scene_6, :impression_scene_7, :impression_scene_8, :impression_scene_9, :impression_scene_10, 
                                                    :impression_scene_11, :impression_scene_12, :impression_scene_13, :impression_scene_14, :impression_scene_15, :impression_scene_16, :impression_scene_17, :impression_scene_18, :impression_scene_19, :impression_scene_20, 
                                                    :impression_scene_21, :impression_scene_22, :impression_scene_23, :impression_scene_24, :impression_scene_25, :impression_scene_26, :impression_scene_27, :impression_scene_28, :impression_scene_29, :impression_scene_30, 
                                                    :impression_scene_31, :impression_scene_32, :impression_scene_33, :impression_scene_34, :impression_scene_35, :impression_scene_36, :impression_scene_37, :impression_scene_38, :impression_scene_39, :impression_scene_40, 
                                                    :impression_scene_41, :impression_scene_42, :impression_scene_43, :impression_scene_44, :impression_scene_45, :impression_scene_46, :impression_scene_47, :impression_scene_48, :impression_scene_49, :impression_scene_50,                                                     
                                                    :impression_final,
                                                    :related_performance_1, :related_performance_2, :related_performance_3, :related_performance_4, :related_performance_5, :related_performance_6, :related_performance_7, :related_performance_8, :related_performance_9, :related_performance_10)");
              
                $sql_add -> bindParam(':userid', $userid, PDO::PARAM_INT);
                $sql_add -> bindParam(':performance', $performance, PDO::PARAM_STR);
                $sql_add -> bindParam(':theater', $theater, PDO::PARAM_STR);
                $sql_add -> bindParam(':date', $date, PDO::PARAM_STR);
                $sql_add -> bindParam(':open_time', $open_time, PDO::PARAM_STR);
                $sql_add -> bindParam(':close_time', $close_time, PDO::PARAM_STR);
                $sql_add -> bindParam(':stage', $stage, PDO::PARAM_STR);
                $sql_add -> bindParam(':seat', $seat, PDO::PARAM_STR);
                $sql_add -> bindParam(':first_date', $first_date, PDO::PARAM_STR);
                $sql_add -> bindParam(':final_date', $final_date, PDO::PARAM_STR);
                $sql_add -> bindParam(':organizer', $organizer, PDO::PARAM_STR);
                $sql_add -> bindParam(':director', $director, PDO::PARAM_STR);
                $sql_add -> bindParam(':author', $author, PDO::PARAM_STR);
                $sql_add -> bindParam(':dance', $dance, PDO::PARAM_STR);
                $sql_add -> bindParam(':music', $music, PDO::PARAM_STR);
                $sql_add -> bindParam(':lyrics', $lyrics, PDO::PARAM_STR);
                $sql_add -> bindParam(':cloth', $cloth, PDO::PARAM_STR);
                $sql_add -> bindParam(':light', $light, PDO::PARAM_STR);
                $sql_add -> bindParam(':property', $property, PDO::PARAM_STR);
                $sql_add -> bindParam(':players', $players, PDO::PARAM_STR);
                $sql_add -> bindParam(':scenario', $scenario, PDO::PARAM_STR);
                $sql_add -> bindParam(':impression_all', $impression_all, PDO::PARAM_STR);
                for($i=1; $i<51; $i++){
                    $sql_add -> bindParam(":player_impression_{$i}", $player_impression[$i], PDO::PARAM_STR);
                    $sql_add -> bindParam(":impression_player_{$i}", $impression_player[$i], PDO::PARAM_STR);
                    $sql_add -> bindParam(":impression_scene_{$i}", $impression_scene[$i], PDO::PARAM_STR);
                    if($i<11){
                        $sql_add -> bindParam("related_performance_{$i}", $related_performance[$i], PDO::PARAM_STR);
                    }
                }
                $sql_add -> bindParam(':impression_final', $impression_final, PDO::PARAM_STR);
                     
                $sql_add -> execute();
                    
                $_SESSION = array();
                $_SESSION['userid'] = $userid;
                     
                $sucess = "追記されました。";
            }catch(PDOException $e){
                //トランザクション取り消し
                $pdo -> rollBack();
                $errors['error'] = "もう一度やり直してください。";
                print('Error:'.$e->getMessage());
            }
        }else{
            $errors = "useridがありません。";
        }
             
    }
         
    /**
      * 戻る・page1・page3(btn_back_home)を押した後の処理
    */
    if(isset($_POST['btn_back_home'])){
        header("Location:m6-indivisual-subject-home.php");
        exit();
    }
         
    /**
      * 戻る・page2(btn_back_add)を押した後の処理
    */
    if(isset($_POST['btn_back_add'])){
        header("Location:m6-indivisual-subject-add.php");
        exit();
    }
?>

<body>
    <h1>楽しかった公演の記録をどうぞ！</h1>
    <!-- page3 完了画面 -->
    <?php if(count($errors) === 0 && isset($_POST['btn_submit'])): ?>
        <?php echo $sucess.PHP_EOL; ?>
            <p>ホーム<a href="m6-indivisual-home.php">こちら</a></p>
   
    <!-- page2 確認画面 -->
    <?php elseif(count($errors) === 0 && isset($_POST['btn_confirm'])): ?>
        <form action="" method="post" enctype="multipart/form-data">
		    <p>公演：<?php echo $_SESSION['performance']; ?></p>
			<p>劇団：<?php echo $_SESSION['theater']; ?></p>
			<p>観劇日：<?php echo $_SESSION['date']; ?></p>
			<p>開演時刻：<?php echo $_SESSION['open_time']; ?> ~ 終演時刻：<?php echo $_SESSION['close_time']; ?></p>
			<p>観劇した劇場：<?php echo $_SESSION['stage']; ?></p>
			<p>座席：<?php echo $_SESSION['seat']; ?></p>
			<p>主催：<?php echo $_SESSION['organizer']; ?></p>
			<p>演出：<?php echo $_SESSION['director']; ?></p>
			<p>作家：<?php echo $_SESSION['author']; ?></p>
			<p>振付：<?php echo $_SESSION['dance']; ?></p>
				   <p>音楽：<?php echo $_SESSION['music']; ?></p>
				   <p>作詞：<?php echo $_SESSION['lyrics']; ?></p>
				   <p>衣装：<?php echo $_SESSION['cloth']; ?></p>
				   <p>照明：<?php echo $_SESSION['light']; ?></p>
				   <p>小道具：<?php echo $_SESSION['property']; ?></p>
				   <p>公演期間：<?php echo $_SESSION['first_date']; ?> ~
				                <?php echo $_SESSION['final_date']; ?></p>
				   <p>出演者：<?php echo $_SESSION['players']; ?></p>
				   <p>あらすじ：<?php echo $_SESSION['scenario']; ?></p>
				   <p>全体について思うこと：<?php echo $_SESSION['impression_all']; ?></p>
				   <?php for($i=1; $i<51; $i++){ 
				             if(isset($_SESSION['player_impression_['.$i.']'])): ?>
				   <p>出演者について感想：<?php echo $_SESSION['player_impression_['.$i.']']; ?> 
				      出演者に対するコメント：<?php echo $_SESSION['impression_player_['.$i.']']; ?></p>
				   <?php     else :
				                 break;
				             endif; 
				          }?>
				   <?php for ($i=1; $i<51; $i++){ 
				             if(isset($_SESSION['impression_scene_['.$i.']'])): ?>
				   <p>好きな場面とその理由：<?php echo $_SESSION['impression_scene_['.$i.']']; ?></p>
                   <?php     else :
                                 break;
                             endif; 
                         } ?>
	   			   <p>最後に：<?php echo $_SESSION['impression_final']; ?></p>
	   			   <?php for($i=1; $i<11; $i++){ 
	   			             if(isset($_SESSION['related_performance_['.$i.']'])): ?>
	   			   <p>関連のある公演：<?php echo $_SESSION['related_performances_['.$i.']']; ?></p>
	   			   <?php     else :
	   			                 break;
	   			             endif;
	   			         } ?>
             
             <input type="submit" name="btn_back" value="戻る">
             <input type="submit" name="btn_submit" value="登録する">
         </form>
   
     <!-- page1 登録画面 -->
     <?php if(count($errors) > 0):?>
             <?php 
             foreach($errors as $value){
                 echo "<p class='error'>".$value."</p>";
             }
             ?>
         <?php endif; ?>
     <?php elseif($flag === 1 || isset($_POST['btn_back'])): ?>
                 <form action="" method="post" enctype="multipart/form-data">
				    <p>公演：<input type="text" name="performance" value="<?php if( !empty($_SESSION['performance']) ){ echo $_SESSION['performance']; } ?>"></p>
				    <p>劇団：<input type="text" name="theater" value="<?php if( !empty($_SESSION['theater']) ){ echo $_SESSION['theater']; } ?>"></p>
				    <p>観劇日：<input type="date" name="date" value="<?php if( !empty($_SESSION['date']) ){ echo $_SESSION['date']; }else{ echo "2021-07-04"; } ?>"></p>
				    <p>公演時間：<input type="time" name="open_time" value="<?php if( !empty($_SESSION['open_time']) ){ echo $_SESSION['open_time']; }else{ echo "13:00"; } ?>"> ~
				                <input type="time" name="close_time" value="<?php if( !empty($_SESSION['close_time']) ){ echo $_SESSION['close_time']; }else{ echo "16:00"; } ?>"></p>
				    <p>観劇した劇場：<input type="text" name="stage" value="<?php if( !empty($_SESSION['stage']) ){ echo $_SESSION['stage']; } ?>"></p>
				    <p>座席：<input type="text" name="seat" value="<?php if(!empty($_SESSION['stage'])){ echo $_SESSION['seat']; } ?>"></p>
				    <p>公演期間：<input type="date" name="first_date" value="<?php if( !empty($_SESSION['first_date']) ){ echo $_SESSION['first_date']; } ?>"> ~
				                <input type="date" name="final_date" value="<?php if( !empty($_SESSION['final_date']) ){ echo $_SESSION['final_date']; } ?>"></p>
				    <p>主催：<input type="text" name="organizer" value="<?php if( !empty($_SESSION['organizer']) ){ echo $_SESSION['organizer']; } ?>"></p>
				    <p>演出：<input type="text" name="director" value="<?php if( !empty($_SESSION['director']) ){ echo $_SESSION['director']; } ?>"></p>
				    <p>作家：<input type="text" name="author" value="<?php if( !empty($_SESSION['author']) ){ echo $_SESSION['author']; } ?>"></p>
				    <p>振付：<input type="text" name="dance" value="<?php if( !empty($_SESSION['dance']) ){ echo $_SESSION['dance']; } ?>"></p>
                    <p>音楽：<input type="text" name="music" value="<?php if( !empty($_SESSION['music']) ){ echo $_SESSION['music']; } ?>"></p>
                    <p>作詞：<input type="text" name="lyrics" value="<?php if( !empty($_SESSION['lyrics']) ){ echo $_SESSION['lyrics']; } ?>"></p>
				    <p>衣装：<input type="text" name="cloth" value="<?php if( !empty($_SESSION['cloth']) ){ echo $_SESSION['cloth']; } ?>"></p>
				    <p>照明：<input type="text" name="light" value="<?php if( !empty($_SESSION['light']) ){ echo $_SESSION['light']; } ?>"></p>
				    <p>小道具：<input type="text" name="property" value="<?php if( !empty($_SESSION['property']) ){ echo $_SESSION['property']; } ?>"></p>
				    <p>出演者：</p>
                        <div id='all_players_area'>
                            <p id='player_area_0'><input type="text" name="player[]" id="player_0" onkeyup="checkPlayer()" value="<?php if( !empty($_SESSION['player'][0]) ){ echo $_SESSION['player'][0]; } ?>"></p>
                            <p id='player_area_1'><input type="text" name="player[]" id="player_1" onkeyup="checkPlayer()" value="<?php if( !empty($_SESSION['player'][1]) ){ echo $_SESSION['player'][1]; } ?>"></p>
                            <p id='player_area_2'><input type="text" name="player[]" id="player_2" onkeyup="checkPlayer()" value="<?php if( !empty($_SESSION['player'][2]) ){ echo $_SESSION['player'][2]; } ?>"></p>
                            <p id='player_area_3'><input type="text" name="player[]" id="player_3" onkeyup="checkPlayer()" value="<?php if( !empty($_SESSION['player'][3]) ){ echo $_SESSION['player'][3]; } ?>"></p>
                            <p id='player_area_4'><input type="text" name="player[]" id="player_4" onkeyup="checkPlayer()" value="<?php if( !empty($_SESSION['player'][4]) ){ echo $_SESSION['player'][4]; } ?>"></p>                    
                        </div>
                        <input type="button" id="add_player" value='+' onclick="addPlayer()">
                        <input type="button" id="disp_player" value='-' onclick="dispPlayer()">
                    
				    <p>あらすじ：<textarea name="scenario" value="<?php if( !empty($_SESSION['scenario']) ){ echo $_SESSION['scenario']; } ?>"></textarea></p>
				    <p>全体について思うこと：<textarea name="impression_all" value="<?php if( !empty($_SESSION['impression_all']) ){ echo $_SESSION['impression_all']; } ?>"></textarea></p>

                    <p>出演者に対する感想</p>
                    <div id='all_impressions_player_area'>
                        <p id='impression_area_0'>
                            出演者：<select name='player_impression[]' id='player_impression_0'>
                                        <option value='<?php if( !empty($_SESSION['player_impression'][0]) ){ echo $_SESSION['player_impression'][0]; } ?>'>選択してください</option>
                                    </select> 
				            出演者に対するコメント：<textarea name="impression_player[]" id='impression_player_0' value="<?php if( !empty($_SESSION['impression_player'][0]) ){ echo $_SESSION['impression_player'][0]; } ?>"></textarea>
                        </p>
                    </div>
				    <input type="button" id="add_impression_player" value='+' onclick="addImpression_Player()">
                    <input type="button" id="disp_impression_player" value='-' onclick="dispImpression_Player()">
                    
				    <p>好きな場面とその理由</p>
                    <div id='all_impressions_scene_area'>
                        <p id='impression_scene_area_0'>
                            <input type='text' name='scene_impression[]' value='<?php if( !empty($_SESSION['scene_impression'][0]) ){ echo $_SESSION['scene_impression'][0]; } ?>'>
                            <textarea name="impression_scene[]" value="<?php if( !empty($_SESSION['impression_scene'][0]) ){ echo $_SESSION['impression_scene'][0]; } ?>"></textarea>
                        </p>                        
                    </div>                    
	   			    <input type="button" id="add_impression_scene" type="button" value='+' onclick="addImpression_Scene()">
                    <input type="button" id="disp_impression_scene" value='-' onclick="dispImpression_Scene()">

	   			    <p>最後に：<textarea name="impression_final" value="<?php if( !empty($_SESSION['impression_final']) ){ echo $_SESSION['impression_final']; } ?>"></textarea></p>
	   			    <p>関連のある公演：</p>
                    <div id='all_related_performances_area'>
                        <p id='related_performance_area_0'>
	   			            <select name='related_performances[]' id='related_performances_0' value="<?php if( !empty($_SESSION['related_performances'][0]) ){ echo $_SESSION['related_performances'][0]; } ?>">
	   			                <option value=''>選択してください</option>
                                    <?php 
                                        echo $stages_array; ?>
                            </select>
                        </p>
                    </div>                        
                    <input type="button" id="add_related_performance" value="+" onclick="addRelated_Performance()">
                    <input type="button" id="disp_related_performance" value="-" onclick="dispRelated_Performance()"><br>
                   
                    <input type="submit" name="btn_confirm" value="確認する"><br>
                    <p><a href="m6-indivisual-home.php">戻る</a></p>
                </form>
	<?php endif; ?>
 
	<script>
        let all_players_area = document.getElementById('all_players_area');
        let all_impressions_player_area = document.getElementById('all_impressions_player_area');
        let all_related_performances_area = document.getElementById('all_related_performances_area');

        let select_players = [].slice.call(document.querySelectorAll('[id^="player_impression"]')); // 出演者を入力したら、出演者のプルダウンメニューが増える。
        // フォーム追加
        let current_player_number = 4; // 出演者の最後のid番号
        let current_impression_player_number = 0; // 出演者に関する感想の最後のid番号
        let current_impression_scene_number = 0; // シーンごとの感想の最後のid番号
        let current_related_performance_number = 0; // 関連する舞台の最後のid番号

        function checkPlayer() {
            const str = event.currentTarget.id;
            id_number = str.replace('player_', '');
            const player_id = 'player_'+ id_number;
            var inputPlayer = document.getElementById(player_id).value;
            const player_option_id = 'player_impression_option[' + id_number + ']';
            let option_player = document.getElementById(player_option_id);

            if(option_player == null){
                let playerOption = document.createElement('option');
                playerOption.id = player_option_id;
                
                let i = 1;
                select_players.forEach((select_player) => {
                    select_player.appendChild(playerOption);
                })
            }else{
                option_player.value = inputPlayer;
                option_player.text = inputPlayer;
            }
        }

        // 出演者 追加
        // フォーム追加
        function addPlayer(){        
            current_player_number++; // id
            const formerNumber = current_player_number - 1; // ひとつ前のフォーム（コピーしたフォーム）のid番号
            // 要素をコピーする
            let copied = all_players_area.firstElementChild.cloneNode(true);
            copied.id = 'player_area_' + current_player_number; // コピーした要素のidを変更
            // コピーしてフォーム番号を変更した要素を親要素の一番最後の子要素にする
            all_players_area.appendChild(copied);
            // 出演者のnameを取得する     
            var copied_player_names = document.getElementsByName('player[]'); // 一度name属性を取得して、最後の要素のidを書き換える
            // 出演者のidを変更する
            const new_player_id = 'player_' + current_player_number; // 新しいplayerのid、文字＋計算はできない
            copied_player_names[(copied_player_names.length)-1].id = new_player_id; // 出演者のidを変更
            copied_player_names[(copied_player_names.length)-1].value = '';
        }
        // フォーム削除
        function dispPlayer(){
            if(current_player_number > 0){
                const remove_element = all_players_area.children[current_player_number];
                all_players_area.removeChild(remove_element);
                current_player_number--;
            }else{
                alert('フォームが1つの場合には削除できません。');
            }
        }

        // 出演者に対する感想 追加
        // フォーム追加
        function addImpression_Player(){        
            current_impression_player_number++; // id
            const formerNumber = current_impression_player_number - 1; // ひとつ前のフォーム（コピーしたフォーム）のid番号
            // 要素をコピーする
            let copied = all_impressions_player_area.firstElementChild.cloneNode(true);
            copied.id = 'impression_area_' + current_impression_player_number; // コピーした要素のidを変更
            // コピーしてフォーム番号を変更した要素を親要素の一番最後の子要素にする
            all_impressions_player_area.appendChild(copied);
            // 出演者のnameを取得する     
            var copied_player_impression_names = document.getElementsByName('player_impression[]'); // 一度name属性を取得して、最後の要素のidを書き換える
            // 出演者のidを変更する
            const new_player_impression_id = 'player_impression_' + current_impression_player_number; // 新しい出演に対する感想の出演者のid、文字＋計算はできない
            copied_player_impression_names[(copied_player_impression_names.length)-1].id = new_player_impression_id; // 出演者のidを変更
            copied_player_impression_names[(copied_player_impression_names.length)-1].value = '';
            // 感想のnameを取得する     
            var copied_impression_player_names = document.getElementsByName('impression_player[]'); // 一度name属性を取得して、最後の要素のidを書き換える
            // 感想のidを変更する
            const new_impression_player_id = 'impression_player_' + current_impression_player_number; // 新しい出演に対する感想の出演者のid、文字＋計算はできない
            copied_impression_player_names[(copied_impression_player_names.length)-1].id = new_impression_player_id; // 出演者のidを変更
            copied_impression_player_names[(copied_impression_player_names.length)-1].value = '';
            // 関数checkPlayerを動作させる
            select_players.push(document.querySelector('#'+ new_player_impression_id));
        }
        // フォーム削除
        function dispImpression_Player(){
            if(current_impression_player_number > 0){
                const remove_element = all_impressions_player_area.children[current_impression_player_number];
                all_impressions_player_area.removeChild(remove_element);
                current_impression_player_number--;
            }else{
                alert('フォームが1つの場合には削除できません。');
            }
        }

        // 好きな場面とその理由 追加
        // フォーム追加
        function addImpression_Scene(){        
            current_impression_scene_number++; // id
            const formerNumber = current_impression_scene_number - 1; // ひとつ前のフォーム（コピーしたフォーム）のid番号
            // 要素をコピーする
            let copied = all_impressions_scene_area.firstElementChild.cloneNode(true);
            copied.id = 'impression_scene_area_' + current_impression_scene_number; // コピーした要素のidを変更
            // コピーしてフォーム番号を変更した要素を親要素の一番最後の子要素にする
            all_impressions_scene_area.appendChild(copied);
            // 好きな場面のnameを取得する     
            var copied_scene_impression_names = document.getElementsByName('scene_impression[]'); // 一度name属性を取得して、最後の要素のidを書き換える
            // 好きな場面のidを変更する
            const new_scene_impression_id = 'scene_impression_' + current_impression_scene_number; // 好きな場面のid、文字＋計算はできない
            copied_scene_impression_names[(copied_scene_impression_names.length)-1].id = new_scene_impression_id; // 好きな場面のidを変更
            copied_scene_impression_names[(copied_scene_impression_names.length)-1].value = '';
            // 感想のnameを取得する     
            var copied_impression_scene_names = document.getElementsByName('impression_scene[]'); // 一度name属性を取得して、最後の要素のidを書き換える
            // 感想のidを変更する
            const new_impression_scene_id = 'impression_scene_' + current_impression_scene_number; // 新しい出演に対する感想の出演者のid、文字＋計算はできない
            copied_impression_scene_names[(copied_impression_scene_names.length)-1].id = new_impression_scene_id; // 出演者のidを変更
            copied_impression_scene_names[(copied_impression_scene_names.length)-1].value = '';
        }
        // フォーム削除
        function dispImpression_Scene(){
            if(current_impression_scene_number > 0){
                const remove_element = all_impressions_scene_area.children[current_impression_scene_number];
                all_impressions_scene_area.removeChild(remove_element);
                current_impression_scene_number--;
            }else{
                alert('フォームが1つの場合には削除できません。');
            }
        }

        // 関連のある公演 追加
        // フォーム追加
        function addRelated_Performance(){        
            current_related_performance_number++; // id
            const formerNumber = current_related_performance_number - 1; // ひとつ前のフォーム（コピーしたフォーム）のid番号
            // 要素をコピーする
            let copied = all_related_performances_area.firstElementChild.cloneNode(true);
            copied.id = 'related_performance_area_' + current_related_performance_number; // コピーした要素のidを変更
            // コピーしてフォーム番号を変更した要素を親要素の一番最後の子要素にする
            all_related_performances_area.appendChild(copied);
            // 出演者のnameを取得する     
            var copied_related_performance_names = document.getElementsByName('related_performances[]'); // 一度name属性を取得して、最後の要素のidを書き換える
            // 出演者のidを変更する
            const new_related_performance_id = 'related_performances_' + current_related_performance_number; // 新しい出演に対する感想の出演者のid、文字＋計算はできない
            copied_related_performance_names[(copied_related_performance_names.length)-1].id = new_related_performance_id; // 出演者のidを変更
            copied_related_performance_names[(copied_related_performance_names.length)-1].value = '';
        }
        // フォーム削除
        function dispRelated_Performance(){
            if(current_related_performance_number > 0){
                const remove_element = all_related_performances_area.children[current_related_performance_number];
                all_related_performances_area.removeChild(remove_element);
                current_related_performance_number--;
            }else{
                alert('フォームが1つの場合には削除できません。');
            }
        }
    </script>

</body>
</html>
