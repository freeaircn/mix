# 发布前Check List

---
### 说明  
检查Check List中每一项要求，满足后打包，将server文件夹上传至服务器.   
关键字: [G]-global  [v]-vue  [c]-ci

---
### Check List
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
1 修改生产环境账号密码  
  数据库连接config文件-server\application\config\database.php
  
2 修改base_url
  文件server\application\config\config.php
  # $config['base_url'] = 'http://127.0.0.1';
```

