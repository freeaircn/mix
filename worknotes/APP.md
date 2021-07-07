# APP Worknotes

---
### Requirements  
1. Http API请求和响应
2. 路由
3. 角色和权限
4. 用户


4. [done]设定页面crud流程
5. 用户管理页面  
   [done]数据显示区分页，新增，编辑，删除操作，显示变化如何呈现用户  
   [done]用户管理页面 检索功能，适应多条件组合  
   [done]后端输入验证  
   [done]前端post, put, delete请求 content-type ；application/json，后端需支持json输入
6. 后端log  
8. [done]编写用户头像功能   
9. [done]table列动态显示/隐藏功能  
10. 参照用户管理页面，更新 app其他页面文件
    [done]创建search组件
    [done]search 组件，watch输入框，输入字母时，触发多次change事件，导致发往后端多条无效请求。  
    [done]search组件传递表单验证rule。   
    [done]基于el元素创建tree select单选组件
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
    
  3 动态路由
    1 用户请求登录，后端验证成功，使用session机制记录用户登录状态，此时，通过cookie返回前端。
      session自动处理，返回的cookie http only属性，js不能访问，则用户验证通过后，后端通过http响应手动返回token，前端js存下该token。
      
      session配置文件：\server\mix\app\Config\App.php
      
      文件：\server\mix\app\Controllers\BaseController.php
      初始化session:
      $this->session = \Config\Services::session();
      
      
      
    2 前端-登录状态检查
      antd框架使用local Storage存储token，关闭浏览器或刷新浏览器页面，storage不会清空，除非清除浏览器数据。
      更换使用cookie保存token，使用js-cookie组件，创建cookie时，不设置expire时间，则关闭浏览器自动删除cookie。
      npm install js-cookie --save
      
      文件：\client\src\permission.js
      查询是否有token cookie，控制路由
    
  4 前端动态加载路由
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
### 3 角色和权限
```
  1 角色定义，数据表结构:
      `name` varchar(31) NOT NULL COMMENT '名称',
      `alias` varchar(63) NOT NULL COMMENT '别名',
      `status` varchar(31) NOT NULL COMMENT '状态',
      `description` varchar(127) DEFAULT NULL COMMENT '说明',
  
  2 角色设置页面
  
  3 角色的权限，包含：页面路由的可访问性，API的可访问性（数据的访问）。
    数据表结构：
    `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
    `menu_id` int(11) UNSIGNED NOT NULL COMMENT '菜单ID',
  
  # 以下需用户功能配合
  4 前端页面访问权限控制：
    前端用户登录，后端验证后，根据用户的角色，查找对应的分配的menu，后端生成前端路由表，反馈给前端。
    
  5 API的访问权限控制
    后端收到API request，查找用户session保存的acl，控制访问。
    后端使用CI 控制器的Event，对API 鉴权。
  
    
```


---
### 4 用户
```
  1 用户信息定义，数据表结构:
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `workID` int(11) UNSIGNED NOT NULL COMMENT '工号',
    `username` varchar(10) NOT NULL COMMENT '中文名',
    `sex` varchar(3) NOT NULL COMMENT '女，男',
    `IdCard` varchar(31) DEFAULT NULL COMMENT '证件',
    `phone` varchar(15) NOT NULL,
    `email` varchar(63) NOT NULL,
    `status` varchar(3) NOT NULL COMMENT '启用或禁用',
    `password` varchar(255) NOT NULL,
    `forceChgPwd` varchar(3) NOT NULL DEFAULT '0' COMMENT '要求修改密码',
    `avatar` int(11) UNSIGNED DEFAULT NULL COMMENT '头像ID',
    
    `department` varchar(255) NOT NULL COMMENT '部门',
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

    `forgotten_password_selector` varchar(255) DEFAULT NULL,
    `forgotten_password_code` varchar(255) DEFAULT NULL,
    `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `uc_phone` (`phone`) USING BTREE,
    UNIQUE KEY `uc_email` (`email`) USING BTREE,
    UNIQUE KEY `uc_forgotten_password_selector` (`forgotten_password_selector`) USING BTREE,
    KEY `key_username` (`username`) USING BTREE,
    KEY `key_user_ref_job` (`job`) USING BTREE,
    KEY `key_user_ref_titile` (`titile`) USING BTREE,
    KEY `key_user_ref_politic` (`politic`) USING BTREE,
    
    KEY `key_user_ref_id01` (`id01`) USING BTREE,
    KEY `key_user_ref_id02` (`id02`) USING BTREE,
    KEY `key_user_ref_id03` (`id03`) USING BTREE,
    KEY `key_user_ref_id04` (`id04`) USING BTREE,
    KEY `key_user_ref_id05` (`id05`) USING BTREE,
    KEY `key_user_ref_id06` (`id06`) USING BTREE,
    KEY `key_user_ref_id07` (`id07`) USING BTREE,
    KEY `key_user_ref_id08` (`id08`) USING BTREE,
    KEY `key_user_ref_id09` (`id09`) USING BTREE,
    KEY `key_user_ref_id10` (`id10`) USING BTREE,
    KEY `key_user_ref_id11` (`id11`) USING BTREE,
    KEY `key_user_ref_id12` (`id12`) USING BTREE,
    KEY `key_user_ref_id13` (`id13`) USING BTREE,
    KEY `key_user_ref_id14` (`id14`) USING BTREE,
    KEY `key_user_ref_id15` (`id15`) USING BTREE,
    KEY `key_user_ref_id16` (`id16`) USING BTREE
    

  2 用户的公共属性：定义部门，岗位，政治面貌，职称。
    1 部门定义页面
      层级：1 - 2 - 3
      权重：10000 - 100 - 1
      例如：
      1, 1, 1 - 10101
      1, 1, 2 - 10102
      1，2，1 - 10201
      
      查找:
      1，0，0 - 10000， x > 10000
      1, 1, 0 - 10100,  x < 10200 && x > 10100
      1, 1, 2 - 10102, 
      
    
    2 岗位定义页面
    
    3 职称定义页面
    
    4 政治面貌定义页面
    
  3 用户管理页面
    
    
    

```







---
### 5 编写用户管理页面
```
  1 编写user页面
  
  # 约定
  1 dict.name 字段和dict_data.name，含user_attr_前缀 表示 user表 附加属性项
  2 user_attribute表
    `user_id` 
    `dict_data_id`

  3 相关table:
      user，user_attribute，users_roles
        dict_data，dict
        role
  
  4 页面用户信息包含多条，单个表单显示，画面较长，划分两个表单，使用el-tabs 切换表单。
    # 注意，单个表单的validate和tab的跳转。
  
  5 db操作，比如 批量insert多条，其中一条失败，事务处理测试
  
  6 确保额外属性项在 dict 和 user_attribute 的读，写 顺序一致，与页面显示一致，特别核对“编辑”
    # 新增时，id递增；查询时，order by id保证顺序；外键引用id，约束保证删除一致。更新时，id不变，仅label变化，对应顺序一致。
      分析如下：
      # 读 dict 按ida升序
      # 读 dict_data 按idb升序
      # 页面
      ida-1
        idb-1
        idb-2
      ida-2
        idb-3
        idb-4
        
      # 提交user_attribute table
      uid  (ida-1) idb-2
      uid  (ida-2) idb-3

      # preUpdate读 user_attribute table 按idb升序
      (ida-1) idb-2
      (ida-2) idb-3

      删除ida或idb
      user_attribute table 引用 dict_data table 引用 dict table  外键约束
  
  7 流程：
      dict table:
      attribute 类
      
      dict data table       fk dict
      attribute 可选值
      
      user_extra table:     fk dict data
      uid, dict data id
      
      示例：
      A，B两类
      A类，可选值 有A1 A2
      B类，可选值 有B1 B2
      
      dict table：
      1 A
      2 B
      
      dict data table：
      1 A1 (fk 1)
      2 A2 (fk 1)
      3 B1 (fk 2)
      4 B2 (fk 2)
      
      新加用户form显示正确。
      当A域不选，B域选B1，前台提交 extra_attributes: [null, 4]
      # 影响查询，新建，编辑方法
      
      
      约定：
      dict 表name字段 -- user_attr_AA
      dict_data 表name字段 -- user_attr_AA_BBBB
      
      方案：
      1 pre create: form
        后端
        user_attribute_category = select id, label from dict where name like user_attr_% order by id
        foreach user_attribute_category
          user_attribute_data = select id, label from dict data where fk = user_attribute_category.id
          
        【response】user_attribute_dynamic_list = [
                                [label: user_attribute_category.label,
                                 sub_list: user_attribute_data
                                ],
                              ]
        示例：
        [
          [A,
           [[1, A1], [2, A2]]
          ],
          [B,
           [[3, B1], [4, B2]]
          ]
        ]
        
        【待测试】有A，但没有A1，A2，user_attribute_dynamic_list去除A的部分
        【待测试】场景：A，B属性，已添加user。新增C属性，查询，新建，编辑user功能？？？？
      
        前端：
        length = user_attribute_dynamic_list
        【表单v-model】user_attribute = array(length).fill('')
        
      2 do create: 
        前端:
        提交 user_attribute
        
        示例：
        ['', ''] 或 ['', B2] 或 [A1, ''] 或 [A2, B1]
        
        后端：
        '' 不写入table
        
      3 查询：
        后端：
        【response】dynamic_columns = select label, name from dict where name like user_attr_%

        uid = select id from user where ... order by sort
        
        uid_to_attribute = select dict data id from user attribute where user id = uid
        
        user_attribute_data = slect label, name from dict data where id in uid_to_attribute.id
        
        foreach ($dynamic_columns as $category) {
            $user[$category['name']] = '';
        }
        foreach ($dynamic_columns as $category) {
            foreach ($user_attribute_data as $item) {
                // 转小写字母
                if (strpos(strtolower($item['name']), strtolower($category['name'])) !== false) {
                    $user[$category['name']] = $item['label'];
                }
            }
        }
        
      4 pre update:
        前端：
        提交uid
        
        后端：
        pre create: form
        
        //
        dynamic_columns = select label, name from dict where name like user_attr_%
        
        uid_to_attribute = select dict data id from user attribute where user id = uid
        
        user_attribute_data = slect id, name from dict data where id in uid_to_attribute.id
        
        $i = 0;
        foreach ($dynamic_columns as $category) {
            foreach ($user_attribute_data as $item) {
                // 转小写字母
                if (strpos(strtolower($item['name']), strtolower($category['name'])) !== false) {
                    $user_attribute[$i] = $item['id'];
                }
            }
            $i++;
        }
        
        【response】$user_attribute
  
  8 password
      不是必改项，后端识别password = ‘’，跳过hash_password
  
  10. 删除: 
      开启事务
      user_attribute，users_roles, user
  
  11. 前端post, put, delete请求 content-type ；application/json，后端需支持json输入
      1 背景：当params含空数组时，qs序列化，自动去除空数组，扰乱了后端基于前后端 数据定义的输入验证。调整前端post, put, delete请求body使用json格式，get请求仍使用qs序列化参数。
      2 调整点：
        1 前端，@/src/utils/request.js文件，headers: { 'Content-Type': 'application/json' },
          const service = axios.create({
            baseURL: process.env.VUE_APP_BASE_API, // url = base url + request url
            withCredentials: true, // send cookies when cross-domain requests
            headers: { 'Content-Type': 'application/json' },
            timeout: 5000 // request timeout
          })
          
          JSON.stringify(data)
          service.defaults.transformRequest = [function(data) {
            return JSON.stringify(data)
          }]
          
        2 后端CI api，采用chriskacerguis restful组件(不支持解析json输入)，在控制器post,put,delete方法入口
          $stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
          $client       = json_decode($stream_clean, true);
          
  12. 后端验证
      1 CI库提供了form validation library，出于以下需求，手动修改了CI原生form validation适应GET,POST,PUT,DELETE请求数据，且检查数据为空数组。
        1 在前后端分离模式下，原生实现中判断了HTTP method post，导致相同输入数据，在post api和put api的验证输出不一致。
        2 当PUT数据为空数组时，比如PUT []，原生跳过验证。前后端分离和restful api场景，前端更新表单，PUT方法提交数据，需校验字段是否存在。
      2 修改form validation library
        1 复制form validation.php 文件至@/application/app/library，命名为App_form validation.php，同步修改class名App_form_validation
        2 校验规则集文件，@/application/app/config/app_form_validation.php，CI机制，load library时，类的construct函数会自己动加载config路径下同名配置文件。
        3 修改App_form_validation类
          1 去掉入口的if
            public function set_rules($field, $label = '', $rules = array(), $errors = array())
            {
              // No reason to set rules if we have no POST data
              // or a validation array has not been specified
              if ($this->CI->input->method() !== 'post' && empty($this->validation_data))
              {
                return $this;
              }
          2 直接赋值$this->validation_data，不用POST数组。
            public function run($group = '')
            {
              $validation_array = empty($this->validation_data)
                ? $_POST
                : $this->validation_data;
            .
            .
            .
            屏蔽 $this->_reset_post_array()
            // Now we need to re-set the POST data with the new, processed data
            empty($this->validation_data) && $this->_reset_post_array();
            
          
          【注意】修改后，当输入某个域的值为[]时，比如PUT ['idx': []]，规则检查idx域返回true。
          
      3 后端验证约定：
        1 前后端协商定义每个api 请求的数据结构
        2 根据请求数据结构，定义校验规则集
        3 api入口，获取提交的数据
        4 进行校验，分场景：
          1 client数据中，包含规定的字段，valide 字段合法。
          2 client数据中，不包含规定的字段，valide 字段存在。
          3 client数据中，包含不规定的字段（不在验证规则集中，不会检查），只取valide 通过的规定字段，忽略超出规定的字段。
        5 流程：
          获取输入 -- 执行数据验证 -- 验证失败，响应client -- 验证通过，业务处理，失败，响应client。
          当验证通过，业务处理结束，api结束时，response合理的信息。
        
      4 示例：
        1 api方法入口：
            public function index_get()
            {
                $client = $this->get();
                $valid = $this->common_tools->valid_client_data($client, 'user_index_get');
            
            public function index_post()
            {
                $stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
                $client       = json_decode($stream_clean, true);
                $valid = $this->common_tools->valid_client_data($client, 'user_index_post');
        
        2 @/application/app/libraries/Common_tools.php 
            public function valid_client_data($array = [], $rules = '')
            {
                if (empty($rules)) {
                    return false;
                }
                $this->form_validation->reset_validation();
                $this->form_validation->set_data($array);
                if ($this->form_validation->run($rules) == false) {
                    return $this->form_validation->error_string();
                } else {
                    return true;
                }
            }
        
        3 @/application/app/config/app_form_validation.php  
          支持域值为 数组类型，当某个域的值为[]时，比如PUT ['idx': []]，规则检查idx域返回true。
            $config = [
                'user_index_post' => [
                    [
                        'field'  => 'role_ids[]',
                        'label'  => 'role_ids',
                        'rules'  => [
                            ['valid_role_ids',
                                function ($str = null) {
                                    // field is required
                                    if (!isset($str)) {
                                        return false;
                                    }
                                    // e.g. number no zero
                                    return ($str != 0 && ctype_digit((string) $str));
                                },
                            ],
                        ],
                        'errors' => ['valid_role_ids' => '请求参数非法！role_ids'],
                    ],


            $config['error_prefix'] = '';
            $config['error_suffix'] = '';
            
      5 新建时，job域不填，提交域值''，后端写DB时判断===''，跳过该字段，而table该字段default null.
        编辑时，读取到job域值null，不影响页面显示含义。保持job域不变，提交编辑，后端验证不通过。
        原因：查询时后端返回null，不影响页面显示，但提交null，验证不通过。
        方案：前端接收''，不影响页面显示的含义，后端查询返回前，遍历结果，将null 替换为''。
          @/application/app/model/user_model.php  
            public function get_form_by_user_edit
              $user = $this->_replace_null_field_in_user_array($query->result_array()[0]);
            
            protected function _replace_null_field_in_user_array($array = [])
            {
                if (empty($array)) {
                    return true;
                }

                foreach ($array as &$v) {
                    if ($v === null) {
                        $v = '';
                    }
            }
            return $array;
            }
            
        对于dept_id域，不能按此方案处理。
        原因：dept_id域绑定 treeselect组件（不选时，组件返回undefined），而undefined域值，在json序列化时，去除了此域，则后端接收不到此域。
        方案：前端dept域必填，初始默认值。
        
  
  12. table 增加显示attribute列
      # 流程
        查询user table
        !empty(user)
          获取列表头prop(extra_columns)：查询dict表 select label, name like(name, user_attr_)，order by(id)
          获取user extra attr：
            查询user_attribute表 select dict_data_id where user_id, order by dict_data_id;
            查询dict_data表 select label where_in dict_data_id
      
      # 适配动态隐藏列
        因为 用户extra attribute 从后端读取，有延时，添加标志initTableDone，收到extra attribute后，只更新一次[mixin]updateColumns(),支持pre-hide。
        使用Vue.set( 用于组件中data 对象，追加新属性。
        使用this.$nextTick(() => {， 用于等待view更新完毕，再读取view中元素。
        使用v-for  v-if，用于隐藏控制。
  
  13. 数据显示区分页，新增，编辑，删除操作，显示变化如何呈现用户（分页，limit）
      # 前端 
        api 入参：limit: string e.g. num_offset
        
        pagechange事件，调用refreshTblDisplay()
        
      # 后端：
        if (!empty($limit)) {
            $limit_temp = explode('_', $limit);
            $num        = (int) $limit_temp[0];
            $offset     = (int) $limit_temp[1];
            $this->db->limit($num, $offset);
        }
        $total_rows = $this->db->count_all($this->tables['user']);
        返回 table总行数。
        
      # 新增一行
        对于DB table，新行肯定在表的末尾。查询时order by(sort)，结果集新行不一定在最后一行。
        对于user 表，有“工号”列，insert时，工号既不是递增，也不是递减，则工号列 乱序。比如：insert了小强-1，小猪-5，小芳-3
        
        【不处理】insert 小明-4，怎么让页面显示 定位在 用户刚新增的那一行？？
        # insert增加分页数
        # 有3个分页，在任意分页，点击新增按钮
        
        操作成功response
          .then(function() {
              this.tableTotalRows = this.tableTotalRows + 1
              this.$nextTick(() => {
                this.refreshTblDisplay()
              })
      
      # 最后一分页，最后一行，删除处理
        1 current-page添加.sync修饰符
          <el-pagination
            :page-sizes="[5, 10, 30]"
            :page-size="pageSize"
            :current-page.sync="pageIdx"
            
        2 删除操作成功response
          .then(function() {
              if (this.tableTotalRows > 0) {
                this.tableTotalRows = this.tableTotalRows - 1
              }
              this.$nextTick(() => {
                this.refreshTblDisplay()
              })
          this.tableTotalRows - 1 改变总行数，触发el-pagination组件内更新current-page，借助current-page.sync，更新反馈给父组件。
          this.$nextTick 等待current-page更新，再调用api刷新table显示区        
  
  14. 页面 检索功能，适应多条件组合
      # 约定
        1 前端提交查询条件数据，后端验证
        2 前端使用GET方法场景：
          1 新加用户时，请求后端填表信息。
          2 编辑用户时，请求后端用户信息。
          3 查询时，将查询条件提交后端。
        3 基于GET方法场景，约定：
          1 不同场景下GET方法，params不一样，含不同字段。
          2 每个场景，每个字段需包含在params内。若页面输入框等，用户不填写，前端处理对应字段为''。
          3 后端验证：
            1 规则定义：每个字段isset() == false or == ''，return true
            2 业务func：前端提交的数据通过valid后，业务func对接收到的前端数据再判断，isset() == true，执行业务处理。
          
          
      1 查询语句
        SELECT 
        <select_list>  -- 需适配 select: string
        FROM <left_table>  -- api指定访问的table
        <join_type> JOIN <right_table>
        ON <join_condition>
        WHERE <where_condition> -- 需适配 where, where_in, like, ...
        GROUP BY <group_by_list>
        HAVING <having_condition>
        ORDER BY <order_by_condition>
        LIMIT <limit_number>  -- 需适配 分页读取 limit: string e.g. num_offset
      
      2 查询条件分类；
        1 A 个性，比如：工号，中文名，手机号，身份证号，邮箱
        2 B1，B2，B3 共性，多条件组合“且”关系，比如：性别，部门，岗位，党派，职称
        
        # 示例：
          select * from user where 工号 like A or where 中文名 like A
          select * from user where 性别 like B1 and where 部门 like B2 and where 岗位 like B3
          
          分页，页面跳转如何携带 查询条件？？
      
      3 场景：
        1 A ！= ''，（ where 工号 like A or 中文名 like A ）
        2 B1 ！= ''， （ where 工号 like A or 中文名 like A ） and  性别 = B1
        3 B2 ！= ''， （ where 工号 like A or 中文名 like A ） and  性别 = B1 and 部门 like B2
        4 ...
        
      4 实现：
        1 A ！= ''，（ where 工号 like A or 中文名 like A ）
          【去重】？？？
        
        2 字段-部门，树形结构，比如，工作室以下有小组1，小组2。当查询 工作室时，需要其下所有子节点的user。
          # 查询select id from dept like label %str% group by id  【去重】
          # 查询子节点，合并id，并去重
            如果没有匹配的部门，说明 最终查询结果是空，不用往后执行查询语句，提前返回前台。
          # 添加( dept_id in id集合)
          
        3 字段-岗位，比如，不同部门下有相同岗位，开发组1，开发组2下都有开发员。当查询 开发员时，列出各个部门user。
          # 查询select id from job like label %str% group by id  【去重】
            如果没有匹配的岗位，说明 最终查询结果是空，不用往后执行查询语句，提前返回前台。
          # 添加( job_id in id集合)
          
        4 字段-党派。
          # 查询select id from dict_data where name like 'user_attr_politic%' and label like %str% group by id  【去重】
          # 查询select user_id from user_attribute where dict_data_id in () group by user_id  【去重】
            如果没有匹配的，说明 最终查询结果是空，不用往后执行查询语句，提前返回前台。
          # 添加( id in id集合)
```

---
### 8. 编写用户头像功能
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
### 9. table列动态显示/隐藏功能
```
  1 组件文件：src\components\app\TableOptions
    view: index.vue
      # 子组件属性，类型-Object，其实是父组件传入对象的引用。对应mixin的columns。
      props: { tableColumns: {
        type: Object,
        default: function() {
          return {}
        }
      }},
    mixin: hide-columns.js
      data() {
        return {
          columnOpt: obColumns(), // 混入显示判断函数
          columns: {} // 混入 table列属性 {label, visble}
        }
      },
    
  
  2 父组件引入：
    import TableOptions from '@/components/app/TableOptions/index'
    import hideColumns from '@/components/app/TableOptions/hide-columns'

    components: { TableOptions },
    mixins: [hideColumns()],
    
    # 使用如下，借用column-key="pre-hide"，初始化时，默认隐藏。
      <el-table-column v-if="columnOpt.visible('last_login')" column-key="pre-hide" :show-overflow-tooltip="true" prop="last_login" label="登录日期" />
      
    # 修改table表，只需要<el-table-column属性的修改。
```

---
### 10. 参照用户管理页面，更新 app其他页面文件
```
  1 前端：
    1 模板
      1 添加表头search区，定义search字段
      2 添加分页
      3 调整table和dialog-form
    2 JS
      1 import 组件，修改api方法名
      2 调整查询区域
      3 调整CRUD方法
      4 form输入框验证方法
      5 search区 验证规则
  
  2 后端：
    1 调整api方法
    2 调整model
    3 定义规则，验证前端数据
    4 定义响应code和msg定义
      
  3 search 组件，watch输入框，输入字母时，触发多次change事件，导致发往后端多条无效请求。 
    # 方案：
      去掉watch，使用el-input change，clear事件。组件mounted 发一次change事件，页面刷新自动向后端请求数据。
  
  4 search组件传递表单验证rule
    blur 触发验证
  
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