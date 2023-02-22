# 合入

---
###1. v1.0.3
```
dts详情：附件[上传，删除，下载]，更新进展，修改评分。@dts详情：工作流状态流转；关联：RM，RFC，Task

dts详情 - 附件[上传，删除，下载]，修改评分，工作流[新建，处理，解决]。Next：工作流[关闭，重新处理]；关联[RM，RFC，Task]

dts详情 - 附件[上传，删除，下载]，修改评分，工作流[新建，处理，解决，关闭，重新处理]。Next：关联[RM，RFC，Task]

Next：修改数据表[API，Dept，Equipment]合入生产环境[User，GE，Meter，kwh，DTS]，DTS关联[RM，RFC，Task]


增加 DTS新建问题邮件通知，Next：统计结果redis cache，修改检索页面，点击图表跳转检索页面

修改 DTS检索页面，Next：统计结果redis cache，点击图表跳转检索页面

添加场景：搜索结果页面跳转至详细信息页面，返回搜索页面，store缓存搜索页面数据。 Next：统计结果redis cache

增加 点击DTS统计图表打开对应过滤条件的检索页面据。 Next：统计结果redis cache
  
```

### 2. v1.0.4
```
1 变更：
  1 修改DB：menu，role_menu，dts_attachment
  2 新增DB：attachment替换dts_attachment
  
2 记录
  增加DTS附件管理页
    
  配置主cache为redis，备cache为file；session使用redis 缓存；增加发送验证码API 请求限流；
  
  2022-6-20 使用CI cache redis处理器，缓存DTS统计图数据。

  验证码使用redis cache存储和验证，验证码方式登录。
```

### 3. v1.0.5  2023-2-20 开始
```
1 变更：
  1 取消 v1.0.4 增加的DB：attachment数据表，DTS模块的附件仍由dts_attachment数据表保存
  
2 记录
  增加图纸资料页面.
  
  操作：查询，上传文件，下载，删除。
  
  文件格式：pdf，zip
  
  新增DB：drawing
  
  图纸编号：
  站点-设备分类-序号
  XXX-X(X..X)-XXX
  例如：
  SSH-GIS-001
  SSH-ZF-001
  SSH-TSQ-001
  
  1 创建前端页面
  
  2 为用户添加新页面访问权限
  
  3 创建API接口：前端url，方法和传递参数。后端：url映射，控制器方法，参数检查，数据方法。
  
  4 为用户添加API请求权限
  
  5 创建附件保存文件夹，设置读写权限
  
  6 新增 检索页
  
  7 新增 新建页
  
  app_drawing
  app_drawing_category
  app_dept
  
  8 新增上传文件存放的临时文件夹uploads/temp，当表单确认提交成功后，将文件从临时文件夹移动至对应模块的文件夹。
   
  9 新增 详情页

  
```