# 作成
githubでリポジトリを作成して、ローカルにクローンする
_templateディレクトリを複製して、コンテンツの中身をクローンしたディレクトリ内に移動する
docker-compose.yml のportsの値を変更する（表示用とDB用の2箇所）

共通用 commonディレクトリ
sync_watch.sh にパスを追加する

# 運用

## 作業開始時
docker compose up -d && dgulp

これで gulp の watch が始まる

## 作業終了時
1. control + c これで watch（監視）が終了する
2. docker compose stop もしくは GUI で停止ボタンを選択する