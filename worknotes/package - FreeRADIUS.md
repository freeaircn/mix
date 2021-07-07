# FreeRADIUS
---
Centos

---
### 1 安装
```
  1 yum install freeradius
  
  2 yum install freeradius-utils
  
  3 配置文件
    /etc/raddb/
  
  4 测试
    1 添加测试用户
      /etc/raddb/users
      文件开头，写入 testing Cleartext-Password := "password"
      
    2 启动debug
      radiusd -X
      
      systemctl enable radiusd
      systemctl start radiusd
      
      查找进场
      netstat -lnp|grep 1812
      
      kill -9 进程ID      
      
    3 打开另一个终端，请求验证
      radtest testing password 127.0.0.1 0 testing123
      
  5 RADIUS client 配置
    RADIUS client 指 交换机、路由器等NAS设备。
    
    /etc/raddb/clients.conf
    client sch-fw {
      ipaddr		= 192.168.1.254/32
      secret		= fw@sch.321
    }

  6 防火墙开启 udp 1812
    firewall-cmd --zone=public --add-port=1812/udp --permanent
    firewall-cmd --zone=public --add-port=1813/udp --permanent
    
    firewall-cmd --reload
  
  7 数据库配置
    http://www.doc88.com/p-5317498255447.html
    http://www.beijinghuayu.com.cn/centos7-freeradius-mysql%E9%85%8D%E7%BD%AE/
    
    1 安装freeradius-mysql组件
      yum install freeradius-mysql
    
    2 创建数据库和用户
      # mysql -u root binglang < /etc/raddb/mods-config/sql/main/mysql/schema.sql
      
      [mariadb]
      CREATE DATABASE IF NOT EXISTS `radius` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
      create user ssh_radius@localhost identified by 'radius@ssh.321';
      grant all on radius.* to ssh_radius@localhost;
    
    3 导入DB表    
      mysql -u root radius < /etc/raddb/mods-config/sql/main/mysql/schema.sql
      导入了7个表：
      radcheck 用户检查信息表
      radreply 用户回复信息表
      radgroupcheck 用户组检查信息表
      radgroupreply 用户组检查信息表
      radusergroup 用户和组关系表
      radacct 计费情况表
      radpostauth 认证后处理信息，可以包括认证请求成功和拒绝的记录。
    
    3 数据表添加测试数据
      https://freeradius.org/radiusd/man/rlm_pap.html
      https://wiki.freeradius.org/config/Users
      https://wiki.freeradius.org/config/Operators
    
      [mariadb]
      USE `radius`;
      
      测试NAS设备
      insert into nas (nasname,shortname,type,ports,secret,description) values ('192.168.1.254','sch-fw','other','1812','fw@sch.321','ssh radius server');
      
      测试用户
      insert into radcheck (username,attribute,op,value) values ('ss','Cleartext-Password',':=','123');
      
      用户组
      INSERT INTO radusergroup (username,groupname) VALUES ('ss','admin');
      
      限制同时登陆人数，radgroupcheck表
      INSERT INTO radgroupcheck (groupname,attribute,op,VALUE) VALUES ('admin','Simultaneous-Use',':=','1');
      
    4 加入mysql 扩展
      cd /etc/raddb/mods-enabled
      ln -s ../mods-available/sql
    
      修改配置sql
      http://blog.itblood.com/1794.html
      看到篇文章说 localhost 会走域套接字 使用IP地址却不会 于是改下配置文件
      1)将 driver = "rlm_sql_null" 修改为 driver = "rlm_sql_mysql"
      2)将
              dialect = "sqlite"
      #        server = "localhost"
      #        port = 3306
      #        login = "radius"
      #        password = "radpass"
      修改为
              dialect = "mysql"
              server = "127.0.0.1"
              port = 3306
              login = "radius"
              password = "passwd-radius"
      3)从数据库读取clients NAS 
        将
        #       read_clients = yes
        修改为
               read_clients = yes          
    
    5 修改配置
      1 post_auth默认验证成功和失败，都将请求用户/密码记录到sql中。
        /etc/raddb/sites-enabled/default
        post-auth {
        	#
          #  After authenticating the user, do another SQL query.
          #
          #  See "Authentication Logging Queries" in mods-available/sql
        	-sql
        
        用户请求验证，取消记录密码：
        /etc/raddb/mods-config/sql/main/mysql/queries.conf
        
        post-auth {
          # Write SQL queries to a logfile. This is potentially useful for bulk inserts
          # when used with the rlm_sql_null driver.
        #	logfile = ${logdir}/post-auth.sql

          query =	"\
            INSERT INTO ${..postauth_table} \
              (username, pass, reply, authdate) \
            VALUES ( \
              '%{SQL-User-Name}', \
              '%{%{User-Password}:-%{Chap-Password}}', \
              '%{reply:Packet-Type}', \
              '%S')"
        }
        修改为：
        	query =	"\
            INSERT INTO ${..postauth_table} \
              (username, reply, authdate) \
            VALUES ( \
              '%{SQL-User-Name}', \
              '%{reply:Packet-Type}', \
              '%S')"   
        
      2 调试时开启，记录sql queries 到logfile。生产环境，关闭该功能。
        /etc/raddb/mods-available/sql
          # Write SQL queries to a logfile. This is potentially useful for tracing
          # issues with authorization queries.  See also "logfile" directives in
          # mods-config/sql/main/*/queries.conf.  You can enable per-section logging
          # by enabling "logfile" there, or global logging by enabling "logfile" here.
          #
          # Per-section logging can be disabled by setting "logfile = ''"
        #	logfile = ${logdir}/sqllog.sql

        

        
        
    
    
```