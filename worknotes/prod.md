# Prod Check List

---
***1. vue部分***
```
1 修改VUE打包config    
  文件 client\src\main.js
  /**
   * If you don't want to use mock-server
   * you want to use MockJs for mock api
   * you can execute: mockXHR()
   *
   * Currently MockJs will be used in the production environment,
   * please remove it before going online! ! !
   */
  // import { mockXHR } from '../mock'
  // if (process.env.NODE_ENV === 'production') {
  //   mockXHR()
  // }

2 修改base_api
  文件：client\.env.production
  # base api
  VUE_APP_BASE_API = '/prod-api'
  改为：
  # base api
  VUE_APP_BASE_API = 'http://127.0.0.1'
```

---
***2. ci部分***
```
1 修改pwd
  
2 修改base_url
  文件server\application\config\config.php
  # $config['base_url'] = 'http://127.0.0.1';
  
3
  rm -rf /var/www/html/mix/server
  chown -R apache:apache /var/www/html/mix/server
  [optional] chmod -R 755 /var/www/html/mix/server/writable/
  
4
  create user ssh_auto@localhost identified by 'Meter@2021';

  grant all on mix.app_meter to ssh_auto@localhost;
  grant Select on mix.app_generator_event_log to ssh_auto@localhost;
  flush privileges;

  show grants for ssh_auto@localhost;
```

