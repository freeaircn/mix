
# 配置Web应用前端和后端(Windows)
---

### 概述(2021.6.26)
- 前端选用antd vue pro模板
- 后端选用CodeIgniter 4框架
- WAMP
---

### 目录结构
- 应用根目录: D:\www\mix
- 前端根目录：D:\www\mix\client
- 后端根目录：D:\www\mix\server
- 数据库脚本：D:\www\mix\db
---

### wamp安装&配置
```
1. 安装php[可选]
  # 安装php  http://www.php.net/downloads.php
  # 解压至C:\Program Files\php
  # 将php.ini-development改为php.ini，并将extension_dir = "ext"修改为PHP根目录下的php/ext目录，修改为：extension_dir = "C:/Program Files/php/ext"
  # 将C:\Program Files\php添加至系统环境PATH变量，添加完成，CMD验证：php -v

2. 修改DB root账号
use mysql;  
update user set authentication_string=PASSWORD('App@4321') where user='root';  
UPDATE user SET password=password('App@4321') WHERE user='root';  
flush privileges;

  用户
    create user app@localhost identified by 'Sql@1234';
    grant all on binglang.* to app@localhost;
    REVOKE all ON binglang.* FROM 'app'@'localhost';
    show grants;
    show grants for app@localhost;
    
    SET password for 'root'@'localhost'=password('pwd');
  
3. 修改phpadmin config
\wamp64\apps\phpmyadmin4.5.2\config.inc.php
$cfg['Servers'][$i]['auth_type'] = 'config';
$cfg['Servers'][$i]['user'] = 'root';
$cfg['Servers'][$i]['password'] = 'App@4321';

4. 修改appche httpd-vhosts
<VirtualHost *:8080>
  ServerName mix
  ServerAlias mix
  DocumentRoot "D:/www/mix/server/public"
  <Directory "D:/www/mix/server/public/">
    Options -Indexes -Includes +FollowSymLinks -MultiViews
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>  

5. 系统环境变量添加PHP
wamp集合多个php版本，选定PHP 版本.
将对应路径C:\wamp64\bin\php\php7.3.5\添加至系统环境PATH变量
CMD验证：php -v
```
---

### 配置工具

***1. 安装nodejs, webpack, vue-cli***
```
1 安装nodejs

2 修改npm模块路径和cache路径
  将global模块和cache缓存路径修改到D盘目录下，手动创建文件加node_cache和node_global  
  执行命令：
  npm config set prefix "D:\nodejs\node_global"
  npm config set cache "D:\nodejs\node_cache"
  系统变量，新建"NODE_PATH"，输入D:\nodejs\node_global\node_modules
  用户变量，PAHT添加D:\nodejs\node_global

3 修改国内 镜像地址
  npm config set registry http://registry.npm.taobao.org

4 npm全局安装webpack，4.0版本后还需要webpack-cli
  npm install -g webpack
  npm install -g webpack-cli
5 验证
  webpack -v
  
6 npm全局安装vue-cli
  npm install -g @vue/cli
7 卸载vue-cli
  npm uninstall -g vue-cli
8 验证
  vue --version

```

***2. 安装php-Composer***
```
1 设置国内镜像
composer config -g repo.packagist composer https://packagist.phpcomposer.com

composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

2 [跳过，设置PHP环境变量解决]修改composer.bat指定WAMP使用的PHP版本
  # C:\ProgramData\ComposerSetup\bin\composer.bat
  @C:\wamp64\bin\php\php7.2.18\php.exe "%~dp0composer.phar" %*
```
 
---

### 配置CI 4
```
1 下载
  composer create-project codeigniter4/appstarter --no-dev server

2 新建应用
  文件夹: \server\mix
  将CI 4的app，public，tests，writable文件夹复制到\server\mix内。

3 修改mix应用的systemPath
  文件：\server\mix\app\Config\Paths.php
  修改：
  public $systemDirectory = __DIR__ . '/../../../vendor/codeigniter4/framework/system';
  
4 设置时区
  文件：\server\mix\app\Config\App.php
  修改：
  public $appTimezone = 'Asia/Shanghai';

5 修改默认控制器视图
  文件：\server\mix\app\Controllers\Home.php
  修改：
  index方法 - return view('home.html');
  
6 env文件
  复制文件至\server\mix路径下
  文件：\server\mix\.env
```  
---

### 配置antd vue pro
```
1 下载至D:\www\mix，修改文件夹名为client
  
2 安装依赖
  npm install
  
3 build for test environment
  npm run server

4 build for production environment
  npm run build

5 构建发布
  文件：\client\vue.config.js
  添加：
  const vueConfig = {
  // html header src引用路径
  publicPath: process.env.NODE_ENV === 'development' ? '/' : '/dist/',
  // 存放路径
  outputDir: process.env.NODE_ENV === 'development' ? 'dist' : 'D:/www/mix/server/mix/public/dist',
  // html首页路径
  indexPath: process.env.NODE_ENV === 'development' ? 'index.html' : 'D:/www/mix/server/mix/app/Views/home.html',
  
  文件：\client\.env
  修改：
  VUE_APP_API_BASE_URL=http://127.0.0.1:8080/api
    
6 开发/生产环境与后端交互
  文件：\client\vue.config.js
  修改：
  devServer: {
    // development server port 8000
    port: 8081,
    open: false,
    overlay: {
      warnings: false,
      errors: true
    },
    // If you want to turn on the proxy, please remove the mockjs /src/main.jsL11
    // detail: https://cli.vuejs.org/config/#devserver-proxy
    proxy: {
      [process.env.VUE_APP_API_BASE_URL]: {
        target: `http://127.0.0.1:8080`,
        changeOrigin: true
        // pathRewrite: {
        //   ['^' + process.env.VUE_APP_API_BASE_URL]: ''
        // }
      }
    }
    
  文件：/src/main.js
  去除：
  import mockjs
```
---
