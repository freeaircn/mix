# 权限

---
### 1. 页面，路由，API，权限
```
1 前端单页面应用。

2 前端的页面url，后端CI的route配置，都指向home页面。

3 页面显示的数据信息，CRUD按钮将调用API请求后端数据。

4 后端对API请求识别其用户权限。

5 页面url，API url设定：
  页面url：
  1 例如：http://site/login, http://site/admin/user, http://site/account/settings
  2 页面url路径在vue route表定义
  3 后端CI route配置，将页面url的请求都指向home页面。
  
  API url：
  1 例如：http://site/api/auth/login, http://site/api/menu?req=build_menu
  2 url的字段1 是关键字“api”，这样与页面或其他请求的url区分开来。    
  3 api url在axios的接口中定义。
  4 后端CI route表识别该关键字“api”，指向不同controller。

6 restful API
  将Http请求method，对应数据资源的CRUD。
  Get     --  Read
  Post    --  Create
  Put     --  Update
  Delete  --  Delete
  
  结合后端CI控制器与route的层级关系，可设定api url访问的资源有：
    1 /api/CI控制器名:method，例如：/api/dept:get -- CI控制器Dept，控制器方法index_get，查询dept资源。
    2 /api/CI控制器名/func_x:method，例如：/api/auth/login:post -- CI控制器Auth，控制器方法login_post。
  
7 权限
  自定义类App_Rest_API，继承restserver类，在构造函数中，验证api请求的权限。调试时设置超级用户，超级用户拥有所有api请求权限。
    1 查询session data，检查用户登录状态。
    2 从http请求提取url字段和method，比对用户登录后session data保存的acl列表。例如：
      http://site/api/a:get，或http://site/api/a/c:get，识别url中含“api”，取字段“a”，将字段与method组合成"a:method".
      用户的acl数组存储内容为["a:get", "b:get", "b:post", ...]
    3 不是所有API都需要鉴权，比如login，register，所有用户都可以访问。

```

---
### 2. 定义：页面url - API url - API权限字
```
页面url                 API url                                       API 权限字                                 后端检查请求数据
/login                  /api/auth/login:post                          pass                                          phone password
                        /api/auth/logout:post                         pass                                          /
                        /api/auth/check_user:get                      pass                                          /
/forget_password        /api/auth/req_verification_code:get           pass                                          phone
                        /api/auth/reset_password:post                 pass                                          phone code password
                        
/admin/dept             /api/dept                                     dept:get  dept:post dept:put dept:delete
                  
/admin/job              /api/job                                      ...
      
/admin/user             /api/user                                     ...
      
/admin/role             /api/role                                     ...
      
/admin/menu             /api/menu                                     ...
      
                        /api/role_menu                                ...
                        
/admin/dict             /api/dict                                     ...
      
/admin/dict_data        /api/dict_data                                ...
      
/account/setting        /api/account/basic_Info_form_list_content:get account:get                                   /
                        /api/account/basic_info:put                   account:put                                   user_prop_edit_mask
                        /api/account/avatar:post                      account:post                                  ？
                        /api/account/verification_code:get            account:get                                   /
                  
                        /api/account/security_setting:post            account:post                                  prop phone email code
                        /api/account/password:put                     account:put                                   password

```

---
### 多部门数据权限
```
角色：
  前端页面  - app_role_menu
  API       - app_role_api
  数据数据  - app_role_dept
  工作流    - app_role_workflow
  
  
  app_role_api和app_role_dept，共同决定用户可以访问的部门数据和允许的CRUD方法。例如，用户允许访问部门A数据，但没有允许的API CRUD方法，最终用户在前端不会看到任何信息。
  
  部门组织为树形结构。
  
  部门 - 数据：某一级部门有各类数据，例如 某厂站的电度表信息、设备缺陷信息等。
  
  部门C的用户，允许查询部门A和B的数据，该用户app_role_dept含部门A，B和C，app_role_api含读权限。
  
  为用户指定可读的部门，并在可读的部门中，指定写权限的部门，并指定读写的默认部门。
  
  要求：用户设置的所有角色中，只有一个角色设置了部门数据权限

```
