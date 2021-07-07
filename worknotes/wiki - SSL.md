# SSL
---

---
### 1 ��ǩ��SSL
```
  1 �ο�
    # https://www.cnblogs.com/nidey/p/9041960.html
    # https://www.cnblogs.com/walk1314/p/9100019.html
    
    # https://www.cnblogs.com/jie-hu/p/8034226.html
    # https://www.cnblogs.com/xiaoleiel/p/11160661.html
    # https://www.cnblogs.com/idjl/p/9610561.html
  
  2 Appache��װsslģ�飬��װ�����/etc/httpd/conf.d/����һ��ssl.conf���ļ������ļ��Ժ��ҵ�SSLCertificateFile��SSLCertificateKeyFile2�У����Կ�����������Ҫ���ɵ���Կ��������Ϣ
    yum install mod_ssl
  
  3 ��װopenssl
    yum install openssl
  
  4 ca�����ļ�
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
  
    # ����ca��Կ���õ�ca.key
    openssl genrsa -out ca.key 4096
    
    # ����ca֤��ǩ�����󣬵õ�ca.csr
    openssl req -new -sha256 -out ca.csr -key ca.key -config ca.conf
    pwd/6669
    
    # ����ca��֤�飬�õ�ca.crt
    openssl x509 -req -days 365 -in ca.csr -signkey ca.key -out ca.crt

  # appache��
    # �����ļ�
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

    
    # ������Կ���õ�server.key
    openssl genrsa -out server.key 2048
    
    # ����֤��ǩ�����󣬵õ�server.csr
    openssl req -new -sha256 -out server.csr -key server.key -config server.conf
    
    # ��CA֤��ǩ��֤�飬�õ�server.crt
    openssl x509 -req -days 365 -CA ca.crt -CAkey ca.key -CAcreateserial -in server.csr -out server.crt -extensions req_ext -extfile server.conf
    
    # ����ļ�
    /etc/pki/tls/certs/server.crt
    /etc/pki/tls/private/server.key
  
  # ���÷���ǽ
  firewall-cmd --permanent --add-service=https 
  firewall-cmd --permanent --add-port=443/tcp
  firewall-cmd --reload
  
  # �û���������װca.crt
  �Ҽ�ca.crt��װ����װ���������εĸ�֤��䷢����������Ȼserver.crt���ǲ������εģ�
```


### �����������
```
  # chrome ��װ���ȸ�������֡����
  # https://www.jianshu.com/p/6086ec29c173
    # ��¡�����زֿ⵽����
    # ��Chrome���������չ�����������Ȼ��ѡ������ģʽ��
    # �����Ͻǵ�������ѽ�ѹ����չ����ѡ���ڵ�һ�����صĹȸ���������ļ���
    # ��Ȼ���������������chrome�������װ�ȸ�������֣���ô���أ����ģ�������Ϊ���ǿ��ǵ���㡣ͬ���������GitHub��ҳ�ϣ��������ṩ��һ����վhttp://www.ggfwzs.com
    
  # ����������룬��Ҫgmail�˺ţ�freeair.sam@gmail.com��
  https://www.freenom.com/en/index.html
  www.binglang.cf  182.247.101.235
```

### �������ssl֤��
```
  # Ϊappache��װsslģ�飬��װ�����/etc/httpd/conf.d/����һ��ssl.conf���ļ������ļ��Ժ��ҵ�SSLCertificateFile��SSLCertificateKeyFile�����Կ�����������Ҫ���ɵ���Կ��������Ϣ
  yum install mod_ssl

  # https://www.cnblogs.com/esofar/p/9291685.html
  # https://www.jianshu.com/p/3aa5cb957d9f  
  
  # �õ������ͻ��� acme.sh ����
  # �� acme.sh ��װ����ǰ�û�����Ŀ¼$HOME�µ�.acme.sh�ļ����У���~/.acme.sh/��֮���������ɵ�֤��Ҳ��������Ŀ¼��
  # ��װ acme.sh
  cd ~/.acme.sh
  curl https://get.acme.sh | sh
  
  # ������һ��ָ�����alias acme.sh=~/.acme.sh/acme.sh���������ǿ���ͨ��acme.sh�������ٵ�ʹ�� acme.sh �ű�
  # acme.sh --versionȷ���Ƿ�������ʹ��acme.sh���
  
  # ����֤��
  # acme.sh --issue -d xxx.cn -d www.xxx.cn -w /var/www/html
  --issue�� acme.sh �ű������䷢֤���ָ�
  -d��--domain�ļ�ƣ����������д�ѱ�����������
  -w��--webroot�ļ�ƣ����������д��վ�ĸ�Ŀ¼��
  
  acme.sh --issue -d binglang.cf -d www.binglang.cf -w /var/www/html
  
  # ���֤��
  /etc/pki/tls/certs/fullchain.cer
  /etc/pki/tls/private/be-green.ga.key
  
  # ���÷���ǽ
  firewall-cmd --permanent --add-service=https 
  firewall-cmd --permanent --add-port=443/tcp
  firewall-cmd --reload
  
  # ����httpd
  systemctl restart httpd.service
  
  # �����վ�İ�ȫ����
  https://myssl.com
  https://www.ssllabs.com
  
  # ����֤�飬Ŀǰ Let's Encrypt ��֤����Ч����90�죬ʱ�䵽�˻��Զ����£��������κβ���
    # ǿ����ǩ֤��
    acme.sh --renew -d example.com --force
 ```   
    
    

### [��ѡ] ������ǩ��SSL
```
  # https://www.cnblogs.com/nidey/p/9041960.html
  # https://www.cnblogs.com/walk1314/p/9100019.html
  
  # https://www.cnblogs.com/jie-hu/p/8034226.html
  # https://www.cnblogs.com/xiaoleiel/p/11160661.html
  # https://www.cnblogs.com/idjl/p/9610561.html
  
  # Ϊappache��װsslģ�飬��װ�����/etc/httpd/conf.d/����һ��ssl.conf���ļ������ļ��Ժ��ҵ�SSLCertificateFile��SSLCertificateKeyFile2�У����Կ�����������Ҫ���ɵ���Կ��������Ϣ
  yum install mod_ssl
  
  # openssl
  yum install openssl
  
  # CA��
    # ca�����ļ�
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
  
    # ����ca��Կ���õ�ca.key
    openssl genrsa -out ca.key 4096
    
    # ����ca֤��ǩ�����󣬵õ�ca.csr
    openssl req -new -sha256 -out ca.csr -key ca.key -config ca.conf
    pwd/6669
    
    # ����ca��֤�飬�õ�ca.crt
    openssl x509 -req -days 365 -in ca.csr -signkey ca.key -out ca.crt

  # appache��
    # �����ļ�
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

    
    # ������Կ���õ�server.key
    openssl genrsa -out server.key 2048
    
    # ����֤��ǩ�����󣬵õ�server.csr
    openssl req -new -sha256 -out server.csr -key server.key -config server.conf
    
    # ��CA֤��ǩ��֤�飬�õ�server.crt
    openssl x509 -req -days 365 -CA ca.crt -CAkey ca.key -CAcreateserial -in server.csr -out server.crt -extensions req_ext -extfile server.conf
    
    # ����ļ�
    /etc/pki/tls/certs/server.crt
    /etc/pki/tls/private/server.key
  
  # ���÷���ǽ
  firewall-cmd --permanent --add-service=https 
  firewall-cmd --permanent --add-port=443/tcp
  firewall-cmd --reload
  
  # �û���������װca.crt
  �Ҽ�ca.crt��װ����װ���������εĸ�֤��䷢����������Ȼserver.crt���ǲ������εģ�
```  

