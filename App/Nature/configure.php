<?php
/**
 * nature 默认配置文件
 */
return [
    'domain'=>$_SERVER['HTTP_HOST'],
    'nature\\mysql'=>[
        'dsn'=>getenv('MYSQL_DSN'),
        'username'=>getenv('MYSQL_USER'),
        'password'=>getenv('MYSQL_PASSWORD'),
        'charset'=>'utf8mb4'
    ],
    'nature\\template'=>[
        'root'=>APP_DIR.'/Template'
    ],
    'nature\\curl'=>[
        'timeout'=>10,
    ]
];
