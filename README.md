# YOLOv5を用いた駐輪場管理業務支援システム
Bicycle parking lot management system using YOLOv5  
## URL
ローカル：[http://localhost:8000](http://localhost:8000)  
ステージング：ドキュメントに記載  
本番環境：ドキュメントに記載  
## DB  
ローカル：[projectd-database](https://github.com/projectd-team14/projectd-database)  
ステージング：AWS RDS  
本番環境：AWS RDS  
## 環境構築  
※本プロジェクトは[フロントエンド](https://github.com/projectd-team14/bicycle-system-frontend)と[YOLOv5用サーバー](https://github.com/projectd-team14/yolov5-server)の環境構築が必要です。  
〇主要フレームワーク、ライブラリ、言語等  
・Nuxt.js(Vue.js,Node.js,TypeScript,Sass)  
・Laravel(PHP, Nginx, PHP-FPM, MySQL)  
・YOLOv5(FastAPI, YOLOv5 ※別のインスタンスに設置)  
  
1.リポジトリのclone
```
git clone https://github.com/projectd-team14/bicycle-system-backend.git
```
2.ディレクトリに移動
```
cd bicycle-system-backend
```
3.Dockerイメージのビルド
```
docker compose up -d --build
docker compose exec php sh
composer install
```
4.worker(ワーカー)を起動
```
php artisan queue:work
```











