<?php
  // ファイルの読み込み
  require_once('../inc/functions.php'); // 関数定義ファイル
  require_once('../inc/config.php'); // データベースの設定ファイル

  // セッションの開始
  session_start();

  // 「GET」か「POST」を判別
  if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    // POSTの時

    // 例外処理（データベースにレコードを挿入）
    try {
      // データベースに接続
      $dbh = new PDO(DSN, DB_USER, DB_PASSWORD);

      // SQLのエラーがあったら例外とする設定
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // SQL文（Insert）
      $sql = 'INSERT INTO members(name, email, password, picture, created)
                VALUES( ?, ?, ?, ?, NOW())';

      // プリペアドステートメントを作成
      $stmt = $dbh->prepare( $sql );

      // パスワードハッシュ化
      $password = password_hash($_SESSION['join']['password'], PASSWORD_DEFAULT);

      // ? に値をガッチャンコ
      $stmt->bindValue(1, $_SESSION['join']['name'], PDO::PARAM_STR);
      $stmt->bindValue(2, $_SESSION['join']['email'], PDO::PARAM_STR);
      $stmt->bindValue(3, $password, PDO::PARAM_STR);
      $stmt->bindValue(4, $_SESSION['join']['image'], PDO::PARAM_STR);

      // ステートメントを実行
      $stmt->execute();

      // データベース切断
      $dbh = null;

      // リダイレクト
      header('Location: thanks.php');
      exit();


    } catch(PDOException $e) {
      // 例外発生時はエラーを出力して処理終了
      echo 'エラー' . h($e->getMessage());
      exit();
    }
  }

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>入力確認</title>
</head>
<body>
  <h1>入力確認</h1>
  <p>確認して下さい</p>
  <form action="" method="post">
    <dl>
      <dt>ニックネーム</dt>
      <dd>
        <?php echo h($_SESSION['join']['name']); ?>
      </dd>
      <dt>メールアドレス</dt>
      <dd>
        <?php echo h($_SESSION['join']['email']); ?>
      </dd>
      <dt>パスワード</dt>
      <dd>
        【表示されません】
      </dd>
      <dt>写真など</dt>
      <dd>
        <?php if ( !empty($_SESSION['join']['image']) ) : ?>
        <img src="../member_picture/<?php echo h($_SESSION['join']['image']); ?>" alt="<?php echo h($_SESSION['join']['name']); ?>">
        <?php endif; ?>
      </dd>
    </dl>
    <ul>
      <li><a href="index.php?action=rewrite">&laquo; 書き直す</a></li>
      <li><input type="submit" value="登録する"></li>
    </ul>
  </form>
</body>
</html>
