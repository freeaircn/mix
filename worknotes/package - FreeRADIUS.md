# FreeRADIUS
---
Centos

---
### 1 ��װ
```
  1 yum install freeradius
  
  2 yum install freeradius-utils
  
  3 �����ļ�
    /etc/raddb/
  
  4 ����
    1 ��Ӳ����û�
      /etc/raddb/users
      �ļ���ͷ��д�� testing Cleartext-Password := "password"
      
    2 ����debug
      radiusd -X
      
      systemctl enable radiusd
      systemctl start radiusd
      
      ���ҽ���
      netstat -lnp|grep 1812
      
      kill -9 ����ID      
      
    3 ����һ���նˣ�������֤
      radtest testing password 127.0.0.1 0 testing123
      
  5 RADIUS client ����
    RADIUS client ָ ��������·������NAS�豸��
    
    /etc/raddb/clients.conf
    client sch-fw {
      ipaddr		= 192.168.1.254/32
      secret		= fw@sch.321
    }

  6 ����ǽ���� udp 1812
    firewall-cmd --zone=public --add-port=1812/udp --permanent
    firewall-cmd --zone=public --add-port=1813/udp --permanent
    
    firewall-cmd --reload
  
  7 ���ݿ�����
    http://www.doc88.com/p-5317498255447.html
    http://www.beijinghuayu.com.cn/centos7-freeradius-mysql%E9%85%8D%E7%BD%AE/
    
    1 ��װfreeradius-mysql���
      yum install freeradius-mysql
    
    2 �������ݿ���û�
      # mysql -u root binglang < /etc/raddb/mods-config/sql/main/mysql/schema.sql
      
      [mariadb]
      CREATE DATABASE IF NOT EXISTS `radius` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
      create user ssh_radius@localhost identified by 'radius@ssh.321';
      grant all on radius.* to ssh_radius@localhost;
    
    3 ����DB��    
      mysql -u root radius < /etc/raddb/mods-config/sql/main/mysql/schema.sql
      ������7����
      radcheck �û������Ϣ��
      radreply �û��ظ���Ϣ��
      radgroupcheck �û�������Ϣ��
      radgroupreply �û�������Ϣ��
      radusergroup �û������ϵ��
      radacct �Ʒ������
      radpostauth ��֤������Ϣ�����԰�����֤����ɹ��;ܾ��ļ�¼��
    
    3 ���ݱ���Ӳ�������
      https://freeradius.org/radiusd/man/rlm_pap.html
      https://wiki.freeradius.org/config/Users
      https://wiki.freeradius.org/config/Operators
    
      [mariadb]
      USE `radius`;
      
      ����NAS�豸
      insert into nas (nasname,shortname,type,ports,secret,description) values ('192.168.1.254','sch-fw','other','1812','fw@sch.321','ssh radius server');
      
      �����û�
      insert into radcheck (username,attribute,op,value) values ('ss','Cleartext-Password',':=','123');
      
      �û���
      INSERT INTO radusergroup (username,groupname) VALUES ('ss','admin');
      
      ����ͬʱ��½������radgroupcheck��
      INSERT INTO radgroupcheck (groupname,attribute,op,VALUE) VALUES ('admin','Simultaneous-Use',':=','1');
      
    4 ����mysql ��չ
      cd /etc/raddb/mods-enabled
      ln -s ../mods-available/sql
    
      �޸�����sql
      http://blog.itblood.com/1794.html
      ����ƪ����˵ localhost �������׽��� ʹ��IP��ַȴ���� ���Ǹ��������ļ�
      1)�� driver = "rlm_sql_null" �޸�Ϊ driver = "rlm_sql_mysql"
      2)��
              dialect = "sqlite"
      #        server = "localhost"
      #        port = 3306
      #        login = "radius"
      #        password = "radpass"
      �޸�Ϊ
              dialect = "mysql"
              server = "127.0.0.1"
              port = 3306
              login = "radius"
              password = "passwd-radius"
      3)�����ݿ��ȡclients NAS 
        ��
        #       read_clients = yes
        �޸�Ϊ
               read_clients = yes          
    
    5 �޸�����
      1 post_authĬ����֤�ɹ���ʧ�ܣ����������û�/�����¼��sql�С�
        /etc/raddb/sites-enabled/default
        post-auth {
        	#
          #  After authenticating the user, do another SQL query.
          #
          #  See "Authentication Logging Queries" in mods-available/sql
        	-sql
        
        �û�������֤��ȡ����¼���룺
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
        �޸�Ϊ��
        	query =	"\
            INSERT INTO ${..postauth_table} \
              (username, reply, authdate) \
            VALUES ( \
              '%{SQL-User-Name}', \
              '%{reply:Packet-Type}', \
              '%S')"   
        
      2 ����ʱ��������¼sql queries ��logfile�������������رոù��ܡ�
        /etc/raddb/mods-available/sql
          # Write SQL queries to a logfile. This is potentially useful for tracing
          # issues with authorization queries.  See also "logfile" directives in
          # mods-config/sql/main/*/queries.conf.  You can enable per-section logging
          # by enabling "logfile" there, or global logging by enabling "logfile" here.
          #
          # Per-section logging can be disabled by setting "logfile = ''"
        #	logfile = ${logdir}/sqllog.sql

        

        
        
    
    
```