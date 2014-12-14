#php nature
for php5.4 and above.

这是一次 PHP 语法糖的探索。
原则

- 浑然天成
- 可维护第一，兼顾性能



### 一、文件存放的位置
#### app 目录
app 目录用于存放应用相关文件。

	nginx.conf
	php-fpm.conf
	template/
	resource/
	
#### library 目录

library 目录存放 nature php 库相关文件。可以直接拷贝，跳过以下说明。
文件名为： classname.class.php，其中 classname 必须小写。
<code>约定：我们不喜欢很长很长的 className，保持小写可以强迫使用者起一个短小的类名。</code>

-  loader.php ：`auto_prepend_file`，用于自动加载函数
-  execute.php ：`auto_append_file`，用于按约定执行用户编写的程序。


### 二、约定好的配置
#### 为什么要配置
-  集中式的配置管理有助于对配置文件进行版本管理，并且避免后期维护中遗忘应有的配置。
-  php 库介入必要的配置有助于简化库的使用。

#### 以下是约定

-  将 nginx.conf 放置于 app 目录，并且在 /etc/nginx/site-enabled 中 include。
<code>约定：将系统相关的配置放置于 site-enabled，将应用相关的配置放到 app/nginx.conf 中 </code>

-  将 php-fpm.conf 也放置于 app 目录，并在 /etc/php5/fpm/pool.d/ 的站点 pool.conf 中 include。 <code>约定：一个站点一个 pool</code>
  
	
#### nginx 配置
app/nginx.conf 的内容如下

	location / {
	    index index.php;
	    try_files $uri $uri/ $uri.php$is_args$args;
	}
    location /app {
        return 404;
    }
    location /library {
	    return 404;
	}

try_files 用于去掉 URL 中 .php 的扩展名
return 404部分用于安全设置的加强。

-  php 的配置
	- php-fpm.conf	
	- .htaccess
	
#### php-fpm.conf 示例：

    php_value[date.timezone] = "Asia/Shanghai"
    php_flag[display_errors] = On
    php_flag[short_open_tag] = On
	php_admin_value[error_reporting] = E_ALL
	php_value[include_path]  = "/data/www/library:/data/www/app"
	php_admin_value[auto_prepend_file] = "loader.php"
	php_admin_value[auto_append_file] = "execute.php"

-  设置时区，用于避免 /etc/php.ini 遗忘默认的时区设置。
-  设置 `short_open_tag` 可以让模板语法更优雅
-  `include_path` 设置 app 和 library 目录，出于便捷考虑。
-  `auto_prepend_file/auto_append_file` 隐藏了每个入口点所需的 include。

*_important:_*
*如果你使用了 apache，上面也可以用一个 .htaccess 实现*


### 三、使用示例


#### 入口点
<code>我们约定：选择 php 文件名为入口点</code>
例如： 编写 user.php 于根目录，即可使用 /user 访问（自动补全 <code>.php</code> ）。

当然你想用单入口模式我们也不会拦着你，自个儿写个，也不是很麻烦。

#### 不需要 include，自动使用 nature 库
	
	<?php
	//想咋用就咋用



#### 初始化 rest 风格的 controller
文件内放置一个 class ，并且 extends 自 controller，框架即可自动初始化，并以 rest 风格调用。
<code>约定: 为避免混乱，一个文件内放一个 controller </code>
	
	/index.php
    <?php
    	indexController extends controller {
    		function get(){
    			 echo 'Can you see me ?';
    		}
    	}

### 另一种 rest 风格的调用方式
除了支持初始化 controller 对象，我们还支持使用 function 初始化。
	
	/index.php
	<?php
		function get() {
    		echo 'Can you see me ?';
		}
		function post() {
		}
		


#### 模板
php 是最好的模板！ 我们选择 php 原生语法做模板。controller 自带两个方法： controller::assign 和 controller::display

如果没有指定 display 的模板文件，默认使用当前文件名，并把 .php 替换成 .html

	/index.php
    <?php
    	indexController extends controller {
    		function get(){
				$this->display();
				//等值于 $this->display('index.html');
    		}
    	}
    	

	/app/template/index.html
	<!DOCTYPE html>
	<html>
		<body>
			<h1>Can you see me ?</h1>
		</body>	
	</html>

#### 加载数据库和模板

-  约定：默认启用模板，因为 php 是一种模板语言。
-  需要数据库？为 controller 设置一个 $db 属性，nature 会自动为你初始化数据库。


	/index.php
    <?php
    	indexController extends controller {
    		public $db;
    		function get(){
				$data = $this->db->fetch("SELECT 1,2,3");
				var_dump($data);
				//$data 等值于 array(1, 2, 3)
    		}
    	}


#### 易于使用的单例模式
约定：使用 singleton 函数用单例模式初始化一个类

-  singleton($className);
-  singleton([$className1, $className1]);

如果传入字符串，将会以字符串为类名返回一个实例；如果传入的是数组，将返回一个数组，每一项是一个示例。

这样我们可以实现这样的调用方式：

    <?php
    list($db, $tpl) = singleton(['db', 'tpl']);