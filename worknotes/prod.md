# 合入

---
***1. 备份***
```
1 备份dev 数据库

2 下载prod 数据库备份

3 下载prod http server文件夹
```

**2. dev前端打包***
```
1 npm run build 
```

**3. dev后端打包***
```
1 .env 修改DB设置

2 修改base_url
  文件server\application\config\config.php
  # $config['base_url'] = 'http://127.0.0.1';
  
3 删除dev文件
  路径：
  \server\writable\debugbar\
  \server\writable\logs\
  \server\writable\session\
```

**4. 合入***
```
1 手动复制prod后端文件至dev后端 
  路径：
  \server\public\avatar\
  \server\writable\uploads\
  
2 将dev数据表合入prod数据库中
  表x
  表y
  ...

3 将dev后端server文件夹 上传至prod /var/www/html/mix/
  先删除旧版本：rm -rf /var/www/html/mix/server
  再上传

4 prod 修改server文件夹属性
  chown -R apache:apache /var/www/html/mix/server
  [可选] chmod -R 755 /var/www/html/mix/server/writable/


```

---
***5. 其他1***
```
1 修改前端框架    
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

```

---
***6. 其他2***
```
1 数据库
  create user ssh_auto@localhost identified by 'Meter@2021';

  grant all on mix.app_meter to ssh_auto@localhost;
  grant Select on mix.app_generator_event_log to ssh_auto@localhost;
  grant Select on mix.app_kwh_planning to ssh_auto@localhost;
  flush privileges;

  show grants for ssh_auto@localhost;
  
2 mixcenter
  KNOBFTKWXKSXNNTI
  LTPVNUPFJAFAGXKD
```

