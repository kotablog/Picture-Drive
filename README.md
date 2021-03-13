# Picture-Drive
 
" Picture Drive " とは、私KOTAが作成した画像ファイルをサーバー上にて保存・削除が可能となっている簡易的なオンラインアルバムです。
 
# Usage
 
使用方法は、以下のようになっております。
1. アカウント作成
2. ログイン手続き
3. 画像をアップロードあるいは削除をブラウザ上にて行う

また、データベースにて以下の2つのテーブルを作成する必要があります。

"users"テーブルの作成方法
create table users(
  id int(11) not null auto_increment primary key,
  name varchar(40) not null,
  email varchar(191) not null,
  password varchar(191) not null
 ) engine = innodb;
 
 "picture"テーブルの作成方法
 create table picture(
  id int(11) not null auto_increment,
  user_id int(11) not null,
  file_name varchar(255) not null,
  file_path varchar(255) not null,
  caption varchar(140),
  insert_time datetime
 ) engine = innodb;
 
 このコマンドで作成する事ができます。
 
# Note
 
当Webアプリケーションは初心者がPHPの学習結果をアウトプットする際に作成されたものです。
そのため、多少は学習したつもりではありますが、セキュリティ対策が十分でない場合が考えられます。
もし当ファイルを公開サーバーにて公開し、その際に被害が発生したとしても、私KOTAは一切の責任を負いませんので、ご了承ください。
ローカルサーバーにて動作確認をすることを強くお勧めします。
 
# Author
 
* KOTA
* E-mail: contact@kotablog.jp
 
# License
 
" Picture Drive " is under [MIT license](https://en.wikipedia.org/wiki/MIT_License).
