# LAMP����
---

---
### 1 ���log
```
  1 Windows
    1 �����ص����ð���ѹ������php_seaslog.dll����Ӧ�汾php��ext�ļ���
    
    2 php.ini�ļ������չ������
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
    1 �ο� https://github.com/Neeke/SeasLog

    2 ���أ���װ
      wget https://pecl.php.net/get/SeasLog-2.1.0.tgz 
      tar -zxvf SeasLog-2.1.0.tgz 
      cd SeasLog-2.1.0
      phpize
      ./configure 
      make && make install
    
    3 �����չ������
      cd /etc/php.d
      vi 60-seaslog.ini
        
      �ļ�д�룺
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
      
      ʾ����
      2021-01-09 16:27:41 [NOTICE] [12284] [5ff968fce5c95] | at3dir83 | 138****5678 | auth-login | login successfully. 
      2021-01-09 16:27:43 [NOTICE] [12284] [5ff968ffe43d4] | at3dir83 | 138****5678 | auth-logout | logout. 
      
      12284 - ProcessId ����ID
      5ff968fce5c95 - RequestId ���ֵ�������
      at3dir83 - trace id
      auth-login - д��־λ��
      login successfully. - ��־����
        
    4 �޸���־�ļ��к��ļ�Ȩ�ޣ�ʹ����apache    
      chown -R apache:apache /var/log/www/app
      chmod -R 766 /var/log/www/app
      
    5 ����˵��     
      # ��ʽ
        seaslog.default_template = "%T (%t) [%L] [%F] [%C] %P | %Q | %R | %m | %M "
        SeasLog�ṩ������Ԥ�����������ֱ��ʹ������־ģ���У�������־��������ʱ�滻�ɶ�Ӧֵ��
        %L - Level ��־����
        %M - Message ��־��Ϣ��
        %T - DateTime ��2017-08-16 19:15:02����seaslog.default_datetime_formatӰ�졣
        %t - Timestamp ��1502882102.862����ȷ����������
        %Q - RequestId ���ֵ���������û�е���SeasLog::setRequestId($string)���������ڳ�ʼ������ʱ���������õ�static char *get_uniqid()�������ɵ�Ωһֵ��
        %H - HostName ��������
        %P - ProcessId ����ID��
        %D - Domain:Port ����:�ںţ���www.cloudwise.com:8080; Cliģʽ��Ϊcli��
        %R - Request URI ����URI����/app/user/signin; Cliģʽ��Ϊ����ļ�����CliIndex.php��
        %m - Request Method �������ͣ���GET; Cliģʽ��Ϊִ�������/bin/bash��
        %I - Client IP ��Դ�ͻ���IP; Cliģʽ��Ϊlocal��ȡֵ���ȼ�Ϊ��HTTP_X_REAL_IP > HTTP_X_FORWARDED_FOR > REMOTE_ADDR
        %F - FileName:LineNo �ļ���:�кţ���UserService.php:118��
        %U - MemoryUsage ��ǰ����ʹ��������λbyte������zend_memory_usage��
        %u - PeakMemoryUsage ��ǰ����ʹ�÷�ֵ������λbyte������zend_memory_peak_usage��
        %C - Class::Action ����::����������UserService::getUserInfo����������ʹ��ʱ����¼������
      
      # level
        seaslog.level = 8 ��¼����־����.Ĭ��Ϊ8,��������־����¼��
        seaslog.level = 0 ��¼EMERGENCY��
        seaslog.level = 1 ��¼EMERGENCY��ALERT��
        seaslog.level = 2 ��¼EMERGENCY��ALERT��CRITICAL��
        seaslog.level = 3 ��¼EMERGENCY��ALERT��CRITICAL��ERROR��
        seaslog.level = 4 ��¼EMERGENCY��ALERT��CRITICAL��ERROR��WARNING��
        seaslog.level = 5 ��¼EMERGENCY��ALERT��CRITICAL��ERROR��WARNING��NOTICE��
        seaslog.level = 6 ��¼EMERGENCY��ALERT��CRITICAL��ERROR��WARNING��NOTICE��INFO��
        seaslog.level = 7 ��¼EMERGENCY��ALERT��CRITICAL��ERROR��WARNING��NOTICE��INFO��DEBUG��
        
      # 
        \SeasLog::debug($message, $context, $module)
        \SeasLog::info($message, $context, $module)
        \SeasLog::notice($message, $context, $module)
        \SeasLog::warning($message, $context, $module)
        \SeasLog::error($message, $context, $module)
        \SeasLog::critical($message, $context, $module)
        \SeasLog::alert($message, $context, $module)
        \SeasLog::emergency($message, $context, $module)
        ʹ����ʾ���£�

        SeasLog::debug('this is a {userName} debug',array('{userName}' => 'neeke'));
      
      CI DB����error��ʾ����
        application/app/config/database.php�ļ�
        ['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
        production - db_debug = FALSE
        
        ��ȡDB error
        $error = $this->db->error(); // Has keys 'code' and 'message'
        
  TODO: 
    logչʾ
```

---
### 2 ǰ��log
```
  ��2021-01-09 ȡ��ǰ�˼�¼log��

  �߼�
  1 ���̣�
    # error���ӣ�window��vue�� pormiss
    # client����log��indexDB
    # client����log����
    # ���û��ֶ��ύ����log��������־���ݶ࣬ȡ���x��log����̨�·�x��x�ݶ�10����
    # �����Ӧ�洢log��д�ļ���дdb��
    
  2 log�ֶ�
    # �û��ỰID�����̨��Ӧ
      Seaslog::getRequestID
    
  3 logҳ��
  
    isMobile = (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()));
  
  ��ʽ��
  timestamp | �Ự | level | description | log��Ϣ
    log��Ϣ {
      url
      message
      name
      stack
      ua
    }
  
  ʵ��
  1 vue errorHandler ��
    # Vue.config.errorHandler = function(err) {
      const {
        message, // �쳣��Ϣ
        name, // �쳣����
        stack // �쳣��ջ��Ϣ
      } = err
  
    # ʾ����ӡ��
      {message: "b is not defined", name: "ReferenceError", stack: "ReferenceError: b is not defined?    at a.handleQu��e/dist/static/js/chunk-libs.9e2f0126.js:18:51770)"}
        message : "b is not defined"
        name : "ReferenceError"
        stack : "ReferenceError: b is not defined?    at a.handleQuery (http://127.0.0.1/resource/dist/static/js/chunk-b7754a92.7d401a55.js:1:13133)?    at ne (http://127.0.0.1/resource/dist/static/js/chunk-libs.9e2f0126.js:18:11664)?    
      window.location.href : http://127.0.0.1/admin/user
      
  2 client����log
    # npm����logline
      npm install logline
      
    # js�ļ� ����logline
      import logLine from 'logline';
      
    # ʹ��
      Logline.using(Logline.PROTOCOL.INDEXEDDB, 'binglang')
      Logline.keep(1)

      var appLogger = new Logline('app')
      appLogger.info('description')
      appLogger.error('description', { a: '' })
      appLogger.warn('description')
      appLogger.critical('description', { b: '' })
      
    # ʾ����ӡ��
      appLogger.error('vue error', { message: message, name: name, stack: stack, url: url })
      
      data: {message: "b is not defined", name: "ReferenceError", stack: "ReferenceError: b is not defined?    at a.handleQu��e/dist/static/js/chunk-libs.fc1beddb.js:18:51770)", url: "http://127.0.0.1/admin/user"}
      descriptor: "vue error"
      level: "error"
      namespace: "app"
      time: 1579620015353
  
  TODO: 
    �ֶ��ϱ�log�����
```