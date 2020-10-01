<?php

$editNum ="";
$editName = "";
$editComment = "";
$editPass="";

$error = "";

$isDeleteNum=true;
$isEditNum=true;

//データベース接続
    $dsn = "データベース名";
	$user = "ユーザー名";
	$password = "パスワード";
	//pdo=データベースの内容をPHPのオブジェクトのように扱えるようになる
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));//エラーが発生したとき警告表示
	
	

//ユーザー定義関数
function showRecords()//function関数名（）｛処理｝呼び出すには関数名（）；
{
    global $pdo;//グローバル変数を参照
        //データの絞り込み
        $sql="SELECT * FROM tb_1";
        $stmt=$pdo->query($sql);//SQL固定の場合
        $results=$stmt->fetchAll();//データ取得
        foreach ($results as $row) {
        //$rowの中にはテーブルのカラム名が入る
        echo $row["id"].
            "< >".
             $row["name"].
            "< >".
             $row["comment"].
            "< >".
             $row["date"].
            "<br>";
        echo "<hr>";//水平方向の枠線
        }
}

function deleteRecord($num)
{
    global $pdo;
            
        $id = $num;
        $sql = "delete from tb_1 where id=:id";
        $stmt = $pdo->prepare($sql);//SQL可変
        $stmt->bindParam(":id",$id,PDO::PARAM_INT);//値の参照を受け取る データの挿入
        $stmt->execute();//値を確定をする
}

function addRecord($newName, $newComment, $newPass)
{
    global $pdo;
        $sql = $pdo->prepare(
        "INSERT INTO tb_1 (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)"
        );
            
        $sql->bindParam(":name", $name, PDO::PARAM_STR);
        $sql->bindParam(":comment",$comment , PDO::PARAM_STR);
        $sql->bindParam(":date",$date, PDO::PARAM_STR);
        $sql->bindParam(":pass", $pass, PDO::PARAM_STR);
        $name = $newName;
        $comment = $newComment;
        $date = date("Y年m月d日 H:m:s");
        $pass = $newPass;
        $sql->execute();
}

function editRecord($num, $rename, $newComment, $newPass)
{
    global $pdo;
        
        $id = $num;//変更する投稿番号
        $name = $rename;
        $comment = $newComment;
        $date = date("Y年m月d日 H:m:s");
        $pass = $newPass;
        
        $sql = "UPDATE tb_1 SET name=:name, comment=:comment, date=:date, pass=:pass WHERE id=:id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":date", $date, PDO::PARAM_STR);
        $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
        $stmt->execute();
}
        
//テーブル生成
$sql =
    "CREATE TABLE IF NOT EXISTS tb_1" .//同じ名前のテーブルを作らないように
    "(".
    "id INT AUTO_INCREMENT PRIMARY KEY," .
    "name char(32),".
    "comment TEXT,".
    "date TEXT,".
    "pass TEXT".
    ");";
    $stmt = $pdo->query($sql);

//送信ボタンが押された場合
if($_POST["submit"]) {

    if(empty($_POST["text"]) || empty($_POST ["name"]) || empty($_POST["pass"])) {
        $e = showRecords();
        $error="名前、コメント、またはパスワードが入力されていません。"."</>";

    }elseif ($_POST["editPost"]) {
     //削除前のデータの維持
     $sql = "SELECT * FROM tb_1";
     $stmt = $pdo->query($sql);
     $results = $stmt->fetchAll();
     //編集番号と等しい時、新しいデータを入れ直す
     foreach ($results as $result) {
        if ($_POST["editPost"]===$result["id"]) {
            editRecord($_POST["editPost"], $_POST["name"], $_POST["text"], $_POST["pass"]);
        }
     }
     $e = showRecords();
    }else{
     //新規投稿の場合
     addRecord($_POST["name"], $_POST["text"], $_POST["pass"]);
     $e = showRecords();
    }
}elseif ($_POST["delete"]) {
    $deleteNum = $_POST["deleteNum"];
    
    //データの維持
    $sql = "SELECT * FROM tb_1";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    
    if (empty($_POST["deleteNum"]) || empty($_POST["deletePass"])) {
        $error="削除する番号、またはパスワードが入力されていません"."</br>";
        $e = showRecords();
    }else{
     foreach ($results as $result) {
        if ($_POST ["deletePass"]!== $result["pass"]) {
            $error="正しいパスワードを入力してください"."<br>";
            $styleNone = "display : none;";
        }else{
        //ファイルのチェック&データ削除
        $isDeleteNum = false;
            foreach ($results as $result) {
             if ($deleteNum === $result["id"]) {
            deleteRecord($deleteNum);
            $isDeleteNum = true;
             }
            }
            if (!$isDeleteNum) {
            $idError ="IDが見つかりませんでした"."<br>";
            }
        }
     }
    $e = showRecords();
    }
}elseif ($_POST["edit"]) {
    if (empty($_POST["editNum"]) || empty($_POST["editPass"])) {
        $error="編集する番号、またはパスワードが入力されていません"."<br>";
        $e = showRecords();
    } else {
    //データの維持
    
    $sql = "SELECT * FROM tb_1";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    
    foreach ($results as $result) {
     if($_POST["editPass"]!== $result["pass"]) {
        $error="正しいパスワードを入力してください"."<br>";
        $styleNone = "display:none;";
     }else{
        $isEditNum = false;
        if ($_POST["editNum"] === $result["id"]) {
            $editNum = $result["id"];
            $editName = $result["name"];
            $editComment = $result["comment"];
            $editPass = $result["pass"];
            $isEditNum = true;
        }
     }
    }
    if(!$isEditNum){
        $idError ="IDが見つかりませんでした"."<br>";
    }
    $e = showRecords();
   }
}elseif ($_POST["allDel"]){
    //全削除
    $sql = "delete from tb_1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
}else{
    $e = showRecords();
}
    ?>
    
<!DOCTYPE html>
<html lang="ja">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>mission_5-1</title>
</head>
<body>
<p><?= $error ?></p>    
    <p style="<?=$styleNone ?>"><?=$idError?></p>
    <form action="" method="POST">
        <input type="text" name="name" placeholder="名前" value="<?= $editName ?>">
        <br>
        <input type="text" name="text" placeholder="コメント" value="<?= $editComment ?>">
        <input type="hidden" name= "editPost" value="<?= $editNum ?>">
        <input type="text" name="pass" placeholder="パスワード" value="<?= $editPass ?>">
        <input type="submit" name="submit">
        <br />
        <br />
        
        <input type="text" name="deleteNum" placeholder="削除対象番号">
        <input type-"text" name="deletePass" placeholder="パスワード">
        <input type="submit" name="delete" value="削除">
        <br />
        <br />
        
        <input type="text" name="editNum" placeholder="編集対象番号">
        <input type="text" name= "editPass" placeholder="パスワード">
        <input type="submit" name="edit" value="編集">
        
        <input type="submit" name="allDel" value="全削除">
    </form>
    
    <p><?= $e ?></p>