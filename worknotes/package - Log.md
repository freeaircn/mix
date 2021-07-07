# LAMP环境
---

---
### 1 后端log
```
  1 Windows
    1 将下载的配置包解压，拷贝php_seaslog.dll到对应版本php的ext文件下
    
    2 php.ini文件添加扩展，配置
      extension=php_seaslog.dll
      seaslog.default_basepath="D:/www/binglang/server/application/app/logs"
      seaslog.default_logger=default
      seaslog.default_datetime_format = "Y-m-d H:i:s"
      seaslog.default_template = "%T (%t) [%L] [%F] [%C] %P | %Q | %R | %m | %M "
      seaslog.disting_folder = 1
      seaslog.disting_type=0
      seaslog.disting_by_hour=0
      seaslog.use_buffer=1
      seaslog.buffer_size=100
      seaslog.level=8
      seaslog.trace_error=1
      seaslog.trace_exception=0
  
  2 Centos
    1 参考 https://github.com/Neeke/SeasLog

    2 下载，安装
      wget https://pecl.php.net/get/SeasLog-2.1.0.tgz 
      tar -zxvf SeasLog-2.1.0.tgz 
      cd SeasLog-2.1.0
      phpize
      ./configure 
      make && make install
    
    3 添加扩展，配置
      cd /etc/php.d
      vi 60-seaslog.ini
        
      文件写入：
      extension = seaslog.so        
      
      seaslog.default_basepath="/var/log/www/app"
      seaslog.default_logger=default
      seaslog.default_datetime_format = "Y-m-d H:i:s"
      seaslog.default_template = "%T [%L] [%P] [%Q] | %M "
      seaslog.disting_folder = 1
      seaslog.disting_type=0
      seaslog.disting_by_hour=0
      seaslog.use_buffer=1
      seaslog.buffer_size=100
      seaslog.level=8
      seaslog.trace_error=1
      seaslog.trace_exception=0
      
      示例：
      2021-01-09 16:27:41 [NOTICE] [12284] [5ff968fce5c95] | at3dir83 | 138****5678 | auth-login | login successfully. 
      2021-01-09 16:27:43 [NOTICE] [12284] [5ff968ffe43d4] | at3dir83 | 138****5678 | auth-logout | logout. 
      
      12284 - ProcessId 进程ID
      5ff968fce5c95 - RequestId 区分单次请求
      at3dir83 - trace id
      auth-login - 写日志位置
      login successfully. - 日志内容
        
    4 修改日志文件夹和文件权限，使用者apache    
      chown -R apache:apache /var/log/www/app
      chmod -R 766 /var/log/www/app
      
    5 配置说明     
      # 格式
        seaslog.default_template = "%T (%t) [%L] [%F] [%C] %P | %Q | %R | %m | %M "
        SeasLog提供了下列预设变量，可以直接使用在日志模板中，将在日志最终生成时替换成对应值。
        %L - Level 日志级别。
        %M - Message 日志信息。
        %T - DateTime 如2017-08-16 19:15:02，受seaslog.default_datetime_format影响。
        %t - Timestamp 如1502882102.862，精确到毫秒数。
        %Q - RequestId 区分单次请求，如没有调用SeasLog::setRequestId($string)方法，则在初始化请求时，采用内置的static char *get_uniqid()方法生成的惟一值。
        %H - HostName 主机名。
        %P - ProcessId 进程ID。
        %D - Domain:Port 域名:口号，如www.cloudwise.com:8080; Cli模式下为cli。
        %R - Request URI 请求URI，如/app/user/signin; Cli模式下为入口文件，如CliIndex.php。
        %m - Request Method 请求类型，如GET; Cli模式下为执行命令，如/bin/bash。
        %I - Client IP 来源客户端IP; Cli模式下为local。取值优先级为：HTTP_X_REAL_IP > HTTP_X_FORWARDED_FOR > REMOTE_ADDR
        %F - FileName:LineNo 文件名:行号，如UserService.php:118。
        %U - MemoryUsage 当前内容使用量，单位byte。调用zend_memory_usage。
        %u - PeakMemoryUsage 当前内容使用峰值量，单位byte。调用zend_memory_peak_usage。
        %C - Class::Action 类名::方法名，如UserService::getUserInfo。不在类中使用时，记录函数名
      
      # level
        seaslog.level = 8 记录的日志级别.默认为8,即所有日志均记录。
        seaslog.level = 0 记录EMERGENCY。
        seaslog.level = 1 记录EMERGENCY、ALERT。
        seaslog.level = 2 记录EMERGENCY、ALERT、CRITICAL。
        seaslog.level = 3 记录EMERGENCY、ALERT、CRITICAL、ERROR。
        seaslog.level = 4 记录EMERGENCY、ALERT、CRITICAL、ERROR、WARNING。
        seaslog.level = 5 记录EMERGENCY、ALERT、CRITICAL、ERROR、WARNING、NOTICE。
        seaslog.level = 6 记录EMERGENCY、ALERT、CRITICAL、ERROR、WARNING、NOTICE、INFO。
        seaslog.level = 7 记录EMERGENCY、ALERT、CRITICAL、ERROR、WARNING、NOTICE、INFO、DEBUG。
        
      # 
        \SeasLog::debug($message, $context, $module)
        \SeasLog::info($message, $context, $module)
        \SeasLog::notice($message, $context, $module)
        \SeasLog::warning($message, $context, $module)
        \SeasLog::error($message, $context, $module)
        \SeasLog::critical($message, $context, $module)
        \SeasLog::alert($message, $context, $module)
        \SeasLog::emergency($message, $context, $module)
        使用演示如下：

        SeasLog::debug('this is a {userName} debug',array('{userName}' => 'neeke'));
      
      CI DB调试error显示控制
        application/app/config/database.php文件
        ['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
        production - db_debug = FALSE
        
        获取DB error
        $error = $this->db->error(); // Has keys 'code' and 'message'
        
  TODO: 
    log展示
```

---
### 2 前端log
```
  【2021-01-09 取消前端记录log】

  逻辑
  1 流程：
    # error钩子：window，vue， pormiss
    # client缓存log，indexDB
    # client清理log缓存
    # 需用户手动提交缓存log。考虑日志数据多，取最近x条log。后台下发x，x暂定10条。
    # 后端响应存储log，写文件，写db？
    
  2 log字段
    # 用户会话ID，与后台对应
      Seaslog::getRequestID
    
  3 log页面
  
    isMobile = (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()));
  
  格式：
  timestamp | 会话 | level | description | log信息
    log信息 {
      url
      message
      name
      stack
      ua
    }
  
  实现
  1 vue errorHandler ：
    # Vue.config.errorHandler = function(err) {
      const {
        message, // 异常信息
        name, // 异常名称
        stack // 异常堆栈信息
      } = err
  
    # 示例打印：
      {message: "b is not defined", name: "ReferenceError", stack: "ReferenceError: b is not defined?    at a.handleQu…e/dist/static/js/chunk-libs.9e2f0126.js:18:51770)"}
        message : "b is not defined"
        name : "ReferenceError"
        stack : "ReferenceError: b is not defined?    at a.handleQuery (http://127.0.0.1/resource/dist/static/js/chunk-b7754a92.7d401a55.js:1:13133)?    at ne (http://127.0.0.1/resource/dist/static/js/chunk-libs.9e2f0126.js:18:11664)?    
      window.location.href : http://127.0.0.1/admin/user
      
  2 client缓存log
    # npm引入logline
      npm install logline
      
    # js文件 引入logline
      import logLine from 'logline';
      
    # 使用
      Logline.using(Logline.PROTOCOL.INDEXEDDB, 'binglang')
      Logline.keep(1)

      var appLogger = new Logline('app')
      appLogger.info('description')
      appLogger.error('description', { a: '' })
      appLogger.warn('description')
      appLogger.critical('description', { b: '' })
      
    # 示例打印：
      appLogger.error('vue error', { message: message, name: name, stack: stack, url: url })
      
      data: {message: "b is not defined", name: "ReferenceError", stack: "ReferenceError: b is not defined?    at a.handleQu…e/dist/static/js/chunk-libs.fc1beddb.js:18:51770)", url: "http://127.0.0.1/admin/user"}
      descriptor: "vue error"
      level: "error"
      namespace: "app"
      time: 1579620015353
  
  TODO: 
    手动上报log至后端
```