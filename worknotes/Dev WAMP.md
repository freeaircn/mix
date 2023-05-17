
# Windows环境配置Web应用前端和后端
---

### 2023.5.16
- antd vue 1.7.8 和ant design pro of vue
- CodeIgniter 4.1.3 
- WAMP 3.3.1  PHP 8.1.19
---

### 目录结构
- 根目录:     \www\mix
- 前端目录：  \www\mix\client
- 后端目录：  \www\mix\server
- 数据库脚本：\www\mix\db
---

### 1 WAMP
```
1 数据库账号
  use mysql;  
  SET password for root@localhost = password('App@4321');
  flush privileges;

  新建数据库用户
    create user app@localhost identified by 'Sql@1234';
    grant all on mix.* to app@localhost;
    REVOKE all ON mix.* FROM 'app'@'localhost';
    show grants;
    show grants for app@localhost;
    
    SET password for 'root'@'localhost'=password('pwd');

2 修改appche httpd-vhosts.conf
<VirtualHost *:8080>
  ServerName mix
  ServerAlias mix
  DocumentRoot "/www/mix/server/public"
  <Directory "/www/mix/server/public/">
    Options -Indexes -Includes +FollowSymLinks -MultiViews
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>

3 修改appche httpd.conf
  Listen 0.0.0.0:8080

4 添加php redis扩展
  http://pecl.php.net/package/redis 下载redis扩展
  将php_redis.dll复制到wamp下某版本php的ext文件夹内，例如：C:\wamp64\bin\php\php8.1.19\ext
  配置php.ini，增加：extension=php_redis.dll

5 [可选]系统环境变量添加PHP
  wamp集合多个php版本，选定PHP 版本.
  将对应路径C:\wamp64\bin\php\php7.4.33\添加至系统环境PATH变量
  CMD验证：php -v

6 [可选]安装php
  # 安装php  http://www.php.net/downloads.php
  # 解压至C:\Program Files\php
  # 将php.ini-development改为php.ini，并将extension_dir = "ext"修改为PHP根目录下的php/ext目录，修改为：extension_dir = "C:/Program Files/php/ext"
  # 将C:\Program Files\php添加至系统环境PATH变量，添加完成，CMD验证：php -v

7 [可选]修改phpadmin config
  \wamp64\apps\phpmyadmin4.5.2\config.inc.php
  $cfg['Servers'][$i]['auth_type'] = 'config';
  $cfg['Servers'][$i]['user'] = 'root';
  $cfg['Servers'][$i]['password'] = 'App@4321';
```
---

### 2 Composer 2.5.5
```
1 设置国内镜像
  composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
  composer config -g repo.packagist composer https://mirrors.cloud.tencent.com/composer/

2 指定PHP版本
  # C:\ProgramData\ComposerSetup\bin\composer.bat
  @C:\wamp64\bin\php\php7.4.33\php.exe "%~dp0composer.phar" %*
```
---

### 3 Nodejs 8.17.0
```
1 国内镜像
  npm config set registry http://registry.npm.taobao.org
  
2 [可选]修改npm模块路径和cache路径
  将global模块和cache缓存路径修改到D盘目录下，手动创建文件加node_cache和node_global  
  执行命令：
  npm config set prefix "D:\nodejs\node_global"
  npm config set cache "D:\nodejs\node_cache"
  系统变量，新建"NODE_PATH"，输入D:\nodejs\node_global\node_modules
  用户变量，PAHT添加D:\nodejs\node_global

3 [可选]全局安装webpack，4.0版本后还需要webpack-cli
  npm install -g webpack
  npm install -g webpack-cli
  webpack -v
  
4 [可选]全局安装vue-cli
  npm install -g @vue/cli
  vue --version
  
  卸载
  npm uninstall -g vue-cli

```
---

### 4 Redis for windows
```
1 下载 Redis-x64-xxx.zip
  https://github.com/tporadowski/redis/releases
  C 盘解压后，将文件夹重新命名为 redis
  
2 默认配置启动redis
  cmd /c "C:\redis\redis-server"
```
---

### 5 Github仓库
```
1 安装git软件

2 添加git用户
  git config --global user.name "freeaircn"
  git config --global user.email "freeaircn@163.com"

3 新建SSH密钥
  ssh-keygen -t rsa -C freeaircn@163.com
  # id_rsa是私钥，id_rsa.pub是公钥，登陆GitHub，打开“Account settings”，“SSH Keys”页面在Key文本框里粘贴id_rsa.pub文件的内容

4 下载仓库
  git clone https://github.com/freeaircn/mix.git
```
---
 
### 6 CodeIgniter 4
```
1 初始安装依赖包
  composer install --no-dev
  
2 更新依赖包
  composer update --no-dev  

3 检查app, public, tests, writable目录下的文件，手动合并变化
  修改：
  ./public
  ./app/Views
  ./app/Controllers/BaseController.php
  ./app/Common.php
  ./spark
  ./phpunit.xml.dist
  ./env
  ./app/Config/App.php
  ./app/Config/Autoload.php
  ./app/Config/Cache.php
  ./app/Config/Constants.php
  ./app/Config/Database.php
  ./app/Config/Email.php
  ./app/Config/Filters.php
  ./app/Config/Paths.php
  ./app/Config/Routes.php
  ./app/Config/Services.php
  ./app/Config/Validation.php
  
  复制：
  ./app/Config/ContentSecurityPolicy.php
  ./app/Config/Cookie.php
  ./app/Config/DocTypes.php
  ./app/Config/Encryption.php
  ./app/Config/Events.php
  ./app/Config/Exceptions.php
  ./app/Config/Format.php
  ./app/Config/Generators.php
  ./app/Config/Honeypot.php
  ./app/Config/Images.php
  ./app/Config/Kint.php
  ./app/Config/Logger.php
  ./app/Config/Migrations.php
  ./app/Config/Mimes.php
  ./app/Config/Modules.php
  ./app/Config/Pager.php
  ./app/Config/Security.php
  ./app/Config/Toolbar.php
  ./app/Config/UserAgents.php
  ./app/Config/View.php
  
  新增：
  ./preload.php
  ./app/Config/CURLRequest.php
  ./app/Config/Feature.php
  ./app/Config/Publisher.php
  ./app/Config/Session.php  // v4.3.0 - Config/App中的session配置项失效
  
4 修改mix应用的systemPath  
  文件：\app\Config\Paths.php
  public $systemDirectory = __DIR__ . '/../../../vendor/codeigniter4/framework/system';
  
 
5 设置时区
  文件：\app\Config\App.php
  修改：
  public $appTimezone = 'Asia/Shanghai';

6 修改默认控制器视图
  文件：\app\Controllers\Home.php
  修改：
  index方法 - return view('home.html');
  
7 env文件
  文件：./.env
  
  下载
  composer create-project codeigniter4/appstarter --no-dev server
 
```  
---

### 7 Ant Design Vue Pro
```
1 初始安装依赖包
  npm install
  
2 配置
  文件：\client\vue.config.js
  const vueConfig = {
  // html header src引用
  publicPath: process.env.NODE_ENV === 'development' ? '/' : '/dist/',
  // 存放
  outputDir: process.env.NODE_ENV === 'development' ? 'dist' : 'D:/www/mix/server/mix/public/dist',
  // html首页
  indexPath: process.env.NODE_ENV === 'development' ? 'index.html' : 'D:/www/mix/server/mix/app/Views/home.html',

  生产环境
  文件：\client\.env
  VUE_APP_API_BASE_URL=http://127.0.0.1:8080/api
  
  dev测试
  文件：\client\vue.config.js
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
  
3 测试Dev
  npm run serve

4 发布
  npm run build
  
  
  文件：/src/main.js
  删除：import mockjs
```
---
