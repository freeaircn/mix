# Python
---
Centos

---
### 1 yum��װ python3.6
``` 
  1 ��װ
    yum install python3

  2 ����Ĭ��python��pip������
    cd /usr/bin/
    1 python
      [root@ssh bin]# ls -al python*
      lrwxrwxrwx. 1 root root     7 1��   8 12:48 python -> python2
      lrwxrwxrwx. 1 root root     9 1��   8 12:48 python2 -> python2.7
      -rwxr-xr-x. 1 root root  7144 11�� 17 06:23 python2.7
      lrwxrwxrwx. 1 root root     9 3��   3 16:01 python3 -> python3.6
      -rwxr-xr-x. 2 root root 11328 11�� 17 00:59 python3.6
      -rwxr-xr-x. 2 root root 11328 11�� 17 00:59 python3.6m
      
      rm /usr/bin/python
      ln -s python3 python
    
    2 pip
      [root@ssh bin]# ls -al pip*
      -rwxr-xr-x. 1 root root 407 10�� 14 00:03 pip3
      lrwxrwxrwx. 1 root root   9 3��   3 16:00 pip-3 -> ./pip-3.6
      lrwxrwxrwx. 1 root root   8 3��   3 16:00 pip-3.6 -> ./pip3.6
      -rwxr-xr-x. 1 root root 407 10�� 14 00:03 pip3.6

      ln -s pip3 pip
    
    3 ����pip
      pip3 install --upgrade pip
    
```

---
### 2 ������װ

https://blog.csdn.net/qq_36297936/article/details/86226189
```
  1 Ĭ��Centos7������python��װ�ģ�������2.7�汾
    [root@localhost bin]# cd /usr/bin
    [root@localhost bin]# ls python*
    python python2 python2.7
    [root@localhost bin]#
  
  2 python������һ��
    mv python python.bak
  
  3 ��װ����
    yum update
    
    yum -y groupinstall "Development tools"
    yum -y install zlib-devel bzip2-devel openssl-devel ncurses-devel sqlite-devel readline-devel tk-devel gdbm-devel db4-devel libpcap-devel xz-devel
    yum install -y libffi-devel zlib1g-dev
    yum install zlib* -y
    
    yum install -y openssl-devel openssl-static zlib-devel lzma tk-devel xz-devel bzip2-devel ncurses-devel gdbm-devel readline-devel sqlite-devel gcc libffi-devel

    yum -y install zlib-devel
    yum -y install bzip2-devel expat-devel gdbm-devel readline-devel sqlite-devel
    yum -y install libffi-devel
    yum install openssl -y
    yum install openssl-devel -y

```

---
### 3 ��װpython��

```
  1 ʹ�÷�root�û���¼
  
  2 ��
    pip install --user requests
    pip install --user serial
    pip install --user pyserial
    
  3 ��־�ļ�Ȩ��
    1 �޸���־�ļ��к��ļ�Ȩ�ޣ�ʹ����apache    
      chown -R app:app /var/meter/log
      chmod -R 766 /var/meter/log
  
  4 ��ĳ�û���ͨ��д����Ȩ��
    usermod -aG dialout app
    
```

