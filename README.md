# 内部用メモ（作業準備手順）
githubでリポジトリを作成して、ローカルにクローンする  
_templateディレクトリを複製して、コンテンツの中身をクローンしたディレクトリ内に移動する  
docker-compose.yml のportsの値を変更する（表示用とDB用の2箇所）
共通用ディレクトリにある sync_watch.sh にパスを追加する

## 作業開始時（初回のみ）
docker compose up -d

## 運用時
dgulp

これで gulp の watch が始まる

## 作業終了時
1. control + c (監視終了)
3. docker compose stop もしくは Docker Desktop アプリの停止ボタン
