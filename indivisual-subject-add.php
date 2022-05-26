<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <title>追加 -観劇感想日記</title>
</meta>
</head>

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
    
    $sucess = "";
    $flag = 0;
    
    $players_number = 0; // 入力されている出演者数
    $impression_players_number = 0; // 入力されている出演者の感想数
    $impression_scenes_number = 0; // 入力されている好き場面数
    $related_performances_number = 0; // 選択されている関連のある公演数
    $drop_select_impression_players = []; // 出演者の感想用の出演者のドロップダウンメニュー（既に出演者の感想で選択された出演者がいる場合）（確認時に使用）
    $drop_impression_players = '<option value="">選択してください</option>'; // 出演者の感想用の出演者のドロップダウンメニュー（出演者の感想で選択された出演者がいない場合と新たに選択する用）
    $drop_select_related_performances = []; // 関連のある公演用のドロップダウンメニュー
    $drop_related_performances = '<option value="">選択してください</option>'; // 関連のある公演用のドロップダウンメニュー（選択された公演がない場合と新たに選択する用）

    $userid= isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : NULL; 
    $performances_row = isset($_SESSION['performances_title']) ? $_SESSION['performances_title'] : NULL;
    $performances_id_row = isset($_SESSION['performances_id']) ? $_SESSION['performances_id'] : NULL;
    $send_all_performances_number = json_encode(count($performances_row));
    $NULL = null; // 変数がない場合にNULLを送る
    $send_NULL = json_encode($NULL);

    if(isset($performances_row)){
        foreach($performances_row as $performances_row_key => $performance_row){
        // 新規追加用
            $drop_related_performances .= "<option value=".h($performances_id_row[$performances_row_key]).">{$performance_row}</option>";
        } 
    }
    
    
    $flag = 1;

    $now_date = date('Y-m-d');
        
    /**
     * 確認する（btn_confirm）を押した後の処理
    */
    if(isset($_POST['btn_confirm'])){
        $_SESSION['performance'] = isset($_POST['performance']) ? $_POST['performance'] : NULL;
        $_SESSION['theatrical_company'] = (isset($_POST['theatrical_company']) && $_POST['theatrical_company'] != '') ? $_POST['theatrical_company'] : NULL;
        $_SESSION['date'] = (isset($_POST['date']) && $_POST['date'] != '') ? $_POST['date'] : NULL;
        $_SESSION['open_time'] = (isset($_POST['open_time']) && $_POST['open_time'] != '') ? $_POST['open_time'] : NULL;
        $_SESSION['close_time'] = (isset($_POST['close_time']) && $_POST['close_time'] != '') ? $_POST['close_time'] : NULL;
        $_SESSION['theater'] = (isset($_POST['theater']) && $_POST['theater'] != '') ? $_POST['theater'] : NULL;
        $_SESSION['seat'] = (isset($_POST['seat']) && $_POST['seat'] != '') ? $_POST['seat'] : NULL;
        $_SESSION['first_date'] = (isset($_POST['first_date']) && $_POST['first_date'] != '') ? $_POST['first_date'] : NULL;
        $_SESSION['final_date'] = (isset($_POST['final_date']) && $_POST['final_date'] != '') ? $_POST['final_date'] : NULL;
        $_SESSION['organizer'] = (isset($_POST['organizer']) && $_POST['organizer'] != '') ? $_POST['organizer'] : NULL;
        $_SESSION['director'] = (isset($_POST['director']) && $_POST['director'] != '') ? $_POST['director'] : NULL;
        $_SESSION['author'] = (isset($_POST['author']) && $_POST['author'] != '') ? $_POST['author'] : NULL;
        $_SESSION['dance'] = (isset($_POST['dance']) && $_POST['dance'] != '') ? $_POST['dance'] : NULL;
        $_SESSION['music'] = (isset($_POST['music']) && $_POST['music'] != '') ? $_POST['music'] : NULL;
        $_SESSION['lyrics'] = (isset($_POST['lyrics']) && $_POST['lyrics'] != '') ? $_POST['lyrics'] : NULL;
        $_SESSION['costume'] = isset($_POST['costume']) ? $_POST['costume'] : NULL;
        $_SESSION['illumination'] = (isset($_POST['illumination']) && $_POST['illumination'] != '') ? $_POST['illumination'] : NULL;
        $_SESSION['property'] = (isset($_POST['property']) && $_POST['property'] != '') ? $_POST['property'] : NULL;
        $_SESSION['scenario'] = (isset($_POST['scenario']) && $_POST['scenario'] != '') ? $_POST['scenario']: NULL;
        $_SESSION['impression_all'] = (isset($_POST['impression_all']) && $_POST['impression_all'] != '') ? $_POST['impression_all'] : NULL;
        for($i = 0; $i < 50; $i++){
            $_SESSION['player'][$i] = (isset($_POST['player'][$i]) && $_POST['player'][$i] != '') ? $_POST['player'][$i] : NULL;
            if(isset($_SESSION['player'][$i])){
                $players_number++;
                if($i == 0){
                    $players_number--;
                }
                $drop_impression_players .= '<option value='.h($_SESSION['player'][$i]).'>'.h($_SESSION['player'][$i]).'</option>';
                $send_players_name[] = $_SESSION['player'][$i];
            }
            $_SESSION['player_impression'][$i] = (isset($_POST['player_impression'][$i]) && $_POST['player_impression'][$i] != '') ? $_POST['player_impression'][$i] : NULL;
            if(isset($_SESSION['player_impression'][$i])){
                $impression_players_number++;
                if($i == 0){
                    $impression_players_number--;
                }
            }
            $_SESSION['impression_player'][$i] = (isset($_POST['impression_player'][$i]) && $_POST['impression_player'][$i] != '') ? $_POST['impression_player'][$i] : NULL;
            $_SESSION['scene_impression'][$i] = (isset($_POST['scene_impression'][$i]) && $_POST['scene_impression'][$i] != '') ? $_POST['scene_impression'][$i] : NULL;
            if(isset($_SESSION['scene_impression'][$i])){
                $impression_scenes_number++;
                if($i == 0){
                    $impression_scenes_number--;
                }
            }
            $_SESSION['impression_scene'][$i] = (isset($_POST['impression_scene'][$i]) && $_POST['impression_scene'][$i] != '') ? $_POST['impression_scene'][$i] : NULL;
            if($i < 11){
                $_SESSION['related_performances_id'][$i] = (isset($_POST['related_performances'][$i]) && $_POST['related_performances'][$i] != '') ? (int)$_POST['related_performances'][$i] : NULL;
                if(isset($_SESSION['related_performances_id'][$i])){
                    foreach($performances_row as $performances_row_key => $performance_row){
                        if($_SESSION['related_performances_id'][$i] == $performances_id_row[$performances_row_key]){
                            $_SESSION['related_performances_title'][$i] = $performance_row;
                        }
                    }
                    $related_performances_number++;
                    if($i == 0){
                        $related_performances_number--;
                    }
                }
            }
        }
        if(isset($send_players_name)){
            $_SESSION['send_players'] = json_encode($send_players_name); // 入力された名前を送る
        }
        if(isset($_SESSION['player_impression'])){
            foreach($_SESSION['player_impression'] as $player_impression_key => $player_impression_name){
                if(isset($player_impression_name)){
                    $drop_select_impression_players[$player_impression_key] = "<option value=''>選択してください</option>";
                    foreach($_SESSION['player'] as $player_name){
                        if($player_impression_name == $player_name && isset($player_name)){
                            $drop_select_impression_players[$player_impression_key] .= '<option value='.h($player_name).' selected>'.h($player_name).'</option>';
                        }elseif(isset($player_name)){
                            $drop_select_impression_players[$player_impression_key] .= '<option value='.h($player_name).'>'.h($player_name).'</option>';
                        }
                    }
                    $send_impression_players_name[] = $player_impression_name;
                }
            }
            $_SESSION['drop_select_impression_players'] = $drop_select_impression_players;
            if(isset($send_impression_players_name)){
                $_SESSION['send_impression_players'] = json_encode($send_impression_players_name); // 選択された名前を送る
            }            
        }
        if(isset($_SESSION['related_performances_id'])){
            foreach($_SESSION['related_performances_id'] as $related_performance_id_key => $related_performance_id_value){
                if(isset($related_performance_id_value)){
                    $drop_select_related_performances[$related_performance_id_key] = "<option value=''>選択してください</option>";
                    foreach($performances_row as $performances_row_key => $performance_row){
                        if($related_performance_id_value == $performances_id_row[$performances_row_key]){
                            $drop_select_related_performances[$related_performance_id_key] .= "<option value=".h($performances_id_row[$performances_row_key])." selected>{$performance_row}</option>";
                        }else{
                            $drop_select_related_performances[$related_performance_id_key] .= "<option value=".h($performances_id_row[$performances_row_key]).">{$performance_row}</option>";
                        }
                        
                    } 
                }
            }                      
            $_SESSION['drop_select_related_performances'] = count($drop_select_related_performances) !== 0 ? $drop_select_related_performances : NULL;    
        }      
        $_SESSION['impression_final'] = (isset($_POST['impression_final']) && $_POST['impression_final'] != '') ? $_POST['impression_final'] : NULL;
        $_SESSION['players_number'] = $players_number;
        $_SESSION['impression_players_number'] = $impression_players_number;
        $_SESSION['impression_scenes_number'] = $impression_scenes_number;
        $_SESSION['related_performances_number'] = $related_performances_number;
    }    

    if(isset($_SESSION['players_number'])){
        $players_number = $_SESSION['players_number'];
    }
    if(isset($_SESSION['impression_players_number'])){
        $impression_players_number = $_SESSION['impression_players_number'];
    }
    if(isset($_SESSION['impression_scenes_number'])){
        $impression_scenes_number = $_SESSION['impression_scenes_number'];
    }
    if(isset($_SESSION['related_performances_number'])){
        $related_performances_number = $_SESSION['related_performances_number'];
    }
    $_SESSION['drop_impression_players'] = $drop_impression_players; // 出演者の感想用ドロップダウンメニュー
    $_SESSION['drop_related_performances'] = $drop_related_performances;
    $send_players_number = json_encode($players_number); // 出演者の最後のid番号
    $send_impression_players_number = json_encode($impression_players_number); // 出演者の感想の最後のid番号
    $send_impression_scenes_number = json_encode($impression_scenes_number); // 好きな場面の最後のid番号
    $send_related_performances_number = json_encode($related_performances_number); // 関連のある公演の最後のid番号
         
    /**
      * 登録する(btn_submit)を押した後の処理
    */
    if(isset($_POST["btn_submit"])){
        $userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : NULL;
        if($userid !== NULL){
            $performance = $_SESSION['performance'];
            $theatrical_company = $_SESSION['theatrical_company'];
            $date = $_SESSION['date'];
            $open_time = $_SESSION['open_time'];
            $close_time = $_SESSION['close_time'];
            $theater = $_SESSION['theater'];
            $seat = $_SESSION['seat'];
            $first_date = $_SESSION['first_date'];
            $final_date = $_SESSION['final_date'];
            $organizer = $_SESSION['organizer'];
            $director  = $_SESSION['director'];
            $author = $_SESSION['author'];
            $dance = $_SESSION['dance'];
            $music = $_SESSION['music'];
            $lyrics = $_SESSION['lyrics'];
            $costume = $_SESSION['costume'];
            $illumination = $_SESSION['illumination'];
            $property = $_SESSION['property'];
            $scenario = $_SESSION['scenario'];
            $impression_all = $_SESSION['impression_all'];
            for($i = 1; $i < 51; $i++){
                $player[$i] = $_SESSION["player"][$i-1];
                $player_impression[$i] = $_SESSION["player_impression"][$i-1];
                $impression_player[$i] = $_SESSION["impression_player"][$i-1];
                $scene_impression[$i] = $_SESSION["scene_impression"][$i-1];
                $impression_scene[$i] = $_SESSION["impression_scene"][$i-1];
                if($i < 11){
                    $related_performances_id[$i] = $_SESSION["related_performances_id"][$i-1];
                }
            }
            $impression_final = $_SESSION['impression_final'];
                 
            try{
                $sql_add = $pdo -> prepare("INSERT INTO impression 
                                            (userid, performance, theatrical_company, date, open_time, close_time, theater, seat,
                                             first_date, final_date, organizer, director, author, dance,
                                             music, lyrics, costume, illumination, property, 
                                             player_1, player_2, player_3, player_4, player_5, player_6, player_7, player_8, player_9, player_10, 
                                             player_11, player_12, player_13, player_14, player_15, player_16, player_17, player_18, player_19, player_20, 
                                             player_21, player_22, player_23, player_24, player_25, player_26, player_27, player_28, player_29, player_30, 
                                             player_31, player_32, player_33, player_34, player_35, player_36, player_37, player_38, player_39, player_40, 
                                             player_41, player_42, player_43, player_44, player_45, player_46, player_47, player_48, player_49, player_50,
                                             scenario, impression_all,
                                             player_impression_1, player_impression_2, player_impression_3, player_impression_4, player_impression_5, player_impression_6, player_impression_7, player_impression_8, player_impression_9, player_impression_10, 
                                             player_impression_11, player_impression_12, player_impression_13, player_impression_14, player_impression_15, player_impression_16, player_impression_17, player_impression_18, player_impression_19, player_impression_20, 
                                             player_impression_21, player_impression_22, player_impression_23, player_impression_24, player_impression_25, player_impression_26, player_impression_27, player_impression_28, player_impression_29, player_impression_30, 
                                             player_impression_31, player_impression_32, player_impression_33, player_impression_34, player_impression_35, player_impression_36, player_impression_37, player_impression_38, player_impression_39, player_impression_40, 
                                             player_impression_41, player_impression_42, player_impression_43, player_impression_44, player_impression_45, player_impression_46, player_impression_47, player_impression_48, player_impression_49, player_impression_50,
                                             impression_player_1, impression_player_2, impression_player_3, impression_player_4, impression_player_5, impression_player_6, impression_player_7, impression_player_8, impression_player_9, impression_player_10, 
                                             impression_player_11, impression_player_12, impression_player_13, impression_player_14, impression_player_15, impression_player_16, impression_player_17, impression_player_18, impression_player_19, impression_player_20, 
                                             impression_player_21, impression_player_22, impression_player_23, impression_player_24, impression_player_25, impression_player_26, impression_player_27, impression_player_28, impression_player_29, impression_player_30, 
                                             impression_player_31, impression_player_32, impression_player_33, impression_player_34, impression_player_35, impression_player_36, impression_player_37, impression_player_38, impression_player_39, impression_player_40, 
                                             impression_player_41, impression_player_42, impression_player_43, impression_player_44, impression_player_45, impression_player_46, impression_player_47, impression_player_48, impression_player_49, impression_player_50, 
                                             scene_impression_1, scene_impression_2, scene_impression_3, scene_impression_4, scene_impression_5, scene_impression_6, scene_impression_7, scene_impression_8, scene_impression_9, scene_impression_10, 
                                             scene_impression_11, scene_impression_12, scene_impression_13, scene_impression_14, scene_impression_15, scene_impression_16, scene_impression_17, scene_impression_18, scene_impression_19, scene_impression_20, 
                                             scene_impression_21, scene_impression_22, scene_impression_23, scene_impression_24, scene_impression_25, scene_impression_26, scene_impression_27, scene_impression_28, scene_impression_29, scene_impression_30, 
                                             scene_impression_31, scene_impression_32, scene_impression_33, scene_impression_34, scene_impression_35, scene_impression_36, scene_impression_37, scene_impression_38, scene_impression_39, scene_impression_40, 
                                             scene_impression_41, scene_impression_42, scene_impression_43, scene_impression_44, scene_impression_45, scene_impression_46, scene_impression_47, scene_impression_48, scene_impression_49, scene_impression_50, 
                                             impression_scene_1, impression_scene_2, impression_scene_3, impression_scene_4, impression_scene_5, impression_scene_6, impression_scene_7, impression_scene_8, impression_scene_9, impression_scene_10, 
                                             impression_scene_11, impression_scene_12, impression_scene_13, impression_scene_14, impression_scene_15, impression_scene_16, impression_scene_17, impression_scene_18, impression_scene_19, impression_scene_20, 
                                             impression_scene_21, impression_scene_22, impression_scene_23, impression_scene_24, impression_scene_25, impression_scene_26, impression_scene_27, impression_scene_28, impression_scene_29, impression_scene_30, 
                                             impression_scene_31, impression_scene_32, impression_scene_33, impression_scene_34, impression_scene_35, impression_scene_36, impression_scene_37, impression_scene_38, impression_scene_39, impression_scene_40, 
                                             impression_scene_41, impression_scene_42, impression_scene_43, impression_scene_44, impression_scene_45, impression_scene_46, impression_scene_47, impression_scene_48, impression_scene_49, impression_scene_50,                                                     
                                             impression_final,
                                             related_performance_1, related_performance_2, related_performance_3, related_performance_4, related_performance_5, related_performance_6, related_performance_7, related_performance_8, related_performance_9, related_performance_10)
                                            VALUES (:userid, :performance, :theatrical_company, :date, :open_time, :close_time, :theater, :seat,
                                                    :first_date, :final_date, :organizer, :director, :author, :dance,
                                                    :music, :lyrics, :costume, :illumination, :property,
                                                    :player_1, :player_2, :player_3, :player_4, :player_5, :player_6, :player_7, :player_8, :player_9, :player_10, 
                                                    :player_11, :player_12, :player_13, :player_14, :player_15, :player_16, :player_17, :player_18, :player_19, :player_20, 
                                                    :player_21, :player_22, :player_23, :player_24, :player_25, :player_26, :player_27, :player_28, :player_29, :player_30, 
                                                    :player_31, :player_32, :player_33, :player_34, :player_35, :player_36, :player_37, :player_38, :player_39, :player_40, 
                                                    :player_41, :player_42, :player_43, :player_44, :player_45, :player_46, :player_47, :player_48, :player_49, :player_50, 
                                                    :scenario, :impression_all,
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
                                                    :scene_impression_1, :scene_impression_2, :scene_impression_3, :scene_impression_4, :scene_impression_5, :scene_impression_6, :scene_impression_7, :scene_impression_8, :scene_impression_9, :scene_impression_10, 
                                                    :scene_impression_11, :scene_impression_12, :scene_impression_13, :scene_impression_14, :scene_impression_15, :scene_impression_16, :scene_impression_17, :scene_impression_18, :scene_impression_19, :scene_impression_20, 
                                                    :scene_impression_21, :scene_impression_22, :scene_impression_23, :scene_impression_24, :scene_impression_25, :scene_impression_26, :scene_impression_27, :scene_impression_28, :scene_impression_29, :scene_impression_30, 
                                                    :scene_impression_31, :scene_impression_32, :scene_impression_33, :scene_impression_34, :scene_impression_35, :scene_impression_36, :scene_impression_37, :scene_impression_38, :scene_impression_39, :scene_impression_40, 
                                                    :scene_impression_41, :scene_impression_42, :scene_impression_43, :scene_impression_44, :scene_impression_45, :scene_impression_46, :scene_impression_47, :scene_impression_48, :scene_impression_49, :scene_impression_50, 
                                                    :impression_scene_1, :impression_scene_2, :impression_scene_3, :impression_scene_4, :impression_scene_5, :impression_scene_6, :impression_scene_7, :impression_scene_8, :impression_scene_9, :impression_scene_10, 
                                                    :impression_scene_11, :impression_scene_12, :impression_scene_13, :impression_scene_14, :impression_scene_15, :impression_scene_16, :impression_scene_17, :impression_scene_18, :impression_scene_19, :impression_scene_20, 
                                                    :impression_scene_21, :impression_scene_22, :impression_scene_23, :impression_scene_24, :impression_scene_25, :impression_scene_26, :impression_scene_27, :impression_scene_28, :impression_scene_29, :impression_scene_30, 
                                                    :impression_scene_31, :impression_scene_32, :impression_scene_33, :impression_scene_34, :impression_scene_35, :impression_scene_36, :impression_scene_37, :impression_scene_38, :impression_scene_39, :impression_scene_40, 
                                                    :impression_scene_41, :impression_scene_42, :impression_scene_43, :impression_scene_44, :impression_scene_45, :impression_scene_46, :impression_scene_47, :impression_scene_48, :impression_scene_49, :impression_scene_50,                                                     
                                                    :impression_final,
                                                    :related_performance_1, :related_performance_2, :related_performance_3, :related_performance_4, :related_performance_5, :related_performance_6, :related_performance_7, :related_performance_8, :related_performance_9, :related_performance_10)");
              
                $sql_add -> bindParam(':userid', $userid, PDO::PARAM_INT);
                $sql_add -> bindParam(':performance', $performance, PDO::PARAM_STR);
                $sql_add -> bindParam(':theatrical_company', $theatrical_company, PDO::PARAM_STR);
                $sql_add -> bindParam(':date', $date, PDO::PARAM_STR);
                $sql_add -> bindParam(':open_time', $open_time, PDO::PARAM_STR);
                $sql_add -> bindParam(':close_time', $close_time, PDO::PARAM_STR);
                $sql_add -> bindParam(':theater', $theater, PDO::PARAM_STR);
                $sql_add -> bindParam(':seat', $seat, PDO::PARAM_STR);
                $sql_add -> bindParam(':first_date', $first_date, PDO::PARAM_STR);
                $sql_add -> bindParam(':final_date', $final_date, PDO::PARAM_STR);
                $sql_add -> bindParam(':organizer', $organizer, PDO::PARAM_STR);
                $sql_add -> bindParam(':director', $director, PDO::PARAM_STR);
                $sql_add -> bindParam(':author', $author, PDO::PARAM_STR);
                $sql_add -> bindParam(':dance', $dance, PDO::PARAM_STR);
                $sql_add -> bindParam(':music', $music, PDO::PARAM_STR);
                $sql_add -> bindParam(':lyrics', $lyrics, PDO::PARAM_STR);
                $sql_add -> bindParam(':costume', $costume, PDO::PARAM_STR);
                $sql_add -> bindParam(':illumination', $illumination, PDO::PARAM_STR);
                $sql_add -> bindParam(':property', $property, PDO::PARAM_STR);
                $sql_add -> bindParam(':scenario', $scenario, PDO::PARAM_STR);
                $sql_add -> bindParam(':impression_all', $impression_all, PDO::PARAM_STR);
                for($i=1; $i<51; $i++){
                    $sql_add -> bindParam(":player_{$i}", $player[$i], PDO::PARAM_STR);
                    $sql_add -> bindParam(":player_impression_{$i}", $player_impression[$i], PDO::PARAM_STR);
                    $sql_add -> bindParam(":impression_player_{$i}", $impression_player[$i], PDO::PARAM_STR);
                    $sql_add -> bindParam(":scene_impression_{$i}", $scene_impression[$i], PDO::PARAM_STR);
                    $sql_add -> bindParam(":impression_scene_{$i}", $impression_scene[$i], PDO::PARAM_STR);
                    if($i<11){
                        $sql_add -> bindParam("related_performance_{$i}", $related_performances_id[$i], PDO::PARAM_INT);
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
    <div class='area area-add'>
        <div class='book-area book-area-add'>
            <div class='book-page-area'>
                <!-- page3 完了画面 -->
                <?php if(count($errors) === 0 && isset($_POST['btn_submit'])): ?>
                <?php echo $sucess.PHP_EOL; ?>
                <p>ホーム<a href="m6-indivisual-home.php">こちら</a></p>
   
                <!-- page2 確認画面 -->
                <?php elseif(count($errors) === 0 && isset($_POST['btn_confirm'])): ?>
                <form action="" method="post" enctype="multipart/form-data">
		            <p>公演：<?=h($_SESSION['performance'])?></p>
     			    <p>劇団：<?=h($_SESSION['theatrical_company'])?></p>
     	     		<p>観劇日：<?=h($_SESSION['date'])?></p>
	    	    	<p>開演時刻：<?=h($_SESSION['open_time'])?> ~ 終演時刻：<?=h($_SESSION['close_time'])?></p>
    	    		<p>観劇した劇場：<?=h($_SESSION['theater'])?></p>
	    	    	<p>座席：<?=h($_SESSION['seat'])?></p>
                    <p>公演期間：<?=h($_SESSION['first_date'])?> ~ <?=h($_SESSION['final_date'])?></p>
     		    	<p>主催：<?=h($_SESSION['organizer'])?></p>
	    		    <p>演出：<?=h($_SESSION['director'])?></p>
    	    		<p>作家：<?=h($_SESSION['author'])?></p>
	    	    	<p>振付：<?=h($_SESSION['dance'])?></p>
		            <p>音楽：<?=h($_SESSION['music'])?></p>
    			    <p>作詞：<?=h($_SESSION['lyrics'])?></p>
        			<p>衣装：<?=h($_SESSION['costume'])?></p>
	        		<p>照明：<?=h($_SESSION['illumination'])?></p>
		        	<p>小道具：<?=h($_SESSION['property'])?></p>			    
    			    <p>出演者：</p>
                    <div>
                        <?php for($i=0; $i<50; $i++){ 
		    	        if(isset($_SESSION['player'][$i])): ?>
			                <?=h($_SESSION['player'][$i])?> <?php echo ' '; ?>
				        <?php  else :
				               break;
     			    	endif; 
	     		        } ?>
                    </div>
             
	     	    	<p>あらすじ：<?=h($_SESSION['scenario'])?></p>
		    	    <p>全体について：<?=h($_SESSION['impression_all'])?></p>
     			    <?php for($i=0; $i<50; $i++){ 
	    		    if(isset($_SESSION['player_impression'][$i])): ?>
		     	    <p>出演者について感想：<?=h($_SESSION['player_impression'][$i])?> 
			    	   出演者に対するコメント：<?=h($_SESSION['impression_player'][$i])?></p>
				    <?php  else :
				           break;
     				endif; 
         			} ?>
	        		<?php for ($i=0; $i<50; $i++){ 
		            if(isset($_SESSION['scene_impression'][$i])): ?>
		            <p>好きな場面：<?=h($_SESSION['scene_impression'][$i])?>
                       感想：<?=h($_SESSION['impression_scene'][$i])?></p>
                    <?php  else :
                           break;
                    endif; 
                    } ?>
    	   		    <p>最後に：<?=h($_SESSION['impression_final'])?></p>
        	   		<?php for($i=0; $i<10; $i++){ 
	                if(isset($_SESSION['related_performances_id'][$i])): ?>
	                <p>関連のある公演：<?=h($_SESSION['related_performances_title'][$i])?></p>
		            <?php  else :
	                        break;
     	     	    endif;
	      	    	} ?>
             
                    <input type="submit" name="btn_back" value="戻る">
                    <input type="submit" name="btn_submit" value="登録する">
                </form>
   
                <!-- page1 登録画面 -->
                <?php if(count($errors) > 0):?>
                <?php foreach($errors as $value){
                    echo "<p class='error'>".$value."</p>";
                } ?>
                <?php endif; ?>
                <?php elseif($flag === 1 || isset($_POST['btn_back'])): ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <label>
                        <input type='checkbox' class='add-book-page-area-input' checked disabled>
                        <span class='page page-add page-right' style='z-index:200;'></span>
                        <span id='page_1' class='page page-add page-left page-add-first-left'>
                            <ul class='note note-add'>                    
     		                    <li>公演：<input type="text" name="performance" value="<?php if( !empty($_SESSION['performance']) ){ echo h($_SESSION['performance']); } ?>" required></li>
                 	    	    <li>劇団：<input type="text" name="theatrical_company" value="<?php if( !empty($_SESSION['theatrical_company']) ){ echo h($_SESSION['theatrical_company']); } ?>"></li>
	    	                    <li>観劇日：<input type="date" name="date" value="<?php if( !empty($_SESSION['date']) ){ echo h($_SESSION['date']); }else{ echo h($now_date); } ?>"></li>
            	    	        <li>公演時間：<input type="time" name="open_time" value="<?php if( !empty($_SESSION['open_time']) ){ echo h($_SESSION['open_time']); }else{ echo "13:00"; } ?>"> ~
		                                     <input type="time" name="close_time" value="<?php if( !empty($_SESSION['close_time']) ){ echo h($_SESSION['close_time']); }else{ echo "16:00"; } ?>"></li>
             	    	        <li>劇場：<input type="text" name="theater" value="<?php if( !empty($_SESSION['theater']) ){ echo h($_SESSION['theater']); } ?>"></li>
         		                <li>座席：<input type="text" name="seat" value="<?php if(!empty($_SESSION['seat'])){ echo h($_SESSION['seat']); } ?>"></;>
                 		        <li>期間：<input type="date" name="first_date" class='add-period' value="<?php if( !empty($_SESSION['first_date']) ){ echo h($_SESSION['first_date']); } ?>"> ~
		                                  <input type="date" name="final_date" class='add-period' value="<?php if( !empty($_SESSION['final_date']) ){ echo h($_SESSION['final_date']); } ?>"></li>
             	        	    <li>主催：<input type="text" name="organizer" value="<?php if( !empty($_SESSION['organizer']) ){ echo h($_SESSION['organizer']); } ?>"></li>
	    	                    <li>演出：<input type="text" name="director" value="<?php if( !empty($_SESSION['director']) ){ echo h($_SESSION['director']); } ?>"></li>
            		            <li>作家：<input type="text" name="author" value="<?php if( !empty($_SESSION['author']) ){ echo h($_SESSION['author']); } ?>"></li>
    	         	            <li>振付：<input type="text" name="dance" value="<?php if( !empty($_SESSION['dance']) ){ echo h($_SESSION['dance']); } ?>"></li>
                                <li>音楽：<input type="text" name="music" value="<?php if( !empty($_SESSION['music']) ){ echo h($_SESSION['music']); } ?>"></li>
                                <li>作詞：<input type="text" name="lyrics" value="<?php if( !empty($_SESSION['lyrics']) ){ echo h($_SESSION['lyrics']); } ?>"></li>
         	     	            <li>衣装：<input type="text" name="costume" value="<?php if( !empty($_SESSION['costume']) ){ echo h($_SESSION['costume']); } ?>"></li>
		                        <li>照明：<input type="text" name="illumination" value="<?php if( !empty($_SESSION['illumination']) ){ echo h($_SESSION['illumination']); } ?>"></li>
                     		    <li>小道具：<input type="text" name="property" value="<?php if( !empty($_SESSION['property']) ){ echo h($_SESSION['property']); } ?>"></li>
                            </ul>
                        </span>
                    </label>
                    <label>
                        <input type='checkbox' class='add-book-page-area-input'>
                        <span  id='page_2' class='page page-add page-right' style='z-index:100;'>
                            <ul class='note note-add'>
                                <li>出演者：</li>
                                <div class='all-players-area'>
                                    <?php for($i=0; $i<$players_number+1; $i++){ ?>
                                    <li id='player_area_<?php echo $i; ?>'>
                                        <input type="text" name="player[]" id="player_<?php echo $i; ?>" onkeyup="checkPlayer()" value="<?php if(!empty($_SESSION['player'][$i]) ){echo h($_SESSION['player'][$i]); }?>">
                                    </li>           
                                    <?php } ?>
                                </div>
                                <li>
                                    <input type="button" id="add_player" value='+' onclick="addPlayer()">
                                    <input type="button" id="disp_player" value='-' onclick="dispPlayer()">
                                </li>
                    			<div class='add-set-area'>
                                    <li class='add-textarea-area'>
                                        あらすじ：<textarea name="scenario" class='add-textarea' value="<?php if( !empty($_SESSION['scenario']) ){ echo h($_SESSION['scenario']); } ?>"><?php if( !empty($_SESSION['scenario']) ){ echo h($_SESSION['scenario']); } ?></textarea>
                                    </li>
                                    <li></li>
                                </div>                                
                    			<div class='add-set-area'>
                                    <li class='add-textarea-area'>
                                        全体について：<textarea name="impression_all" class='add-textarea' value="<?php if( !empty($_SESSION['impression_all']) ){ echo h($_SESSION['impression_all']); } ?>"><?php if( !empty($_SESSION['impression_all']) ){ echo h($_SESSION['impression_all']); } ?></textarea>
                                    </li>
                                    <li></li>
                                </div>
                                <div class='add-set-all-area'>
                                    <li>出演者に対する感想：</li>
                                    <div class='all-impressions-player-area'>
                                        <?php for($i=0; $i<$impression_players_number+1; $i++){ ?>
                                        <div id='impression_area_<?php echo $i; ?>' class='add-set-area'>
                                            <li>
                                                出演者：<select name='player_impression[]' id='player_impression_<?php echo $i; ?>' onchange="choosePlayer()">
                                                <?php if(isset($_SESSION['drop_select_impression_players'])){
                                                    echo $_SESSION['drop_select_impression_players'][$i];
                                                }else{
                                                   echo $_SESSION['drop_impression_players'];
                                                } ?>
                                                </select>
                                            </li>
	    		                            <li>
                                                <div class='add-textarea-area'>
                                                    コメント：<textarea name="impression_player[]" id='impression_player_<?php echo $i; ?>' class='add-textarea' value="<?php if( !empty($_SESSION['impression_player'][$i]) ){ echo h($_SESSION['impression_player'][$i]); } ?>"><?php if( !empty($_SESSION['impression_player'][$i]) ){ echo h($_SESSION['impression_player'][$i]); } ?></textarea>
                                                </div>
                                            </li>
                                            <li></li>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <li>
                                        <input type="button" id="add_impression_player" value='+' onclick="addImpression_Player()">
                                        <input type="button" id="disp_impression_player" value='-' onclick="dispImpression_Player()">
                                    </li>
                                </div>                            
                                
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>            
                            </ul>
                        </span>
                        <span id='page_3' class='page page-add page-left'>
                            <ul class='note note-add'>
                                <div class='add-set-all-area'>
                                    <li>好きな場面とその理由：</li>
                                        <div class='all-impressions-scene-area'>
                                        <?php for($i=0; $i<$impression_scenes_number+1; $i++){ ?>
                                            <div id='impression_scene_area_<?php echo $i; ?>' class='add-set-area'>
                                                <li>
                                                    場面：<input type='text' name='scene_impression[]' value='<?php if( !empty($_SESSION['scene_impression'][$i]) ){ echo h($_SESSION['scene_impression'][$i]); } ?>'>
                                                </li>
                                                <li>
                                                    <div class='add-textarea-area'>
                                                        コメント：<textarea name="impression_scene[]" class='add-textarea' value="<?php if( !empty($_SESSION['impression_scene'][$i]) ){ echo h($_SESSION['impression_scene'][$i]); } ?>"><?php if( !empty($_SESSION['impression_scene'][$i]) ){ echo h($_SESSION['impression_scene'][$i]); } ?></textarea>
                                                    </div>                                            
                                                </li>
                                                <li></li>
                                            </div>
                                        <?php } ?>                                        
                                        </div>
                                    <li>
                                        <input type="button" id="add_impression_scene" type="button" value='+' onclick="addImpression_Scene()">
                                        <input type="button" id="disp_impression_scene" value='-' onclick="dispImpression_Scene()">
                                    </li>
                                </div>
                            
                    	   		<div class='add-set-area'>
                                    <li class='add-textarea-area'>
                                        最後に：<textarea name="impression_final" class='add-textarea' value="<?php if( !empty($_SESSION['impression_final']) ){ echo h($_SESSION['impression_final']); } ?>"></textarea>
                                    </li>
                                    <li></li>
                                </div>
                                <div class='add-set-all-area'>
                                    <li>関連のある公演：</li>
                                        <div class='all-related-performances-area'>
                                            <?php for($i=0; $i<$related_performances_number+1; $i++){ ?>
                                            <li id='related_performance_area_<?php echo $i; ?>' class='add-set-area'>
	     	         	                        <select name='related_performances[]' id='related_performances_<?php echo $i; ?>'>
                                                <?php if(isset($_SESSION['drop_select_related_performances'])){
                                                    echo $_SESSION['drop_select_related_performances'][$i];
                                                }else{
                                                    echo $_SESSION['drop_related_performances'];
                                                } ?>
                                                </select>
                                            </li>                
                                            <?php } ?>                
                                        </div>
                                    <li>
                                        <input type="button" id="add_related_performance" value="+" onclick="addRelated_Performance()">
                                        <input type="button" id="disp_related_performance" value="-" onclick="dispRelated_Performance()"><br>
                                    </li>
                                </div>                         
                	      		
                                <div class='add-set-area'>
                                    <li>
                                        <input type="submit" name="btn_confirm" value="確認する">
                                    </li>
                                </div>
                                
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                            </ul>
                        </span>
                    </label>
		        </form>
	            <?php endif; ?>
            </div>
        </div>
        <form method='post' name='return_home' action='m6-indivisual-home.php'>
            <a href="m6-indivisual-home.php" onClick="document.return_home.submit();return false">ホーム</a>
            <input type='hidden' name='return_home_index'>
        </form>
    </div>
 
	<script>
        let select_players = [].slice.call(document.querySelectorAll('[id^="player_impression"]')); // 出演者を入力したら、出演者のプルダウンメニューが増える。
        // フォーム追加
        let current_player_number = JSON.parse('<?php echo $send_players_number; ?>'); // 出演者の最後のid番号
        let current_impression_player_number = JSON.parse('<?php echo $send_impression_players_number; ?>'); // 出演者に関する感想の最後のid番号
        let current_impression_scene_number = JSON.parse('<?php echo $send_impression_scenes_number; ?>'); // シーンごとの感想の最後のid番号
        let current_related_performance_number = JSON.parse('<?php echo $send_related_performances_number; ?>'); // 関連する舞台の最後のid番号
        const all_performances_number = JSON.parse('<?php echo $send_all_performances_number; ?>'); // 登録されている舞台の数

        let option_players_name = JSON.parse('<?php if(isset($_SESSION['send_players'])){ echo $_SESSION['send_players']; }else{ echo $send_NULL; } ?>') != null ? JSON.parse('<?php if(isset($_SESSION['send_players'])){ echo $_SESSION['send_players']; }else{ echo $send_NULL; } ?>') : []; // optionを構成
        let select_players_name = JSON.parse('<?php if(isset($_SESSION['send_impression_players'])){ echo $_SESSION['send_impression_players']; }else{ echo $send_NULL; } ?>') != null ? JSON.parse('<?php if(isset($_SESSION['send_impression_players'])){ echo $_SESSION['send_impression_players']; }else{ echo $send_NULL; } ?>') : []; // 選択された名前たち

        // 入力された名前をプルダウンメニューに追加
        function checkPlayer() {
            const str = event.currentTarget.id;
            const id_number = str.replace('player_', '');
            const player_id = 'player_'+ id_number;
            var inputPlayer = document.getElementById(player_id).value;
            option_players_name[id_number] = inputPlayer;
            addOptionPlayers();
        }
        function addOptionPlayers(){
            select_players.forEach((select_player, number) => {
                select_player.innerHTML = '';
                let playerOption = document.createElement('option');
                playerOption.innerHTML = '選択してください';
                select_player.appendChild(playerOption);
                option_players_name.forEach((name) => {
                    let playerOption = document.createElement('option');
                    playerOption.setAttribute('value',name);
                    playerOption.setAttribute('text',name);
                    if(name == select_players_name[number]){
                      playerOption.setAttribute('selected', 'true'); //プルダウンメニューで選択された名前だった場合selected
                    }
                    playerOption.innerHTML = name;
                    select_player.appendChild(playerOption);
                })
            })
        }

        // プルダウンメニューで選択された名前を取得
        function choosePlayer(){
            const id = event.currentTarget.id;
            const id_number = id.replace('player_impression_', '');
            const value = event.currentTarget.value;
            select_players_name[id_number] = value;
        }

        // フォーム追加時にページをまたいでフォーム移動
        function moveElement_forth(element, span_id_former, element_count, area_number){ // 移動させたい要素, 移動前のspanのid, 移動させたい要素の行, ulの何番目の要素とするか
            const span_id_former_number = Number(span_id_former.replace(/[^0-9]/g, '')); // class=add-set-areaの中で一番最後のidの番号を取得する
            const span_id_newer_number = span_id_former_number + 1;
            const span_id_new = 'page_' + span_id_newer_number;
            let span = document.getElementById(span_id_new);
            if(span == null){
                if(document.getElementById(span_id_former).className.indexOf('page-right') == -1){
                    // 次作ページは右側&左側
                    const new_label = document.createElement('label');
                    const new_input = document.createElement('input');
                    new_input.type = 'checkbox';
                    new_input.className = 'add-book-page-area-input';

                    const new_span_right = document.createElement('span');
                    new_span_right.id = span_id_new;
                    new_span_right.classList.add('page', 'page-add', 'page-right');
                    new_span_right.style.zIndex = getComputedStyle(document.getElementById(span_id_former).previousElementSibling).zIndex - 1 ;
                    const new_span_left = document.createElement('span');
                    const span_id_newer_left_number = span_id_newer_number + 1;
                    const span_id_new_left = 'page_' + span_id_newer_left_number;
                    new_span_left.id = span_id_new_left;
                    new_span_left.classList.add('page', 'page-add', 'page-left');

                    const new_ul_right = document.createElement('ul');
                    new_ul_right.classList.add('note', 'note-add');
                    const new_ul_left = document.createElement('ul');
                    new_ul_left.classList.add('note', 'note-add');
                    for(let i=0; i<16; i++){
                        const new_li_right = document.createElement('li');
                        new_ul_right.appendChild(new_li_right);
                        const new_li_left = document.createElement('li');
                        new_ul_left.appendChild(new_li_left);
                    }
                    new_span_right.appendChild(new_ul_right); // 同じものは追加できなかった
                    new_span_left.appendChild(new_ul_left);
                    new_label.appendChild(new_input);
                    new_label.appendChild(new_span_right);
                    new_label.appendChild(new_span_left);
                    document.getElementById(span_id_former).parentElement.parentElement.appendChild(new_label);

                    span = document.getElementById(span_id_new);
                }
            }

            const span_id_li= '#'+ span_id_new +' li';  // 出演者追加ボタンのspanの最後のliを取得するための引数
            const lis = document.querySelectorAll(span_id_li); // 出演者追加ボタンのspan内の最後のli
            
            for(let i=1; i<=element_count; i++){
                if(lis[16-i].parentElement === span.children[0]){
                    // 最後のliが調整用の場合
                    lis[lis.length-i].remove(); // span内の最後のliを削除
                }else{
                    // 出演者追加ボタンのあるspan内の最後のidが調整用liでない場合
                    const span_id_add_set_area_last = '#'+ span_id_new +' .add-set-area';  // 出演者追加ボタンのspanの最後のadd-set-areaクラスを取得するための引数
                    const last_add_set_area = document.querySelectorAll(span_id_add_set_area_last); // 出演者追加ボタンのspan内の最後のadd-set-areaクラス

                    const add_set_area_number = last_add_set_area[last_add_set_area.length-1].id.replace(/[^0-9]/g, ''); // class=add-set-areaの中で一番最後のidの番号を取得する
                    
                    if(add_set_area_number === String(0)){
                        // span内の最後の要素が出演者への感想や好きな場面、関連のある公演の場合の最初（idが0）である場合
                        const span_id_add_set_all_area_last = '#'+ span_id_new +' .add-set-all-area:last-child';  // 出演者追加ボタンのspanの最後のadd-set-all-areaクラスを取得するための引数
                        const last_add_set_all_area = document.querySelectorAll(span_id_add_set_all_area_last); // 出演者追加ボタンのspan内の最後のadd-set-all-areaクラス
                        last_add_set_all_area[0].remove();
                        const last_add_set_all_area_id_li_s = last_add_set_all_area[0].getElementsByTagName('li'); // add-set-all-areaクラス内のli
                        for(let i=1; i<last_add_set_all_area_id_li_s.length; i++){
                            const li = document.createElement('li');
                            const ul = span.children;
                            ul.item(0).appendChild(li);
                        }
                        moveElement_forth(last_add_set_all_area[0], span_id_new, last_add_set_all_area_id_li_s.length, 0); // 0はulの最初の子要素として
                    }else{
                        //上記以外の場合
                        last_add_set_area_parent_id = last_add_set_area[last_add_set_area.length-1].parentElement.id;
                        last_add_set_area[last_add_set_area.length-1].remove();
                        const last_add_set_area_id_li_s = last_add_set_area[last_add_set_area.length-1].getElementsByTagName('li'); // add-set-areaクラス内のli
                        for(let i=1; i<last_add_set_area_id_li_s.length; i++){
                            const li = document.createElement('li');
                            const ul = span.children;
                            ul.item(0).appendChild(li);
                        }
                        if(last_add_set_area_parent_id == ''){
                            moveElement_forth(last_add_set_area[last_add_set_area.length-1], span_id_new, last_add_set_area_id_li_s.length, 0); // 0はulの最初の子要素として
                        }else{
                            moveElement_forth(last_add_set_area[last_add_set_area.length-1], span_id_new, last_add_set_area_id_li_s.length, 1); // 1はadd-set-all-areaクラスの最後の子要素として
                        }                     
                    }
                }
            }

            const span_new = document.getElementById(span_id_new);
            const ul_new = span_new.firstElementChild;
            const ul_firstElement = ul_new.firstElementChild;
            
            if(area_number == 0){
                // ulの最初の子要素として追加                
                ul_firstElement.parentNode.insertBefore(element, ul_firstElement);
            }else if(area_number == 1){
                // add-set-all-areaクラスの最後の子要素として追加
                // ここから編集
            }else{
                // ulの2番目の子要素として追加
                ul_firstElement.parentNode.insertBefore(element, ul_firstElement.nextSibling);
            }
        }

        // フォーム削除時にページを跨いでフォーム移動
        function moveElement_back(element, span_id_origin, element_count, li_start, li_count){ // 移動させたい要素, 元々あったspanのid, 移動させたい要素の行数, 何行目から削除するか, 削除したいliの数
            console.log('A');
            const span_id_origin_number = Number(span_id_origin.replace(/[^0-9]/g, ''));
            const span_id_forth_number = span_id_origin_number - 1;
            const span_id_forth = 'page_' + span_id_forth_number; // 前のページのid
            const span_forth = document.getElementById(span_id_forth); // 前のページ
            let span_origin = document.getElementById(span_id_origin); // 元々のページ

            // 前のページについて
            let span_forth_li = span_forth.children[0].getElementsByTagName('li'); // 前のページのspan内のli
            // liを全部消して、後で足すようにする
            for(i=span_forth_li.length-1; i>=li_start; i--){
                // 遡らないと番号が変わる
                if(span_forth_li[i] !== undefined && span_forth_li[i].parentElement.tagName === 'UL'){
                    span_forth.children[0].removeChild(span_forth_li[i]);
                }else{
                    break;
                }           
            }
            span_forth.children[0].appendChild(element);
            span_forth_li = span_forth.children[0].getElementsByTagName('li'); // 前のページのspan内のli
            if(span_forth_li.length != 16){
                for(i=span_forth_li.length; i< 16; i++){
                    const li = document.createElement('li');
                    span_forth.children[0].appendChild(li);
                }
            }
            span_origin = document.getElementById(span_id_origin);

            // 元々のページについて
            const span_id_next_number = span_id_origin_number + 1;
            const span_id_next = 'page_' + span_id_next_number; // 次のページのid
            const span_next = document.getElementById(span_id_next); // 次のページのspan
            let span_origin_li = span_origin.getElementsByTagName('li');

            let span_origin_li_number = li_count; // 元々のページの移動した要素のliの数＋span内の調整用liの数
            let span_origin_li_start = 0; // 何番目のliから消すか
            let want_move_element_li_number; // ページを移動させるかもしれない要素のliの数
            let want_move_element; // ページを移動させるかもしれない要素

            if(span_next !== null){
                for(i=span_origin_li.length-1; i > 0; i--){
                    if(span_origin_li[i].innerHTML === '' && span_origin_li[i].parentElement.tagName === 'UL'){ // タグ名を取得すると大文字
                        span_origin_li_number++;
                    }else{
                        span_origin_li_start = i;
                        break;
                    }
                }

                // 編集
                const span_next_ul_first_childern = span_next.children[0].children[0]; // 次のページのspanの最初のフォーム
                if(span_next_ul_first_childern.className == 'add-set-all-area'){
                    const span_next_ul_first_childern_firstdiv = span_next_ul_first_childern.children[1];
                    if(span_next_ul_first_childern_firstdiv.childElementCount == 1){
                        // add-set-all-areaクラス内のフォームセットが1つだけ
                        want_move_element_li_number = span_next_ul_first_childern.getElementsByTagName('li').length; 
                        want_move_element = span_next_ul_first_childern;
                    }else{
                        // add-set-all-areaクラス内のフォームセットが複数の場合は最初のフォームセットだけ
                        want_move_element_li_number = span_next_ul_first_childern_firstdiv[0].getElementsByTagName('li').length;
                        want_move_element = span_next_ul_first_childern_firstdiv;
                    }
                }else if(span_next_ul_first_childern.className == 'add-set-area'){
                    // 次のspanの最初のフォームセットのクラスがadd-set-areaクラスの場合
                    want_move_element_li_number = span_next_ul_first_childern.getElementsByTagName('li').length;
                    want_move_element = span_next_ul_first_childern;
                }
            }
            
            if(span_next !== null && span_origin_li_number >= want_move_element_li_number){
                // 要素を移動させる
                moveElement_back(want_move_element, span_next.id, want_move_element_li_number, span_origin_li_start, want_move_element_li_number-1);
            }else{
                const span_page_right = document.getElementsByClassName('page-right');
                for(i=0; i<span_page_right[span_page_right.length-1].children[0].children.length; i++){
                    if(span_page_right[span_page_right.length-1].children[0].children[i].tagName === 'LI' && span_page_right[span_page_right.length-1].children[0].children[i].innerHTML == ''){
                        if(i === span_page_right[span_page_right.length-1].children[0].children.length-1){
                            // 最後の右ページ（page-rightクラスを持つspan）の全ての要素が調整用liの場合
                            span_page_right[span_page_right.length-1].parentElement.remove();
                        }
                        continue;
                    }else{
                        break;
                    }
                }
                // 移動させない時は調整用liを追加
                if(span_origin !== null){
                    span_origin_li = span_origin.getElementsByTagName('li');
                    for(i=span_origin_li.length; i<16; i++){
                        const li = document.createElement('li');
                        span_origin.children[0].appendChild(li);
                    }
                }
            }            
        }

        // 出演者 追加
        // フォーム追加
        function addPlayer(){            
            if(current_player_number < 49){
                const all_players_area_s = document.getElementsByClassName('all-players-area');
                const all_players_area = all_players_area_s[all_players_area_s.length-1]; // 最後のall_players_areaクラス
                const span = document.getElementById(event.path[3].id); // 出演者追加ボタンのあるspan
                const span_id_li_last = '#'+ event.path[3].id +' li:last-child';  // 出演者追加ボタンのspanの最後のliを取得するための引数
                const last_li = document.querySelectorAll(span_id_li_last); // 出演者追加ボタンのspan内の最後のli

                current_player_number++; // id
                const formerNumber = current_player_number - 1; // ひとつ前のフォーム（コピーしたフォーム）のid番号
                // 要素をコピーする
                let copied = all_players_area.firstElementChild.cloneNode(true);
                copied.id = 'player_area_' + current_player_number; // コピーした要素のidを変更

                if(event.path[1] == last_li[last_li.length-1]){
                    const new_all_players_area = document.createElement('div');
                    new_all_players_area.className = 'all-players-area';
                    new_all_players_area.appendChild(copied);
                    moveElement_forth(new_all_players_area, event.path[3].id, 1, 0);
                    moveElement_forth(event.path[1], event.path[3].id, 1, 2); // 2は2番目の子要素として

                    const li = document.createElement('li'); // spanの最後に調整用liを作る
                    span.children[0].appendChild(li);
                }else{
                    // コピーしてフォーム番号を変更した要素を親要素の一番最後の子要素にする
                    all_players_area.appendChild(copied);
                    // 出演者のnameを取得する     
                    var copied_player_names = document.getElementsByName('player[]'); // 一度name属性を取得して、最後の要素のidを書き換える
                    // 出演者のidを変更する
                    const new_player_id = 'player_' + current_player_number; // 新しいplayerのid、文字＋計算はできない
                    copied_player_names[(copied_player_names.length)-1].id = new_player_id; // 出演者のidを変更
                    copied_player_names[(copied_player_names.length)-1].value = '';

                    if(last_li[last_li.length-1].parentElement === span.children[0]){
                        // 出演者追加ボタンのあるspan内の最後のliが調整用liの場合
                        last_li[last_li.length-1].remove(); // 出演者追加ボタンのあるspan内の最後のliを削除
                    }else{
                        // 出演者追加ボタンのあるspan内の最後のidが調整用li出ない場合
                        const span_id_add_set_area_last = '#'+ event.path[3].id +' .add-set-area:last-child';  // 出演者追加ボタンのspanの最後のadd-set-areaクラスを取得するための引数
                        const last_add_set_area = document.querySelectorAll(span_id_add_set_area_last); // 出演者追加ボタンのspan内の最後のadd-set-areaクラス

                        const add_set_area_number = last_add_set_area[0].id.replace(/[^0-9]/g, ''); // class=add-set-areaの中で一番最後のidの番号を取得する
                        if(add_set_area_number === String(0)){
                            // span内の最後の要素が出演者への感想や好きな場面、関連のある公演の最初の子要素である（idが0）の場合
                            const span_id_add_set_all_area_last = '#'+ event.path[3].id +' .add-set-all-area:last-child';  // 出演者追加ボタンのspanの最後のadd-set-all-areaクラスを取得するための引数
                            const last_add_set_all_area = document.querySelectorAll(span_id_add_set_all_area_last); // 出演者追加ボタンのspan内の最後のadd-set-all-areaクラス
                            const last_add_set_all_area_id_li_s = last_add_set_all_area[0].getElementsByTagName('li'); // add-set-all-areaクラス内のli
                            for(let i=1; i<last_add_set_all_area_id_li_s.length; i++){
                                const li = document.createElement('li');
                                const ul = span.children;
                                ul.item(0).appendChild(li);
                            }
                            moveElement_forth(last_add_set_all_area[0], event.path[3].id, last_add_set_all_area_id_li_s.length, 0); // 0はulの最初の子要素として
                        }else{
                            //上記以外の場合
                            last_add_set_area_parent_id = last_add_set_area[0].parentElement.id;
                            last_add_set_area[0].remove();
                            const last_add_set_area_id_li_s = last_add_set_area[0].getElementsByTagName('li'); // add-set-areaクラス内のli
                            for(let i=1; i<last_add_set_area_id_li_s.length; i++){
                                const li = document.createElement('li');
                                const ul = span.children;
                                ul.item(0).appendChild(li);
                            }
                            if(last_add_set_area_parent_id == ''){
                                moveElement_forth(last_add_set_area[0], event.path[3].id, last_add_set_area_id_li_s.length, 0); // 0はulの最初の子要素として
                            }else{
                                moveElement_forth(last_add_set_area[0], event.path[3].id, last_add_set_area_id_li_s.length, 1); // 1はadd-set-all-areaクラスの最後の子要素として
                            }
                            
                        }
                    }        
                }                       
            }else{
                // 出演者追加ボタンのあるspan内の最後のliが調整用liでない場合
                alert('出演者の登録は50人までです。');
            }                
        }
        // フォーム削除
        function dispPlayer(){
            if(current_player_number > 0){
                const all_players_area_s = document.getElementsByClassName('all-players-area');
                const all_players_area = all_players_area_s[all_players_area_s.length-1]; 
                const span_now = document.getElementById(event.path[3].id); // 出演者削除ボタンのあるspan
                const span_now_li = span_now.children[0].getElementsByTagName('li'); // 出演者削除ボタンのspan内のli
                let span_now_li_number = 1; // 出演者削除ボタンのspan内の調整用liの数
                let span_now_li_start = 0; // 何番目のliから消すか
                
                for(i=span_now_li.length-1; i > 0; i--){
                    if(span_now_li[i].innerHTML === '' && span_now_li[i].parentElement.tagName === 'UL'){ // タグ名を取得すると大文字
                        span_now_li_number++;
                    }else{
                        span_now_li_start = i;
                        break;
                    }
                }

                const span_now_id_number = Number(event.path[3].id.replace(/[^0-9]/g, '')); // 出演者削除ボタンのあるspanのid番号                

                if(all_players_area.childElementCount == 1){
                    // 出演者削除ボタンが2行目
                    all_players_area.parentElement.removeChild(event.path[1]);
                    moveElement_back(event.path[1], span_now.id, 1, 1, 16);
                    all_players_area.parentElement.removeChild(all_players_area);
                }else{
                    // 出演者削除ボタンが3行目以降
                    const remove_element = all_players_area.children[current_player_number-(all_players_area_s.length-1)*13]; // 削除したいフォームはall-players-areaクラスセットの最後のセットの最後のフォームセット
                    all_players_area.removeChild(remove_element);
                }
                
                current_player_number--;
                const span_next_id_number = span_now_id_number + 1;
                const span_next = document.getElementById('page_' + span_next_id_number); // 出演者削除ボタンのあるspanの次のspan
                const span_next_ul_first_childern = span_next.children[0].children[0]; // 出演者削除のあるspanの次のspanの最初のフォーム
                let want_move_element_li_number; // ページを移動させるかもしれない要素のliの数
                let want_move_element; // ページを移動させるかもしれない要素
                if(span_next_ul_first_childern.className == 'add-set-all-area'){
                    const span_next_ul_first_childern_firstdiv = span_next_ul_first_childern.children[1];
                    if(span_next_ul_first_childern_firstdiv.childElementCount == 1){
                        // add-set-all-areaクラス内のフォームセットが1つだけ
                        want_move_element_li_number = span_next_ul_first_childern.getElementsByTagName('li').length; 
                        want_move_element = span_next_ul_first_childern;
                    }else{
                        // add-set-all-areaクラス内のフォームセットが複数の場合は最初のフォームセットだけ
                        want_move_element_li_number = span_next_ul_first_childern_firstdiv[0].getElementsByTagName('li').length;
                        want_move_element = span_next_ul_first_childern_firstdiv;
                    }
                }else{
                    // 次のspanの最初のフォームセットのクラスがadd-set-areaクラスの場合
                    want_move_element_li_number = span_next_ul_first_childern.getElementsByTagName('li').length;
                    want_move_element = span_next_ul_first_childern;
                }
                if(span_now_li_number >= want_move_element_li_number){
                    moveElement_back(want_move_element, span_next.id, want_move_element_li_number, span_now_li_start, want_move_element_li_number-1);                                       
                }else if(all_players_area.childElementCount != 1){
                    // 移動させない時は調整用liを追加
                    const li = document.createElement('li');
                    span_now.children[0].appendChild(li);
                }
            }else{
                alert('フォームが1つの場合には削除できません。');
            }
        }

        // 出演者に対する感想 追加
        // フォーム追加
        function addImpression_Player(){        
            if(current_player_number > current_impression_player_number){
                const all_impressions_player_area_s = document.getElementsByClassName('all-impressions-player-area');
                const all_impressions_player_area = all_impressions_player_area_s[all_impressions_player_area_s.length-1]; // 最後のall_impressions_player_areaクラス
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
            }else{
                alert('出演者の数より多い感想は入力できません。');
            }       
        
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
            if(current_impression_scene_number < 49){
                const all_impressions_scene_area_s = document.getElementsByClassName('all-impressions-scene-area');
                const all_impressions_scene_area = all_impressions_scene_area_s[all_impressions_scene_area_s.length-1]; // 最後のall_impressions_scene_areaクラス
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
            }else{
                alert('好きな場面の登録は50個までです。');
            }                    
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
            if(current_related_performance_number + 1 < all_performances_number){
                const all_related_performances_area_s = document.getElementsByClassName('all-related-performances-area');
                const all_related_performances_area = all_related_performances_area_s[all_related_performances_area_s.length-1]; // 最後のall_related_performances_areaクラス
                current_related_performance_number++; // id
                const formerNumber = current_related_performance_number - 1; // ひとつ前のフォーム（コピーしたフォーム）のid番号
                // 要素をコピーする
                let copied = all_related_performances_area.firstElementChild.cloneNode(true);
                copied.id = 'related_performance_area_' + current_related_performance_number; // コピーした要素の子要素のidを変更
                // コピーしてフォーム番号を変更した要素を親要素の一番最後の子要素にする
                all_related_performances_area.appendChild(copied);
                // 関連のある公演のnameを取得する     
                var copied_related_performance_names = document.getElementsByName('related_performances[]'); // 一度name属性を取得して、最後の要素のidを書き換える
                // 関連のある公演のidを変更する
                const new_related_performance_id = 'related_performances_' + current_related_performance_number; // 新しい出演に対する感想の出演者のid、文字＋計算はできない
                copied_related_performance_names[(copied_related_performance_names.length)-1].id = new_related_performance_id; // 出演者のidを変更
                copied_related_performance_names[(copied_related_performance_names.length)-1].value = '';
            }else{
                alert('登録されている舞台以外は選択できません。');
            }                 
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
