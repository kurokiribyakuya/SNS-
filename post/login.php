<?php
  // ファイルの読み込み
  require_once('inc/functions.php');
  require_once('inc/config.php');

  // セッションの開始
  session_start();

  // クッキーが保存されているかをチェック
  $is_auto_login = false;
  if ( isset($_COOKIE['email']) && !empty($_COOKIE['email'])  ) {
    // クッキーにメルアドが保管されている時
    $_POST['email'] = $_COOKIE['email'];
    $_POST['password'] = $_COOKIE['password'];
    $_POST['save'] = 'on';
    $is_auto_login = true; // フラグ
  }


  // GETかPOSTかを判別
  if ( $_SERVER['REQUEST_METHOD'] === 'POST' || $is_auto_login) {
    // POSTの時

    // 例外処理
    try {
      // データベースに接続
      $dbh = new PDO(DSN, DB_USER, DB_PASSWORD);

      // SQLのエラーがあったら例外を投げる設定
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // SQL文を作成（入力されたメルアドと一致するレコードを抽出）
      $sql = 'SELECT * FROM members WHERE email = ?';

      // プリペアド・ステートメントを作成
      $stmt = $dbh->prepare($sql);

      // ?に値をガッチャンコ
      $stmt->bindValue(1, $_POST['email'], PDO::PARAM_STR);

      // ステートメントを実行
      $stmt->execute();

      // 実行結果を連想配列に格納
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      // 一旦味見
      // echo '<pre>';
      // print_r($result);
      // echo '</pre>';

      // データベースの切断
      $dbh = null;

      if ( password_verify( $_POST['password'], $result['password'] ) ) {
        // ログイン成功

         echo 'ここまできてる？';

        // セッションハイジャック対策
        session_regenerate_id(true); // セッションIDの再発行


        // セッションにログイン情報を格納
        $_SESSION['id'] = $result['id']; // ユーザーのID
        $_SESSION['time'] = time(); // 現在の時間を格納（1970年1月1日からの経過秒）

        // 自動ログインがオンのときだけ
        if ($_POST['save'] === 'on') {

          // ログイン情報をクッキーに保管
          setcookie( 'email', $_POST['email'], time() + 60 * 60 * 24 * 14 ); // 14日間
          setcookie( 'password', $_POST['password'], time() + 60 * 60 * 24 * 14 ); // 14日間
        } else {
          setcookie( 'email', '', time() - 3600 );
          setcookie( 'password', '', time() - 3600 );
        }

        // リダイレクト
        header('Location: index.php');
        exit();
      }

    } catch( PDOexception $e) {
      // 例外発生時
      // エラーメッセージを出力して処理終了
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
  <title>ログイン</title>
</head>
<body>
<h1>ログイン</h1>
<p>ログインして下さい</p>

<form action="" method="post">
  <dl>
    <dt><label for="email">メールアドレス</label></dt>
    <dd>
      <input type="text" name="email" id="email">
    </dd>
    <dt><label for="password">パスワード</label></dt>
    <dd>
      <input type="password" name="password" id="password">
    </dd>
    <dt>ログインの記録</dt>
    <dd>
      <label>
        <input type="checkbox" name="save" value="on">
        次回から自動的にログインする
      </label>
    </dd>
  </dl>
  <p><input type="submit" value="ログインする"></p>
</form>

</body>
</html>
