# PHP Nature
for php5.4 and above.

这是一次 PHP 语法糖的探索。
原则

- 浑然天成
- 可维护第一，兼顾性能


## 一、文件存放的位置

### Web 目录
1. Web目录里的配置文件用于初始化 Nature 的执行环境。
	- apache： `.htaccess` 
	- nginx： `.user.ini`

2. 用户的 Controller

### App 目录
App 目录用于存放用户编写的程序和应用配置。

	fastparams # for Nginx
	nginx.conf # for Nginx
	Template/
	Model/
	Helper/

-  init.php ：`auto_prepend_file` 在这里必须初始化类 Nature\App
	
### Nature 目录

Nature 目录存放 Nature php 库相关文件。默认应该位于 App 目录的子目录里
*可以直接拷贝*，跳过以下说明。
文件名为： className.class.php，其中文件名和 className 必须大小写保持一致。

-  run.php ：`auto_append_file`，用于按约定执行用户编写的程序。


## 二、约定好的配置
### 2.1 为什么要约定配置
1. 集中式的配置管理有助于对配置文件进行版本管理，并且避免后期维护中遗忘应有的配置。
1. 必要的配置简化使用。

 以下是约定

### 2.2 Nginx

/etc/nginx/site-enabled 中 include：

1.  App/fastcgi_params ，存放 PHP 的环境变量。
1.  App/nginx.conf，rewrite 规则
1.  Web 目录存放 [.user.ini](http://php.net/manual/zh/configuration.file.per-user.php)，
  
	
#### 2.2.1 nginx 示例：
App/nginx.conf 的内容如下
`App/nginx.conf`

	location / {
	    index index.php;
	    try_files $uri $uri/ $uri.php$is_args$args;
	}
	# 如果 App 目录在 Web 目录中
    location /App {
        return 404;
    }

1. try_files 用于去掉 URL 中 .php 的扩展名
2. return 404部分用于安全设置的加强。
3. 此外用户可以自定义 rewrite 规则


#### 2.2.2 .user.ini 示例：

`.user.ini`

    date.timezone = "Asia/Shanghai"
	include_path  = "./App"
	auto_prepend_file = "init.php"
	auto_append_file = "run.php"
	#For debug
    display_errors = On
	error_reporting = E_ALL

1.  设置时区，用于避免 /etc/php.ini 遗忘默认的时区设置。
-  `include_path` 添加 App 目录。
-  `auto_prepend_file/auto_append_file` 为每次请求添加了 Nature 的运行环境。
-  需要调试可以打开显示错误相关选项

#### 2.2.3 fastcgi_params

`App/fastcgi_params`
   
    fastcgi_param   APPLICATION_ENV  production;

用于设置环境变量。应该被包括在 nginx 的 *.php* 配置段中， `include fastcgi_params` 之后。

### 2.3 Apache 下的配置
Apache 下可以将所有配置放置于 Web 目录下的 `.htaccess`

    
    #设置默认 Rewrite 规则
	RewriteEngine On 
	RewriteBase /
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_FILENAME}.php -f
	RewriteRule ^(.+)$ /$1.php [L,QSA]

	#设置环境变量	
	SetEnv DOMAIN "jarfire.org"
	
	# 设置 php 规则
	php_value date.timezone Asia/Shanghai
	php_value include_path "./App"
	php_value auto_prepend_file "init.php"
	php_value auto_append_file "run.php"
	#For debug
	php_flag display_startup_errors on
	php_flag display_errors on
	php_flag html_errors on
	php_value error_reporting 32767



## 三、使用示例


### 3.1 入口点
<code>我们约定：选择 php 文件名为入口点</code>
例如： 编写 user.php 于根目录，即可使用 /user 访问（自动补全 <code>.php</code> ）。

当然你想用单入口模式我们也不会拦着你，自个儿写个，也不是很麻烦。

### 3.2 编写 init.php
在 `App/init.php` 中


	require('Nature/App.class.php');
	$app = new Nature\App;
	

### 3.3 不需要 include，自动使用 Nature 库

`index.php`

	<?php
	//想咋用就咋用



### 3.4 初始化 rest 风格的 Controller
文件内放置一个 class ，并且 extends 自 `Nature\Controller`，框架即可自动初始化，并以 rest 风格调用。
<code>约定: 为避免混乱，一个文件内放一个 Controller </code>


`index.php`

    <?php
        indexController extends Nature\Controller {
            function get(){
                echo 'Can you see me ?';
            }
        }

#### 另一种 rest 风格的调用方式
除了支持初始化 Controller 对象，我们还支持使用 function 初始化。(仅在简单场合使用)
	
`index.php`

	<?php
		function get() {
    		echo 'Can you see me ?';
		}
		function post() {
		}
		


### 3.5 模板
PHP 是最好的模板！ 我们选择 php 原生语法做模板。Controller 自带两个方法：assign 和 display

如果没有指定 display 的模板文件，默认使用当前文件名，并把 .php 替换成 .html

`index.php`

    <?php
    	indexController extends Nature\Controller {
    		function get(){
				$this->display();
				//等值于 $this->display('index.html');
    		}
    	}
    	
`App/Template/index.html`

	<!DOCTYPE html>
	<html>
		<body>
			<h1>Can you see me ?</h1>
		</body>	
	</html>

小技巧：像上面的例子，你还可以写成：

`index.php`

    <?php
    	indexController extends Nature\Controller { }
    	// 如果指定位置的模板文件已经存在，在未编写 get 方法的时候，Controller 会自动调用 $this->display();


#### *Tips:*
[自 PHP5.4 起，即使 `short_open_tag = off`，`<?=` 也是可用的。](http://php.net/manual/zh/ini.core.php#ini.short-open-tag)

### 3.6 加载数据库和模板

-  约定：默认启用模板，因为 PHP 是一种模板语言。
-  需要数据库？为 Controller 设置一个 $db 属性，nature 会自动为你初始化数据库。

示例：`index.php`

    <?php
    	indexController extends Nature\Controller {
    		public $db;
    		function get(){
				$data = $this->db->fetch("SELECT 1,2,3");
				var_dump($data);
				//$data 等值于 array(1, 2, 3)
    		}
    	}


### 3.7 易于使用的单例模式
约定：使用 singleton 函数用单例模式初始化一个类

-  singleton($className);
-  singleton([$className1, $className1]);

如果传入字符串，将会以字符串为类名返回一个实例；如果传入的是数组，将返回一个数组，每一项是一个示例。

这样我们可以实现这样的调用方式：

`test.php`

    <?php
    list($db, $tpl) = singleton(['db', 'tpl']);