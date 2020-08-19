<?php
  // ファイルの読み込み
  require_once('inc/functions.php');
  require_once('inc/config.php');

  // セッションの開始
  session_start();

  // ログインチェック
  if ( !isset($_SESSION['id']) || empty($_SESSION['id']) || time() > $_SESSION['time'] + 3600) {
    // $_SESSION['id']が存在しない または、
    // $_SESSION['id']が空っぽ または、
    // 前回アクセスしてから1時間以上経っている時
    header('location: login.php');
    exit();
  }

  // ログイン時間の更新
  $_SESSION['time'] = time();


  // 例外処理
  try {
    // データベースに接続
    $dbh = new PDO(DSN, DB_USER, DB_PASSWORD);

    // SQLのエラーがあったら例外を投げる設定
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL文を作成（ログインユーザーのIDと一致するレコードを取得）
    $sql = 'SELECT * FROM members WHERE id = ?';

    // プリペアドステートメントの作成
    $stmt = $dbh->prepare($sql);

    // ?に値をガッチャンコ
    $stmt->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);

    // ステートメントの実行
    $stmt->execute();

    // 実行結果を連想配列に格納
    $users = $stmt->fetch(PDO::FETCH_ASSOC);

    // 一旦味見
    // print_r($users);

    // GETかPOSTか
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
      // POSTの場合

      // SQL文の作成（投稿内容をDBに挿入）
      $sql = 'INSERT INTO posts(message, member_id, created)
                VALUES(?, ?,  NOW())';

      // プリペアドステートメントの作成
      $stmt = $dbh->prepare($sql);

      // ?に値をガッチャンコ
      $stmt->bindValue(1, $_POST['message'], PDO::PARAM_STR);
      $stmt->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);

      // ステートメントを実行
      $stmt->execute();

      // リダイレクト（index.phpに）
      // 多重投稿対策
      header('Location: index.php');
      exit();
    }

    // SQL文の作成（postsテーブルの全レコードを新着順に抽出）
    $sql = 'SELECT p.*, m.name
              FROM posts AS p JOIN members AS m
              ON p.member_id = m.id
              ORDER BY created DESC';

    // SQLクエリを実行
    $stmt = $dbh->query($sql);

    // 実行結果を連想配列に格納
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 一旦味見
     //print_r($results);

    // データベースの切断
    $dbh = null;

  } catch( PDOException $e) {
  // 例外処理
    //エラーメッセージを表示して処理終了
    echo 'エラー' . h($e->getMessage());
    exit();
  }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>つぶやき</title>
</head>
<body>
<h1>つぶやき</h1>
<p><a href="logout.php">ログアウト</a></p>

<form action="" method="post">
  <dl>
    <dt><label for="message"><?php echo h($users['name']); ?>呟きをどうぞ</label></dt>
    <dd>
      <textarea name="message" id="message" cols="30" rows="10"></textarea>
    </dd>
  </dl>
  <p><input type="submit" value="投稿"></p>
</form>

<section>
  <h2>つぶやき一覧</h2>
  <?php foreach( $results as $result ) : ?>
  <article>
    <p>
      <b><?php echo h($result['name']); ?></b>さん ：
      <?php echo h($result['message']); ?>
    </p>
    <p><time><?php echo h($result['created']); ?></time></p>
  </article>
  <?php endforeach; ?>
</section>

</body>
</html>
