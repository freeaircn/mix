# APP Worknotes

---
### Requirements  
1. Http API请求和响应
2. 路由
3. 角色
4. 用户
5. 用户权限控制


8. 编写用户头像功能    

11. 用户认证，访问api权限认证  
    [done]前端动态获取后端路由表   
    [done]后端用户验证，权限验证    
    用户头像   

. 后端，用户数据/文件的存放文件位置，和访问权限。  
  数据库权限，centos文件路径权限  

---

### TOP
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
### 6. 编写用户头像功能
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
    
    # 使用el-upload
        <el-upload
          class="avatar-uploader"
          action="http://127.0.0.1/api/avatar/update" // 服务端地址
          :show-file-list="false"
          list-type="picture"
          :on-success="handleAvatarSuccess"
          :before-upload="beforeAvatarUpload"
        >
          <img v-if="imageUrl" :src="imageUrl" class="avatar">
          <i v-else class="el-icon-plus avatar-uploader-icon" />
        </el-upload>

  # 后端
    使用library->upload
      public function update_post() // 控制器使用restserver类
      {
          $config['upload_path']   = './resource/avatar/';   // 存放文件相对路径，注：路径是相对于你网站的 index.php 文件的，而不是相对于控制器或视图文件。
          $config['allowed_types'] = 'gif|jpg|png';
          $config['max_size']      = 100;
          $config['max_width']     = 1024;
          $config['max_height']    = 768;

          $this->load->library('upload', $config);

          if (!$this->upload->do_upload('file')) {  // 接收上传
              $res['code'] = 300;
              $res['msg']  = $this->upload->display_errors();

          } else {
              $res['code'] = App_Code::SUCCESS;
              $res['data'] = $this->upload->data();   // 接收完毕的结果
          }

          $this->response($res, 200);
      }
    
    # CI路径定义  位于index.php
      APPPATH: "D:\www\binglang\server\application\app\"
      BASEPATH: "D:\www\binglang\server\system\"
      FCPATH: "D:\www\binglang\server\"
      SELF: "index.php"
      SYSDIR: "system"
      
      chown -R apache:apache /var/www/html/binglang/server/resource/avatar
      chmod -R 777 /var/www/html/binglang/server/resource/avatar
  
```



---
### 11、修改数据库用户信息表
```
2020-06-01
  1 初始方案里，用户信息的某个属性取自数据库数据字典。
    优点：增加/取消某个属性，用户信息user表不会变化。
    缺点：CURD操作时，操作数据字典复杂，后端验证输入复杂。相关的数据表有4个：user, user_attribute, dict, dict_data
    
  2 修改方案：用户信息user表预留空白属性字段。
    1 数据表中预留属性的 数据类型包含 INT 和 VCHAR
    2 INT数据类型的属性，适用于有共同备选项的，比如：籍贯属性，可能是“保山”，或“腾冲”
    3 VCHAR数据类型的属性，适用于独有的属性值，没有备选项，比如：姓名
    4 启用一个INT数据类型属性，需新建对应的一个属性备选项的数据表。比如：用户信息增加“政治面貌”。
    5 预留INT类型 - 12个，VCHAR类型 - 8个
      `attr_01_id` int(11) UNSIGNED DEFAULT NULL COMMENT '部门',
      `attr_02_id` int(11) UNSIGNED DEFAULT NULL COMMENT '岗位',
      `attr_03_id` int(11) UNSIGNED DEFAULT NULL COMMENT '政治面貌',
      `attr_04_id` int(11) UNSIGNED DEFAULT NULL COMMENT '职称',
      `attr_05_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
      `attr_06_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
      `attr_07_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
      `attr_08_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
      `attr_09_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
      `attr_10_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
      `attr_11_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
      `attr_12_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
      `attr_text_01` varchar(63) DEFAULT NULL COMMENT '预留',
      `attr_text_02` varchar(63) DEFAULT NULL COMMENT '预留',
      `attr_text_03` varchar(63) DEFAULT NULL COMMENT '预留',
      `attr_text_04` varchar(63) DEFAULT NULL COMMENT '预留',
      `attr_text_05` varchar(63) DEFAULT NULL COMMENT '预留',
      `attr_text_06` varchar(63) DEFAULT NULL COMMENT '预留',
      `attr_text_07` varchar(63) DEFAULT NULL COMMENT '预留',
      `attr_text_08` varchar(63) DEFAULT NULL COMMENT '预留',  
      
    6 前端页面显示：列表区域显示 label，表单区域下拉列表要关联 id，则，例如后端查询数据库后，组装res {dept_label, attr_01_id }
      
```

---
### 12. 用户登录功能，api权限验证，授权页面访问控制
```
2020-09-03
  1 方案
    1 后端管理员创建用户，暂不支持用户注册。
    2 登录：link - www.xxx.yy\login，api接口 - 后端控制器auth，控制器方法login_post
    3 后端验证用户访问权限，采用CI的session库保存用户登陆会话。
      1 当使用CI的session库，CI强制cookie httponly，前端js无法读取cookie。
      2 用户从前端请求登陆，后端验证用户合法后，返回CI的session cookie失效时间，前端用该失效时间创建cookie。这样，前端，后端创建的cookie的生命周期一致。
        1 选择 关闭浏览器，用户登录信息失效，即session cookie，有效期至浏览器关闭。
        2 后端CI session config，设置sess_expiration = 0
        3 前端，js-cookie创建cookie时，不填expire参数。
      3 前端的路由切换控制模块，查找前端创建的cookie是否存在，以控制路由页面切换。
      4 当检测到用户登陆失效后，清理之前登陆保存的数据，如 router表，store
      
    4 PHP版本>7，密码hash使用argon2
    5 访问控制
      1 获取用户拥有的权限，用户登录验证通过后，由user_id查询user_role表 - role_id，查询role_menu表 - menu_id，查询menu表 - roles，得到用户拥有的api请求列表，例如dept:get，写入CI的session data。
      
      2 api请求控制
        1 api请求采用restful样式，url样式，例如 www.xxx.yy\api\dept，请求方法：get，post，put，delete
          1 app_menu数据表的“roles”字段，写入字符串约定：
            xx:get
            xx:post
            xx:get
            xx:delete
            其中：
            xx 即CI API控制器的名称，即http request url字段
            get,post.. 对应CI控制器方法中的CRUD操作，也是http request方法
            
        2 自定义类App_Rest_API，继承restserver类，在构造函数中，验证api请求的权限。调试时添加超级用户，超级用户拥有所有api请求权限。
          1 查询session data，检查用户登录状态。
          2 从http请求的url和method，提取请求信息，例如dept:get。再比对session data保存的acl列表。
          3 不是所有API都需要鉴权，比如login，register所有用户都可以访问。
      
      3 页面请求控制
        1 例如，用户有dept:get权限，该用户就可以查看dept的数据，即需要对用户显示该页面。
        2 从menu表中读取type = 1，且包含acl的页面路由，生成前端的路由表结构发给前端。
        3 前端收到路由表，动态添加。
        4 store.auth中定义请求页面路由标志位 - state.reqMenu
          1 初始reqMenu = false
          2 login方法验证用户登录请求通过后，置reqMenu = true
          3 前端路由控制permission.js中，路由before处理中检查reqMenu == true，则向后端请求页面路由表，并置reqMenu = false。

          
    6 【取消】用户X天免登陆 （2020-09-08）
      1 login页面供用户选择“X天免登陆”选项，若用户勾选了“五天免登陆”，则前端，后端设置cookie相同的有效期，否则，前台不设置cookie有效时间。js-cookie不设置失效时间，则成为session-cookie，浏览器关闭销毁。
    
    7 忘记密码功能
      1 发送验证码至账号绑定的邮箱，验证码验证通过，则允许设置新密码。
      2 若用户未绑定邮箱，则无法验证。
      3 流程：
        1 输入手机号
        2 检查手机号和邮箱地址
        3 生成验证码，发送邮件
        4 输入验证码，提交后端验证
        5 验证正确，前端页面显示重置密码
    
    8 访客
      1 不增设“访客”账号，前端初始定义 访客可以访问的页面。
      
  2 场景：
    1 刷新页面
      1 前端js生成的cookie不消失，但有失效时间限制。
      2 vue store中存储的数据消失，包含：用户信息 user，用户可访问的页面路由表 - routes， 请求页面路由标志位 - reqMenu
        1 前端路由控制permission.js中，检查store.getters.user === null，说明vue store user已被清空，向后端请求check_user，请求build_menu。
        
    2 新窗口输入网址链接
      同1
      
    3 关闭浏览器
      同1
      1 选择 关闭浏览器，用户登录信息失效，即session cookie，有效期至浏览器关闭。
      2 在layout/AppMain.vue中，监听浏览器关闭事件，调用store\auth\logout清除前台，后台存储的用户登录信息。
      3 异常场景：
        1 浏览器非正常关闭
        2 后端未收到logout请求
      
    4 请求失败或返回异常处理
      1 auth/login请求，弹窗提示信息，页面停留在login页面
      2 auth/check_user请求，弹窗提示信息，调用auth/logout（方法中rest routes表，另一种方法：强制刷新页面location.reload()），页面切换到login页面
      3 menu/build_menu请求，弹窗提示信息，调用auth/logout，页面切换到login页面
    
  3 session简介
    1 session一般来说要配合cookie使用，如果用户浏览器禁用了cookie，那么只能使用URL重写来实现session的存储功能
    2 过程
      1 用户第一次请求服务器时，服务器端会生成一个sessionid
      2 服务器端将生成的sessionid返回给客户端，通过set-cookie
      3 客户端收到sessionid会将它保存在cookie中，当客户端再次访问服务端时会带上这个sessionid
      4 当服务端再次接收到来自客户端的请求时，会先去检查是否存在sessionid，不存在就新建一个sessionid重复1,2的流程，如果存在就去遍历服务端的session文件，找到与这个sessionid相对应的文件，文件中的键值便是sessionid，值为当前用户的一些信息
      5 此后的请求都会交换这个 Session ID，进行有状态的会话。
    
  4 修改框架文件：
    1 layout:
      sidebar: state => state.app.sidebar,
      device: state => state.app.device,
      showSettings: state => state.settings.showSettings,
      needTagsView: false,
      fixedHeader: state => state.settings.fixedHeader

      this.$store.dispatch('app/closeSideBar', { withoutAnimation: false })
      
    2 Navbar:
    ...mapGetters([
          'sidebar',
          'device'
        ])
        
      this.$store.dispatch('app/toggleSideBar')
      await this.$store.dispatch('user/logout')
          this.$router.push(`/login?redirect=${this.$route.fullPath}`)
          
    3 AppMain
      this.$store.state.tagsView.cachedViews
      
    4 ResizeHandler.js  Mixin
      store.dispatch('app/closeSideBar', { withoutAnimation: false })
      store.dispatch('app/toggleDevice', 'mobile')

    所需store：
    app
    settings
    user

```

---
### 
```

```

---
### 
```

```