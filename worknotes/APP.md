# APP Worknotes

---
### 功能  
1. Http API请求和响应
2. 路由
3. 角色
4. 用户
5. 用户权限控制
6. 忘记密码页面
7. 用户个人信息设置  

99. 后端，用户数据/文件的存放文件位置，和访问权限。  
    数据库权限，centos文件路径权限  
---

### 置顶
```
# DB
  1 外键字段，比如外键某个id，前端表单没有输入，后端收到的是''，写数据表时，将报错。
  2 select语句完整语法
      SELECT 
      DISTINCT <select_list>
      FROM <left_table>
      <join_type> JOIN <right_table>
      ON <join_condition>
      WHERE <where_condition>
      GROUP BY <group_by_list>
      HAVING <having_condition>
      ORDER BY <order_by_condition>
      LIMIT <limit_number>
      
      from →join →on →where →group by→having→select→order by→limit
      
# VUE
  1 vue子组件props类型-Object，是父组件传入对象的引用。
  2 vue 修饰符sync的功能是：当一个子组件改变了一个 prop 的值时，这个变化也会同步到父组件中所绑定。
  
```

---
### 1 Http API请求和响应
```  
  1 API POST请求和响应的数据，使用json序列化。

  2 前端axios组件
    1 设置 base url
      url = base url + request url
      文件：\src\utils\request.js
      baseURL: process.env.VUE_APP_API_BASE_URL,
      
      设置POST请求头
      request.defaults.headers.post['Content-Type'] = 'application/json'
      
    2 请求拦截
      Post请求转换请求，使用json。qs 序列化，undefined或空数组，axios post 提交时，qs不填入http body。
      request.defaults.transformRequest = [function (data) {
        // return qs.stringify(data, { arrayFormat: 'indices' })
        return JSON.stringify(data)
      }]
      
      Get请求，指定请求参数序列号方法
      request.defaults.paramsSerializer = function (params) {
        return qs.stringify(params, { arrayFormat: 'indices' })
      }
      
      默认情况下，axios将JavaScript对象序列化为JSON。 要以application / x-www-form-urlencoded格式发送数据，您可以使用以下选项之一。
    
    3 响应拦截
      文件：\src\utils\request.js
      service.interceptors.response.use(
        response => { 
          // http状态码在2xx内的处理
        },
        error => {
          // http状态码在2xx外的处理
        }
      
      应用业务返回的提示msg，统一在拦截中，通过notification组件显示
      
      返回api：
        apiGetUser(params)
          .then(function(data) {
            // 成功
            this.data = data
          }.bind(this))
          .catch(function(err) {
            // 失败
            
          })
    
  2 CI 4
    1 读取请求数据
      $client = $this->request->getJSON(true);
      默认情况下，这会返回一个 JSON 数据对象。如果你需要返回一个关联数组，请传递 true 作为第一个参数。
    
    2 响应定义
      CI 4提供功能：use CodeIgniter\API\ResponseTrait;
      respond($res, http_status_code);
      如果 $res 是一个字符串，它将被当作 HTML 发送回客户端。
      如果 $res 是一个数组，它将尝试请求内容类型与客户端进行协商，默认为 JSON。如果没有在 ConfigAPI.php 中配置内容。默认使用 $supportedResponseFormats 属性。
      # 
      $res['code'] - 必填，应用业务自定义的返回码，区别于Http请求的状态码
      $res['msg'] - 非必填，应用处理结果的提示，前端可以使用弹出消息窗显示该提示。
      $res['data'] - 非必填，应用处理结果返回的数据。其中，数据库的查询输出结果的数据类型是 关联数组。
    
      # 示例：
        # 成功 
          res['code'] = App_Code::SUCCESS
          res['data'] =
        # 成功，比如 删除操作
          res['code'] = App_Code::SUCCESS
          res['msg'] =
        # DB操作失败
          res['code'] = App_Code::FAILED_CODE
          res['msg'] = 
        # 流程失败
          res['code'] = App_Code::FAILED_CODE
          res['msg'] = 
  
  3 Http状态码
    # 200 表示操作成功，但是不同的方法可以返回更精确的状态码。
      GET: 200 OK
      POST: 201 Created
      PUT: 200 OK
      PATCH: 200 OK
      DELETE: 204 No Content
      上面代码中，POST返回201状态码，表示生成了新的资源；DELETE返回204状态码，表示资源已经不存在。
      此外，202 Accepted状态码表示服务器已经收到请求，但还未进行处理，会在未来再处理，通常用于异步操作

    # 300
      API 用不到301状态码（永久重定向）和302状态码（暂时重定向，307也是这个含义），因为它们可以由应用级别返回，浏览器会直接跳转，API 级别可以不考虑这两种情况。
      API 用到的3xx状态码：
      303 See Other，表示参考另一个 URL，是"暂时重定向"，用于POST、PUT和DELETE请求，收到303以后，浏览器不会自动跳转，而会让用户自己决定下一步怎么办。
      302和307也是"暂时重定向"，用于GET请求。

    # 4xx，表示客户端错误。
      400 Bad Request：服务器不理解客户端的请求，未做任何处理。
      401 Unauthorized：用户未提供身份验证凭据，或者没有通过身份验证。
      403 Forbidden：用户通过了身份验证，但是不具有访问资源所需的权限。
      404 Not Found：所请求的资源不存在，或不可用。
      405 Method Not Allowed：用户已经通过身份验证，但是所用的 HTTP 方法不在他的权限之内。
      410 Gone：所请求的资源已从这个地址转移，不再可用。
      415 Unsupported Media Type：客户端要求的返回格式不支持。比如，API 只能返回 JSON 格式，但是客户端要求返回 XML 格式。
      422 Unprocessable Entity ：客户端上传的附件无法处理，导致请求失败。
      429 Too Many Requests：客户端的请求次数超过限额。

    # 5xx，表示服务端错误。一般来说，API 不会向用户透露服务器的详细信息，所以只要两个状态码就够了。
      500 Internal Server Error：客户端请求有效，服务器处理时发生了意外。
      503 Service Unavailable：服务器无法处理请求，一般用于网站维护状态。
 
```

---
### 2 路由
```
  1 方案：
    SPA单页面应用，前端页面切换，由前端vue路由控制；CI 4只作为API服务，则CI只为API请求url定义路由。
  
  2 CI 4    
    文件：\server\mix\app\Config\Routes.php
    
    API请求:
    $routes->group('api', function ($routes) {
      $routes->add('auth/login', 'Home::login');
    });
    
    非API请求:
    指向Home::index()
    $routes->add('(:any)', 'Home::index');
    
  3 前端动态加载路由
    1 用户请求登录，后端验证成功，跳转至业务页面。
      此时，前端store用户权限数据为空，向后端请求。
      后端查询用户的“个人信息和权限信息”，生成包括：用户信息，头像文件，前端路由表。反馈给前端。
    
    
            前端                                          后端
    
        login请求
                                                        验证login
        验证通过，跳转页面
        
        store用户权限数据空，getUserinfo请求
                                                        查询用户的权限信息
    
        获取到用户信息，加载路由
    
    2 前端路由对象属性
      文件：\src\store\modules\async-router.js
          : \src\router\generator-routers.js
    
        `name` - 路由名称，必填，不能重名。
        `path` - url路径，必填。
        `component` - 组件的加载路径，必填。
        `redirect` - 重定向，选填。
        `hidden` - 侧边栏隐藏，默认false，选填。
        `hideChildrenInMenu` - 强制菜单显示为Item，默认false，选填。
        
        meta:
          `title` - 菜单显示的标题，必填。
          `icon` - 图标，选填。
          `keepAlive` - 缓存该路由 (开启 multi-tab 是默认值为 true)，默认false，选填。
          `hiddenHeaderContent` - 隐藏面包屑和页面标题栏，默认false，选填。
          `permission` - antd权限，在Mix使用中，设置[]
          `target` - 打开到新窗口，默认''，选填。

```

---
### 3 角色
```
  1 角色定义，数据表结构:
      `name` varchar(31) NOT NULL COMMENT '名称',
      `alias` varchar(63) NOT NULL COMMENT '别名',
      `status` varchar(31) NOT NULL COMMENT '状态',
      `description` varchar(127) DEFAULT NULL COMMENT '说明',
  
  2 角色设置页面
  
  3 角色的权限，包含：页面路由的可访问性，API的可访问性（数据CRUD权限）。
    数据表结构：
    `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
    `menu_id` int(11) UNSIGNED NOT NULL COMMENT '菜单ID',
   
```

---
### 4 用户
```
  1 用户信息定义，数据表结构:
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `workID` varchar(63) NOT NULL COMMENT '工号',
    `username` varchar(10) NOT NULL COMMENT '中文名',
    `sex` varchar(3) NOT NULL COMMENT '女，男',
    `IdCard` varchar(31) DEFAULT NULL COMMENT '证件',
    `phone` varchar(15) NOT NULL,
    `email` varchar(63) NOT NULL,
    `status` varchar(3) NOT NULL COMMENT '启用或禁用',
    `forceChgPwd` varchar(3) NOT NULL DEFAULT '1' COMMENT '要求修改密码',
    `avatar` int(11) UNSIGNED DEFAULT NULL COMMENT '头像ID',
    
    `deptLev0` int(11) DEFAULT 0 COMMENT '部门0',
    `deptLev1` int(11) DEFAULT 0 COMMENT '部门1',
    `deptLev2` int(11) DEFAULT 0 COMMENT '部门2',
    `deptLev3` int(11) DEFAULT 0 COMMENT '部门3',
    `deptLev4` int(11) DEFAULT 0 COMMENT '部门4',
    `deptLev5` int(11) DEFAULT 0 COMMENT '部门5',
    `deptLev6` int(11) DEFAULT 0 COMMENT '部门6',
    `deptLev7` int(11) DEFAULT 0 COMMENT '部门7',
    `job` int(11) UNSIGNED DEFAULT NULL COMMENT '岗位ID',
    `title` int(11) UNSIGNED DEFAULT NULL COMMENT '职称ID',
    `politic` int(11) UNSIGNED DEFAULT NULL COMMENT '政治面貌ID',
    
    `ip_address` varchar(63) DEFAULT NULL,
    `last_login` int(11) UNSIGNED DEFAULT NULL,
    
    `created_at` datetime DEFAULT NULL COMMENT '日期',
    `updated_at` datetime DEFAULT NULL COMMENT '日期',
    `deleted_at` datetime DEFAULT NULL COMMENT '日期',
    
    `id01` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id02` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id03` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id04` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id05` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id06` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id07` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id08` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id09` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id10` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id11` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id12` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id13` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id14` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id15` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
    `id16` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',

    `str01` varchar(63) DEFAULT NULL COMMENT '预留',
    `str02` varchar(63) DEFAULT NULL COMMENT '预留',
    `str03` varchar(63) DEFAULT NULL COMMENT '预留',
    `str04` varchar(63) DEFAULT NULL COMMENT '预留',
    `str05` varchar(63) DEFAULT NULL COMMENT '预留',
    `str06` varchar(63) DEFAULT NULL COMMENT '预留',
    `str07` varchar(63) DEFAULT NULL COMMENT '预留',
    `str08` varchar(63) DEFAULT NULL COMMENT '预留',
    `str09` varchar(63) DEFAULT NULL COMMENT '预留',
    `str10` varchar(63) DEFAULT NULL COMMENT '预留',
    `str11` varchar(63) DEFAULT NULL COMMENT '预留',
    `str12` varchar(63) DEFAULT NULL COMMENT '预留',
    `str13` varchar(63) DEFAULT NULL COMMENT '预留',
    `str14` varchar(63) DEFAULT NULL COMMENT '预留',
    `str15` varchar(63) DEFAULT NULL COMMENT '预留',
    `str16` varchar(63) DEFAULT NULL COMMENT '预留',

    `password` varchar(255) NOT NULL,
    `forgotten_password_selector` varchar(255) DEFAULT NULL,
    `forgotten_password_code` varchar(255) DEFAULT NULL,
    `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
    

  2 用户的公共属性：定义部门，岗位，政治面貌，职称。
    1 部门定义页面
    
    2 岗位定义页面
    
    3 职称定义页面
    
    4 政治面貌定义页面
    
  3 用户管理页面
    新加用户信息页面，修改已存在用户信息页面，使用单独的路由页面，不在主页面的侧边菜单栏显示。
    
    通过vue前端路由传递参数：
      新加用户时，传递uid = 0
      修改用户时，传递uid
    
    部门属性层级：8级，使用瀑布选择器组件。值0，为无效。

    检索，分页：查看前端和后端的具体实现。
```

---
### 5 用户权限控制
```
  1 登录状态
    1 用户请求登录，验证通过：
      后端记录用户登录状态：使用session。当执行session->ser()，CI自动将sessionid通过cookie返回浏览器。
      CI自动返回的cookie，其http only属性，js不能访问。
      
      session配置文件：\server\mix\app\Config\App.php
      文件：\server\mix\app\Controllers\BaseController.php
      初始化session:
      $this->session = \Config\Services::session();
      
      前端记录用户登录状态：js操作cookie。
      由于CI的session cookie的属性时http only，用户验证通过后，后端额外通过response返回token = md5(time())，前端收到响应后js存下token。
      
    2 前端-登录状态检查
      antd框架使用local Storage存储token，关闭浏览器或刷新浏览器页面，storage不会清空，除非清除浏览器数据。
      更换为cookie保存token，使用js-cookie组件，创建cookie时，不设置expire时间，则关闭浏览器自动删除cookie。
      npm install js-cookie --save
      
    3 cookie，session，vuex
      整个方案采用了cookie，session，vuex记录一些应用状态。
      
      js创建cookie时，不设置expire时间，关闭浏览器，自动删除该cookie。
      session保存在后端文件，手动调用session->destroy()删除，无自动删除。
      vuex保存在内存，浏览器刷新，vuex重新初始化。
      
      场景：
      1 刷新浏览器：
        cookie - 无变化
        session - 没有调用api方法影响session
        vuex - 重新初始化
      
      2 浏览器新标签1 已登录，打开新标签并打开网页：
        cookie - 无变化，没有被删除
        session - 没有调用api方法影响session
        vuex - 新标签的vuex，新初始化
      
      3 关闭浏览器：
        cookie - 被浏览器自动删除
        session - 没有调用api方法影响session
        vuex - 被删除
      
      4 点击退出按钮：
        cookie - 代码删除
        session - api方法logout，删除
        vuex - 代码初始化
    
    4 登录流程：
      1 前端，文件：\client\src\permission.js
      2 后端，文件：\server\mix\app\Controllers\Auth.php
        
                  前端                                                    后端
        
        查询cookie token
        
                      没有，跳转至login页面              ->             验证login。
                                                                  
                      cookie保存token，跳转页面          <-             通过，创建session，返回token。
                      
        有，查询store.user.roles
        
                      没有，api请求                      ->             检验session。
                      
                      vuex保存数据，动态加载路由表。     <-             通过，返回用户数据和允许访问的页面路由表。
        
        有，next()
        
  2 权限；
    1 包含：页面路由的可访问性，API的可访问性（数据CRUD权限）
      并不是所有的API 都要求鉴权 ，比如 login，logout，forget pwd等
    
    2 用户-角色-页面&API的关系
      给用户分配各种角色。
      给角色分配 允许访问的页面和允许的API。
      
    3 api请求
      1 api请求采用restful样式，url例如 www.xxx.yy\api\job，请求方法：get，post，put，delete
    
    4 数据库menu表定义
      1 menu包含两种类型，即允许访问的页面和允许的API
        type 1：对应前端页面，交给vue动态加载的路由表项。
        
        type 2：对应API，用户点击页面的按钮，触发向后端的API请求对某资源数据的CRUD操作。
        例如 www.xxx.yy\api\job：get，表示向后端请求 查询job数据。
        
        通常，页面用于展示某资源数据和提供修改数据窗口，即 页面和资源数据有 一定的对应关系。
        menu示例：
        id  type  pid name
        1   1     0   job
        2   2     1   get
        3   2     1   create
        
      2 menu数据表的“authority”字段，当type 2的menu，需要填写权限类型。例如：对job数据
          job:get
          job:post
          job:get
          job:delete
          
        menu示例：
        id  type  pid name     authority
        1   1     0   job
        2   2     1             job:get
        3   2     1             job:post
  
    5 角色分配 允许访问的页面和允许的API
      role_menu表，记录某个角色 授权的menu id，当menu type =1表示，允许访问的页面；当menu type =2表示，允许的API。
  
  3 角色-页面-API的后端处理：
    用户请求登录，后端验证通过，查询用户的角色，查找角色分配的menu id：
    1 menu type =1，即允许访问的页面。用途：生成前端路由表，反馈给前端。
    
    2 menu type =2，即允许的API。用途：生成ACL，保存在session中，供后端收到API请求后鉴权。并不是所有的API 都要求鉴权 ，比如register，login，logout，forget pwd等。
      
  4 CI 控制器前置过滤器，对API请求鉴权
    文件：
    \server\mix\app\Config\Filters.php
    \server\mix\app\Filters\AuthFilter.php
    \server\mix\app\Libraries\MixUtils.php

  5 角色分配权限页面，分两部分：
    1 menu type = 1，允许访问的前端页面
    2 menu type = 2，允许API访问的资源
    
    menu type = 0，用于辅助的显示，在tree列表里，不能被checkable
  
```

---
### 6. 忘记密码页面
```
  1 发送验证码至账号绑定的邮箱，验证码通过，则允许设置新密码。
  
  2 若用户未绑定邮箱，则无法验证。
  
  3 流程：
    1 输入手机号
    2 检查手机号和邮箱地址
    3 生成验证码，发送邮件
    4 输入验证码，提交后端验证
    5 验证正确，重置密码
    
```

---
### 7. 用户个人信息设置
```
  1 用户个人信息数据存放点：
    数据库
    session
    vuex
    
  2 修改，需保证3个地方的信息一致，有以下场景：
    1 关闭浏览器
      没有影响
    
    2 关闭浏览器标签
      没有影响
      
    3 刷新浏览页面
      vuex初始化，向后端请求，后端从session取出记录
      
    4 发送修改请求后，后端处理异常
      数据库异常，session异常？
    
    5 发送修改请求后，网络异常，前端未收到正确响应
      提示，延时刷新页面？
      
  3 修改流程：
    发送修改请求
    
    后端检验数据
    
    修改数据库
    
    修改session
    
    返回修改后数据
    
    前端修改vuex
    
    页面更新显示

  4 安全设置
    1 修改账号登录密码
      忘记登录密码，通过绑定邮箱获取验证码，可修改登录密码
      修改登录密码，需提供当前密码
      
      DB修改成功后，不用更新session，不用更新vuex
    
    2 修改手机号
      要求：一个手机号 对应 一个用户
      需提供密码
      DB修改成功后，更新session，更新vuex
    
    3 修改邮箱
      要求：一个邮箱 对应 一个用户
      发送验证码至新邮箱，需提供密码
      DB修改成功后，更新session，更新vuex
    
  5 修改头像
    # 前端
    
      <div class="ant-upload-preview" >
          <img :src="avatarImg"/>
          <div class="upload">
            <a-upload
              name="file"
              :action="uploadAction"
              :showUploadList="false"
              :before-upload="handleBeforeUploadAvatar"
              @change="handleUploadAvatarChange"
            >
              <a-button :loading="isUploading"> <a-icon type="upload" /> 更换头像 </a-button>
            </a-upload>
          </div>
        </div>
    
    前端上传文件组件，http请求不被axios拦截。后端处理过程中，遇到错误时，返回http的状态码不能时200，选择使用422.
      
  6 修改流程：
    前端选择文件，并上传。
    
    后端检查文件，并调整文件。
    
    修改数据库。
    
    修改session。
    
    返回修改后数据。
    
    前端修改vuex。
    
    页面更新显示。
    
  linux 文件权限
    chown -R apache:apache /var/www/html/binglang/server/resource/avatar
    chmod -R 777 /var/www/html/binglang/server/resource/avatar
```

---
### 包
```
  # 前端
    # 引入identicon.js，crypto
      npm install identicon.js --save
      npm install crypto --save 
      
      import crypto from 'crypto'
      import Identicon from 'identicon.js'
      
      var seed = Math.floor((Math.random() * 100) + 1)
      var hash = crypto.createHash('md5')
      hash.update(seed.toString())
      const data = new Identicon(hash.digest('hex'), 178).toString()
      this.imageUrl = 'data:image/png;base64,' + data
  
```

---
### 
```

```
---
### 
```

```