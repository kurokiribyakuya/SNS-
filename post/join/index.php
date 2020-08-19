<?php
  // ファイルの読み込み
  require_once('../inc/functions.php'); // 関数定義ファイル

  // セッションの開始
  session_start();

  // GET か　POST かを判別
  if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    // POSTの時

    // エラーメッセージ格納用の空の配列を作る
    $error = []; // 配列を初期化（廃墟）

    // 名前の必須項目チェック
    if ( $_POST['name'] == '' ) {
      $error['name'] = 'お名前を入力して下さい';
    }

    // メルアドの形式チェック
    if ( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL ) ) {
      $error['email'] = 'メルアドの形式をちゃんとしてね';
    }

    // メルアドの必須項目チェック
    if ( $_POST['email'] == '' ) {
      $error['email'] = 'メルアドを入力して下さい';
    }

    // パスワードの文字数チェック
    if ( mb_strlen($_POST['password']) < 4 ) {
      $error['password'] = 'パスワードは4文字以上で入力してね';
    }

    // パスワードの必須項目チェック
    if ( $_POST['password'] == '' ) {
      $error['password'] = 'パスワードを入力して下さい';
    }

    // 添付ファイルがあるかどうか
    if ( isset( $_FILES['image'] ) && $_FILES['image']['error'] === 0 ) {
      // ファイルがある時

      // ファイル形式の取得
      $file_type = exif_imagetype( $_FILES['image']['tmp_name'] );

      // git か jpeg か png かをチェック
      if (
          $file_type != IMAGETYPE_GIF &&
          $file_type != IMAGETYPE_JPEG &&
          $file_type != IMAGETYPE_PNG
        ) {
          $error['image'] = '画像は「GIF」か「JPEG」か「PNG」でね';
      }
    }


    // エラーがあるかどうか
    if ( empty($error) ) {
      // エラーが無い時

      // 画像のアップロード
      $file_name = '';
      if ( isset( $_FILES['image'] ) && $_FILES['image']['error'] === 0 ) {
        // アップロード先
        $file_dir = '../member_picture/';

        // ファイル名の作成
        $file_name = date('Ymdhis') . '_' . $_FILES['image']['name'];

        // ファイルの移動
        move_uploaded_file($_FILES['image']['tmp_name'], $file_dir . $file_name  );

      }

      // セッションにユーザーの情報を格納
      $_SESSION['join'] = $_POST; // POSTの内容を代入
      $_SESSION['join']['image'] = $file_name; // 画像のファイル名を代入

      // リダイレクト
      header('Location: check.php');
      exit();
    }


    // echo '<pre>';
    // print_r($error);
    // echo '</pre>';
  }

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>会員登録</title>
</head>
<body>
  <h1>会員登録</h1>
  <p>次のフォームに必要事項をご記入下さい</p>
  <form action="" method="post" enctype="multipart/form-data">
    <dl>
      <dt><label for="name">ニックネーム <span>必須</span></label></dt>
      <dd>
        <input type="text" id="name" name="name" value="<?php if( isset($_POST['name']) ) { echo h($_POST['name']); }?>">
        <?php if ( isset( $error['name'] ) ) : ?>
        <p><?php echo h($error['name']); ?></p>
        <?php endif; ?>
      </dd>
      <dt><label for="email">メールアドレス <span>必須</span></label></dt>
      <dd>
        <input type="text" id="email" name="email" value="<?php if( isset($_POST['email']) ) { echo h($_POST['email']); }?>">
        <?php if ( isset( $error['email'] ) ) : ?>
        <p><?php echo h($error['email']); ?></p>
        <?php endif; ?>
      </dd>
      <dt><label for="password">パスワード <span>必須</span></label></dt>
      <dd>
        <input type="password" id="password" name="password" value="<?php if( isset($_POST['password']) ) { echo h($_POST['password']); }?>">
        <?php if ( isset( $error['password'] ) ) : ?>
        <p><?php echo h($error['password']); ?></p>
        <?php endif; ?>
      </dd>
      <dt><label for="image">写真など</label></dt>
      <dd>
        <input type="file" id="image" name="image">
        <?php if ( isset( $error['image'] ) ) : ?>
        <p><?php echo h($error['image']); ?></p>
        <?php endif; ?>
      </dd>
    </dl>
    <p><input type="submit" value="入力内容の確認"></p>
  </form>
</body>
</html>
