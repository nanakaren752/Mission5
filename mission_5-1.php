 <!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission5-1</title>
    
    <style>
        .class1{ background-color:#db7093;}
        .class2{ background-color:#ffb6c1;}
        .class3{ color:white;}
        .class4{ background-color:#afeeee;}
        .class5{ background-color:#eee8aa;}
    </style>
    
</head>
<body>
<?php

/*関数定義*/

    // DB接続設定
    function pdo(){
        $dsn = 'mysql:dbname=データベース名;host=localhost'; //データベース名
        $user = 'ユーザー名'; //ユーザー名
        $password = 'パスワード'; //パスワード
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        //↑データベース操作でエラーが発生した場合に警告（Worning: ）として表示するために設定するオプション
        return $pdo;
    }
    
    //入力・送信チェック 
    function inputCheck($value){
        if( !empty( $_POST[$value] ) && isset( $_POST[$value] ) ){
            $value = $_POST[$value];//PHPで受信して変数に代入
        }else{
            $value = false;
        }
        return $value;
    }

    //テーブルがあるかのチェック
    function tableCheck($tbname){
        $value = false;
        $pdo = pdo();
        //SHOW TABLES：データベースのテーブル一覧を呼び出し
        $sql ='SHOW TABLES';
        $result = $pdo->query($sql);
        foreach ($result as $row){
            if( $row[0] == $tbname ){
            $value = $row;
            }else{
                //何もしない
            }
        }
        return $value;
    }

    //テーブル作成
    function tableCreate($tbname){
        $pdo = pdo();
        //CREATE文：データベース内にテーブルを作成
        $sql = "CREATE TABLE IF NOT EXISTS $tbname"//もしまだテーブルが存在しないなら作成する
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"//自動的にインデックスを割り振る
        . "name char(32),"
        . "comment TEXT,"
        . "date TEXT,"
        . "password TEXT"
        .");";
        $stmt = $pdo->query($sql);
    }

    //テーブルの詳細を確認
    function tableShow($tbname){
        $pdo = pdo();
        //SHOW CREATE TABLE文：作成したテーブルの構成詳細を確認する
        $sql = "SHOW CREATE TABLE $tbname";
        $result = $pdo -> query($sql);
        foreach ($result as $row){
            echo $row[1];
            echo "<br>";
        }
    }
    
    //SHOW TABLES：データベースのテーブル一覧を表示
    function tableShowList(){
        $pdo = pdo();
        $sql ='SHOW TABLES';
        $result = $pdo->query($sql);
        echo "<ul>";
        foreach ($result as $row){
            echo "<li>";
            echo $row[0];
            echo '</li>';
        }
        echo "</ul>";
    }
    
    //テーブルの存在・中身チェック : メッセージを返す
    function tableCheck2($tbname){
        $pdo = pdo();
        //SELECT文：入力したデータレコードを抽出
        $sql = "SELECT * FROM $tbname";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        
        //existsチェック
        if(tableCheck($tbname)){//ファイルが存在するとき
            //ファイルの中身が空でないときの処理↓
            if( !empty ( $results ) ){
                $value = "OK";
            }else{
                $value = "empty";
            }
        }else{
            $value = "!exists";
        }
        return $value;//エラーの種類を戻り値に設定
    }
    
    //テーブルの削除
    function tableDrop($tbname){
        $pdo = pdo();
        $sql = "DROP TABLE $tbname";
        $stmt = $pdo->query($sql);
    }
    
    //投稿の検索
    function search($tbname, $where){
        //existsチェック
        if(tableCheck($tbname)){//データベースが存在するとき
            $pdo = pdo();
            //WHERE句
            $id = $where ; // idがこの値のデータだけを抽出したい、とする
            $sql = "SELECT * FROM $tbname WHERE id=:id ";
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll(); 
            //投稿の中身が空ではないとき↓
            if( !empty ( $results ) ){
                $value = $results;
            }else{
                $value = false;
            }
        }else{
            $value = false;
        }
            
        return $value;
    }// search end

    
    //新規投稿
    function newPost($tbname, $name, $comment, $password){
        
        echo "【".$tbname."】に保存します";
        echo "<br>";

        $date = date("Y/m/d H:i:s");//日時の取得
        
        $pdo = pdo();
        //INSERT文：データを入力（データレコードの挿入）
        $sql = "INSERT INTO $tbname (name, comment, date, password) VALUES (:name, :comment, :date, :password)";
        $stmt = $pdo->prepare($sql);
            //bindParamの引数名（:name など）はテーブルのカラム名に併せる
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
        echo "投稿が保存されました";
        echo "<br>";
    }
    
    //削除
    function delPost($tbname, $delNum, $password){
        
        echo "【".$tbname."】から投稿を削除します";
        echo "<br>";

        $pdo = pdo();
        // DELETE文：入力したデータレコードを削除
        $id = $delNum;
        $sql = "DELETE from $tbname WHERE id=:id AND password=:password";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':password', $password, PDO::PARAM_INT);
        $stmt->execute();
        
        echo "投稿が削除されました";
        
    }

    //投稿編集準備
    function editReady($tbname, $editNum, $password){
        $editData = search($tbname, $editNum);
        if($editData){
            echo $editData[0]["password"];
            echo $password;
            if($editData[0]["password"] == $password){
                echo "【".$tbname."】から編集する投稿を取得しました";//該当の投稿が見つかりました
            }else{
                echo "編集できません";
                $editData = false;
            }
        }else{
            echo "該当する投稿は見つかりませんでした";
        }
        return $editData[0];
    }
    
    //投稿編集
    function editPost($tbname, $editNum, $name, $comment, $password){
        
        echo "【".$tbname."】を編集します";
        echo "<br>";

        $date = date("Y/m/d H:i:s");//日時の取得

        $pdo = pdo();
        //UPDATE文：入力されているデータレコードの内容を編集
        $id = $editNum; //変更する投稿番号
        $sql = "UPDATE $tbname SET name=:name,comment=:comment,date=:date,password=:password WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        echo "投稿が編集されました";
        
    }
    
    //投稿一覧を表示
    function postView($tbname){
        $pdo = pdo();
        //SELECT文：入力したデータレコードを抽出し、表示する
        $sql = "SELECT * FROM $tbname";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        $count = count($results);
        foreach ($results as $row){
            //$row テーブルのカラム名
            echo $row['id'].' ';
            echo $row['name'].' ';
            echo $row['comment'].' ';
            echo $row['date'].' ';
            echo "<br>";
            echo "<hr>";
        }
        return $count;
    }
    
    /*
    //POST誤送信を防ぐ input最後尾に設定
    function disabled($inputType){
        if($inputType){
            //なにもしない
        }else{
            echo "disabled";
        }
    }*/
    
?>

<!--掲示板の表示-->
<div style="background-color:#faf0e6">
    
<div class="class1 class3">
<h1>簡易掲示板</h1>
</div>

<?php
    echo "▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼"."<br>";
    echo "　テーブル一覧"."<br>";
    tableShowList();
    $tbname = "mission_5";//テーブル指定
    echo "　現在選択中のテーブル ☞【".$tbname."】";
    echo "<br>"."▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲"."<br>";
    $inputType = "";//入力の目的を判断
    $editNum = "";
    $delNum = "";

    if(tableCheck($tbname)){
        //tableShow($tbname);//確認用
    }else{
        tableCreate($tbname);
    }
    
    if($_SERVER['REQUEST_METHOD']==='POST'){//このフォ―ムから送信があった場合
        $name = inputCheck("name");
        $comment = inputCheck("comment");
        $postNum = inputCheck("postNum");
        $delNum = inputCheck("delNum");
        $editNum = inputCheck("editNum");
        $password = inputCheck("password");
        
        //「フォーム」で処理を分岐させる(if)
        $inputType = "";
        if( $password ){//パスワードが入力されているとき
            //投稿が入力されているとき
            if( $name && $comment && !($postNum) ){//投稿が入力されたとき
                $inputType = "newPost";//新規投稿
            }else if( $name && $comment && $postNum ){
                $inputType = "editPost";//投稿編集
            }//削除番号が指定されているとき
            else if( $delNum ){
                $inputType = "del";//投稿削除
            }//編集番号が指定されているとき
            else if( $editNum ){
                $inputType = "edit";//投稿編集
            }else{//入力に不備があるとき、もしくは何も入力されていないとき
                echo "入力内容に不備があります。";
            }//入力の目的を判断 end
        }else{
            echo "パスワードが入力されていません。";
        }//if( $password ) end
        
    }//if($_SERVER['REQUEST_METHOD']==='POST') end
    
        //投稿フォーム
        if($inputType == "newPost"){
            newPost($tbname, $name, $comment, $password);
        }else{
                //何もしない
        }//if($inputType == "newPost") end
        
        //投稿編集フォーム
        if($inputType == "editPost"){
            $editNum = $postNum;
            editPost($tbname, $editNum, $name, $comment, $password);
        }else{
                //何もしない
        }//if($inputType == "newPost") end
        
        //削除フォーム
        if($inputType == "del"){
            delPost($tbname, $delNum, $password); 
        }else{
                //何もしない
        }//if($inputType == "del") end
    
        //編集指定フォーム
        $editData = "";
        if($inputType == "edit"){
            
            $editData = editReady($tbname, $editNum, $password);
            //var_dump($editData);//確認用
            if ($editData) {
                $editName = $editData['name'];
                //var_dump($editName);//確認用
                $editComment = $editData["comment"];
                $editPass = $editData["password"];
            }else {
                //何もしない
            }
                
        }else{
                //何もしない
        }//if($inputType == "edit") end
?>
<!-- システムメッセージ　END -->


<hr>
<!--投稿-->
    <form action="" method="post"><!--入力フォームから「POST送信」-->
    <?php
    
    if( $editNum && $editData){//投稿が既に入力されているとき//編集内容が入力されているとき
        echo "<h2>編集モード</h2>";
        $name = $editName;
        $comment = $editComment;
        $password = $editPass;
    }else{
        echo "<h2>新規投稿モード</h2>";
        $name = "";
        $comment = "";
        $password = "";
    }
    ?>
        <input type="text" name="name" placeholder="名前を入力してください" value='<?php echo $name; ?>' >
        <input type="text" name="comment" placeholder="コメントしてください" value='<?php echo $comment; ?>'>
        <!-- <textarea name="message" rows="5" cols="100" placeholder="メッセージ"></textarea><br> -->
        <input type="hidden" name="postNum" value='<?php  echo $editNum?>' >
        <input type="password" name="password" placeholder="パスワードを入力してください" value='<?php echo $password; ?>'>
        <input type="submit" name="submit" value="送信">
    </form>


<!--投稿削除-->
    <form method='POST' action="">
    <h2>投稿削除</h2>
    <input type='number' name='delNum' placeholder="削除対象番号を入力">
    <input type="password" name="password" placeholder="投稿時に設定したパスワード">
    <input type='submit' name='submit' value='削除'>
    </form>
    
<!--投稿編集-->
    <form method='POST' action="">
    <h2>投稿編集</h2>
    <input type='number' name='editNum' placeholder="編集対象番号を入力">
    <input type="password" name="password" placeholder="投稿時に設定したパスワード">
    <input type='submit' name='submit' value='編集'>
    </form>
<br>
<span>※投稿編集後は下のリセットボタンを押してブラウザを更新してください</span>
<div class="btn"><a href="" class="reset"><h3>リセット</h3></a></div>

<hr>
<?php

//テキストファイルの内容表示
        echo "<h2> ＊書き込み状況＊ </h2>";
        if(tableCheck2($tbname)=="OK"){//ファイルを確認
            $count = postView($tbname);
            echo "<h3> "."投稿数　計：".$count."</h3>";
        }else if(tableCheck2($tbname)=="empty"){
            echo "まだ何も投稿されていません";
        }else if(tableCheck2($tbname)=="!exists"){//ファイルが存在しないとき
            echo "<br>書き込み記録が存在しません。";
        }
?>

</div>

</body>
</html>