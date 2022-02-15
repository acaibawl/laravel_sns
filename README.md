## メール送信テスト
laradockディレクトリでmailhogコンテナを起動  
`docker-compose up -d mailhog`

.envの設定
```
MAIL_DRIVER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=memo
MAIL_PASSWORD=password
MAIL_ENCRYPTION=null
MAIL_FROM_NAME=memo
MAIL_FROM_ADDRESS=no-reply@example.com
```
localhost:8025でmailhogの画面にアクセスできる


