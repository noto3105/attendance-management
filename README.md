# coachtech 勤怠管理アプリ
## 環境構築
### dockerビルド
git clone github.com/noto3105/attendance-management.git  
docker-compose up -d -build  

## laravel環境構築
1 docker-compose exec php bash  
2 composer install  
3 .env.exampleから.envファイルをコピーし、環境変数を変更  
~~~
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
~~~
4 アプリケーションキーの作成  
php artisan key:generate  
5 マイグレーションの実行  
php artisan migrate  
6 シーディングの実行  
php artisan db:seed

## メール認証
mailhogというツールを使用しています。  
以下の方法で起動してください。 
.env設定
~~~
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=admin@test.com
MAIL_FROM_NAME="Attendance App"
App_URL=http://localhost
~~~
docker-compose.yml  
~~~
mailhog:
    image: mailhog/mailhog:v1.0.1
    ports:
      - "1025:1025"
      - "8025:8025"
~~~

## テストアカウント
name:test  
email:test@test.com  
password:test1234  

name:admin  
email:admin@test.com  
password:admin1234  

## PHPUnitを使用したテストに関して
~~~
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database test_database;

docker-compose exec php bash
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
~~~
※私がテストをした際、テスト用データベースでのテスト実行でエラーが発生し実行できなかったため、学習用の模擬案件であることを理解した上で、やむを得ず、本番環境でのテストを実行しました。  
もし上記方法でエラーが出るようであれば、本番環境でのテスト実行お願いいたします。  
その際、テスト後はDBの情報は消えますので、お手数ですがシーディングを実行していただければseedhingの情報は戻りますのでよろしくお願いいたします。  
また、PHPUnit.xmlの下記の部分をコメントアウトしていただければ、テスト実行できるかと思います。  
~~~
<server name="APP_ENV" value="testing"/>
<server name="BCRYPT_ROUNDS" value="4"/>
<server name="CACHE_DRIVER" value="array"/>
<server name="DB_CONNECTION" value="mysql_test"/>
<server name="DB_DATABASE" value="demo_test"/>
<server name="MAIL_MAILER" value="array"/>
<server name="QUEUE_CONNECTION" value="sync"/>
<server name="SESSION_DRIVER" value="array"/>
<server name="TELESCOPE_ENABLED" value="false"/>
~~~
## 使用技術
php7.4.9  
laravel8.83.8  
Mysql 8.0.26
