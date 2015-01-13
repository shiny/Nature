<?php
/**
 * nature 默认配置文件
 */
return [
	'environment'=>'development',
    'domain'=>$_SERVER['HTTP_HOST'],
    'x-powered-by'=>true,
    'Nature'=>[
        'MySQL'=>[
            'dsn'=>getenv('MYSQL_DSN'),
            'username'=>getenv('MYSQL_USER'),
            'password'=>getenv('MYSQL_PASSWORD'),
            'charset'=>'utf8mb4'
        ],
    ],
    'Nature.Template'=>[
        'root'=>APP_DIR.'/Template'
    ],
    'Nature.cURL.timeout'=>10,
];
