# Owncloud

---

### owncloud Ô´
```
  # https://download.owncloud.org/download/repositories/stable/owncloud/index.html
  # Run the following shell commands as root to trust the repository.
  rpm --import https://download.owncloud.org/download/repositories/production/CentOS_7/repodata/repomd.xml.key
  wget http://download.owncloud.org/download/repositories/production/CentOS_7/ce:stable.repo -O /etc/yum.repos.d/ce:stable.repo
 
 
  yum install owncloud-files

  # Appache owncloud config
  Alias /owncloud "/var/www/html/owncloud/"
  
  <Directory /var/www/html/owncloud/>
    Options +FollowSymlinks
    AllowOverride All
  <IfModule mod_dav.c>
    Dav off
  </IfModule>
  </Directory>

  # db and account
  # account - freeair/Free@321
  
  CREATE USER 'oc_admin'@'localhost' IDENTIFIED BY 'Bing@753';
  CREATE DATABASE IF NOT EXISTS owncloud;
  GRANT ALL PRIVILEGES ON owncloud.* TO oc_admin@localhost;
  # ³·ÏúÊÚÈ¨
  revoke all on owncloud.* from oc_admin@localhost;
  Delete FROM user Where User='oc_admin' and Host='localhost';
  

  # add selinux config
  semanage fcontext -a -t httpd_sys_rw_content_t '/stations/oc_data(/.*)?'
  semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/owncloud/data(/.*)?'
  semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/owncloud/config(/.*)?'
  semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/owncloud/apps(/.*)?'
  semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/owncloud/apps-external(/.*)?'
  semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/owncloud/.htaccess'
  semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/owncloud/.user.ini'

  restorecon -Rv '/var/www/html/owncloud/'
  restorecon -Rv '/stations/'

  # remove selinux config
  semanage fcontext -d '/stations/oc_data(/.*)?'
  semanage fcontext -d '/var/www/html/owncloud/data(/.*)?'
  semanage fcontext -d '/var/www/html/owncloud/config(/.*)?'
  semanage fcontext -d '/var/www/html/owncloud/apps(/.*)?'
  semanage fcontext -d '/var/www/html/owncloud/apps-external(/.*)?'
  semanage fcontext -d '/var/www/html/owncloud/.htaccess'
  semanage fcontext -d '/var/www/html/owncloud/.user.ini'
  
  restorecon -Rv '/var/www/html/owncloud/'
  restorecon -Rv '/stations/'
```
