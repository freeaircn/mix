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
  1 DB：menu，role_menu，dts_attachment
  
2 记录
  1 增加DTS附件管理页
    
  2 配置主cache为redis，备cache为file；session使用redis 缓存；增加发送验证码API 请求限流；
  
  3 2022-6-20 使用CI cache redis处理器，缓存DTS统计图数据。

  4 验证码使用redis cache存储和验证，验证码方式登录
```

