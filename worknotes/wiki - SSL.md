# SSL
---

---
### 1 自签名SSL
```
  1 参考
    # https://www.cnblogs.com/nidey/p/9041960.html
    # https://www.cnblogs.com/walk1314/p/9100019.html
    
    # https://www.cnblogs.com/jie-hu/p/8034226.html
    # https://www.cnblogs.com/xiaoleiel/p/11160661.html
    # https://www.cnblogs.com/idjl/p/9610561.html
  
  2 Appache安装ssl模块，安装完后在/etc/httpd/conf.d/会有一个ssl.conf的文件，打开文件以后找到SSLCertificateFile和SSLCertificateKeyFile2行，可以看到后面我们要生成的密钥的配置信息
    yum install mod_ssl
  
  3 安装openssl
    yum install openssl
  
  4 ca配置文件
    vi ca.conf
    [ req ]
    default_bits       = 4096
    distinguished_name = req_distinguished_name

    [ req_distinguished_name ]
    countryName                 = CN
    stateOrProvinceName         = YunNan
    localityName                = BaoShan
    organizationName            = BingLangJiang Co.,Ltd.
    organizationalUnitName      = Freeair Studio 
    commonName                  = Own CA
    commonName_max              = 64
  
    # 生成ca秘钥，得到ca.key
    openssl genrsa -out ca.key 4096
    
    # 生成ca证书签发请求，得到ca.csr
    openssl req -new -sha256 -out ca.csr -key ca.key -config ca.conf
    pwd/6669
    
    # 生成ca根证书，得到ca.crt
    openssl x509 -req -days 365 -in ca.csr -signkey ca.key -out ca.crt

  # appache侧
    # 配置文件
    vi server.conf
    [ req ]
    default_bits       = 2048
    distinguished_name = req_distinguished_name
    req_extensions     = req_ext

    [ req_distinguished_name ]
    countryName                 = Country Name (2 letter code)
    countryName_default         = CN
    stateOrProvinceName         = State or Province Name (full name)
    stateOrProvinceName_default = YunNan
    localityName                = Locality Name (eg, city)
    localityName_default        = BaoShan
    organizationName            = Organization Name (eg, company)
    organizationName_default    = BingLangJiang Co.,Ltd. 
    commonName                  = Common Name (e.g. server FQDN or YOUR name)
    commonName_max              = 64
    commonName_default          = www.be-green.com

    [ req_ext ]
    subjectAltName = @alt_names

    [alt_names]
    DNS.1   = www.be-green.com
    IP.1    = 182.247.101.235
    IP.2    = 192.168.205.60

    
    # 生成秘钥，得到server.key
    openssl genrsa -out server.key 2048
    
    # 生成证书签发请求，得到server.csr
    openssl req -new -sha256 -out server.csr -key server.key -config server.conf
    
    # 用CA证书签名证书，得到server.crt
    openssl x509 -req -days 365 -CA ca.crt -CAkey ca.key -CAcreateserial -in server.csr -out server.crt -extensions req_ext -extfile server.conf
    
    # 存放文件
    /etc/pki/tls/certs/server.crt
    /etc/pki/tls/private/server.key
  
  # 配置防火墙
  firewall-cmd --permanent --add-service=https 
  firewall-cmd --permanent --add-port=443/tcp
  firewall-cmd --reload
  
  # 用户侧主机安装ca.crt
  右键ca.crt安装，安装到“受信任的根证书颁发机构”（不然server.crt还是不受信任的）
```


### 申请免费域名
```
  # chrome 安装“谷歌访问助手”插件
  # https://www.jianshu.com/p/6086ec29c173
    # 克隆或下载仓库到本地
    # 打开Chrome浏览器的扩展程序管理器，然后勾选开发者模式。
    # 在左上角点击加载已解压的扩展程序，选在在第一步下载的谷歌访问助手文件夹
    # 当然，如果想在其他非chrome浏览器安装谷歌访问助手，怎么办呢？别担心，开发者为我们考虑到这点。同样在上面的GitHub网页上，开发者提供了一个网站http://www.ggfwzs.com
    
  # 免费域名申请，需要gmail账号（freeair.sam@gmail.com）
  https://www.freenom.com/en/index.html
  www.binglang.cf  182.247.101.235
```

### 申请免费ssl证书
```
  # 为appache安装ssl模块，安装完后在/etc/httpd/conf.d/会有一个ssl.conf的文件，打开文件以后找到SSLCertificateFile和SSLCertificateKeyFile，可以看到后面我们要生成的密钥的配置信息
  yum install mod_ssl

  # https://www.cnblogs.com/esofar/p/9291685.html
  # https://www.jianshu.com/p/3aa5cb957d9f  
  
  # 用第三方客户端 acme.sh 申请
  # 把 acme.sh 安装到当前用户的主目录$HOME下的.acme.sh文件夹中，即~/.acme.sh/，之后所有生成的证书也会放在这个目录下
  # 安装 acme.sh
  cd ~/.acme.sh
  curl https://get.acme.sh | sh
  
  # 创建了一个指令别名alias acme.sh=~/.acme.sh/acme.sh，这样我们可以通过acme.sh命令方便快速地使用 acme.sh 脚本
  # acme.sh --version确认是否能正常使用acme.sh命令。
  
  # 生成证书
  # acme.sh --issue -d xxx.cn -d www.xxx.cn -w /var/www/html
  --issue是 acme.sh 脚本用来颁发证书的指令；
  -d是--domain的简称，其后面须填写已备案的域名；
  -w是--webroot的简称，其后面须填写网站的根目录。
  
  acme.sh --issue -d binglang.cf -d www.binglang.cf -w /var/www/html
  
  # 存放证书
  /etc/pki/tls/certs/fullchain.cer
  /etc/pki/tls/private/be-green.ga.key
  
  # 配置防火墙
  firewall-cmd --permanent --add-service=https 
  firewall-cmd --permanent --add-port=443/tcp
  firewall-cmd --reload
  
  # 重启httpd
  systemctl restart httpd.service
  
  # 检测网站的安全级别：
  https://myssl.com
  https://www.ssllabs.com
  
  # 更新证书，目前 Let's Encrypt 的证书有效期是90天，时间到了会自动更新，您无需任何操作
    # 强制续签证书
    acme.sh --renew -d example.com --force
 ```   
    
    

### [备选] 开启自签名SSL
```
  # https://www.cnblogs.com/nidey/p/9041960.html
  # https://www.cnblogs.com/walk1314/p/9100019.html
  
  # https://www.cnblogs.com/jie-hu/p/8034226.html
  # https://www.cnblogs.com/xiaoleiel/p/11160661.html
  # https://www.cnblogs.com/idjl/p/9610561.html
  
  # 为appache安装ssl模块，安装完后在/etc/httpd/conf.d/会有一个ssl.conf的文件，打开文件以后找到SSLCertificateFile和SSLCertificateKeyFile2行，可以看到后面我们要生成的密钥的配置信息
  yum install mod_ssl
  
  # openssl
  yum install openssl
  
  # CA侧
    # ca配置文件
    vi ca.conf
    [ req ]
    default_bits       = 4096
    distinguished_name = req_distinguished_name

    [ req_distinguished_name ]
    countryName                 = CN
    stateOrProvinceName         = YunNan
    localityName                = BaoShan
    organizationName            = BingLangJiang Co.,Ltd.
    organizationalUnitName      = Freeair Studio 
    commonName                  = Own CA
    commonName_max              = 64
  
    # 生成ca秘钥，得到ca.key
    openssl genrsa -out ca.key 4096
    
    # 生成ca证书签发请求，得到ca.csr
    openssl req -new -sha256 -out ca.csr -key ca.key -config ca.conf
    pwd/6669
    
    # 生成ca根证书，得到ca.crt
    openssl x509 -req -days 365 -in ca.csr -signkey ca.key -out ca.crt

  # appache侧
    # 配置文件
    vi server.conf
    [ req ]
    default_bits       = 2048
    distinguished_name = req_distinguished_name
    req_extensions     = req_ext

    [ req_distinguished_name ]
    countryName                 = Country Name (2 letter code)
    countryName_default         = CN
    stateOrProvinceName         = State or Province Name (full name)
    stateOrProvinceName_default = YunNan
    localityName                = Locality Name (eg, city)
    localityName_default        = BaoShan
    organizationName            = Organization Name (eg, company)
    organizationName_default    = BingLangJiang Co.,Ltd. 
    commonName                  = Common Name (e.g. server FQDN or YOUR name)
    commonName_max              = 64
    commonName_default          = www.be-green.com

    [ req_ext ]
    subjectAltName = @alt_names

    [alt_names]
    DNS.1   = www.be-green.com
    IP.1    = 182.247.101.235
    IP.2    = 192.168.205.60

    
    # 生成秘钥，得到server.key
    openssl genrsa -out server.key 2048
    
    # 生成证书签发请求，得到server.csr
    openssl req -new -sha256 -out server.csr -key server.key -config server.conf
    
    # 用CA证书签名证书，得到server.crt
    openssl x509 -req -days 365 -CA ca.crt -CAkey ca.key -CAcreateserial -in server.csr -out server.crt -extensions req_ext -extfile server.conf
    
    # 存放文件
    /etc/pki/tls/certs/server.crt
    /etc/pki/tls/private/server.key
  
  # 配置防火墙
  firewall-cmd --permanent --add-service=https 
  firewall-cmd --permanent --add-port=443/tcp
  firewall-cmd --reload
  
  # 用户侧主机安装ca.crt
  右键ca.crt安装，安装到“受信任的根证书颁发机构”（不然server.crt还是不受信任的）
```  

