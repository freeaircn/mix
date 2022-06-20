---
### Debug

```
Services::autoloader()->initialize(new Autoload(), new Modules())

Autoloader

D:\www\mix\server\vendor\codeigniter4\framework\system\Autoloader\Autoloader.php:346:
array (size=7)
  'CodeIgniter' => 
    array (size=1)
      0 => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\' (length=55)
  'App' => 
    array (size=1)
      0 => string 'D:\www\mix\server\vendor\composer/../../app' (length=43)
  'Config' => 
    array (size=1)
      0 => string 'D:\www\mix\server\vendor\composer/../../app/Config' (length=50)
  'Psr\Log' => 
    array (size=1)
      0 => string 'D:\www\mix\server\vendor\composer/../psr/log/Psr/Log' (length=52)
  'Laminas\ZendFrameworkBridge' => 
    array (size=1)
      0 => string 'D:\www\mix\server\vendor\composer/../laminas/laminas-zendframework-bridge/src' (length=77)
  'Laminas\Escaper' => 
    array (size=1)
      0 => string 'D:\www\mix\server\vendor\composer/../laminas/laminas-escaper/src' (length=64)
  'Kint' => 
    array (size=1)
      0 => string 'D:\www\mix\server\vendor\composer/../kint-php/kint/src' (length=54)
D:\www\mix\server\vendor\codeigniter4\framework\system\Autoloader\Autoloader.php:347:
array (size=10)
  'Psr\Log\AbstractLogger' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/AbstractLogger.php' (length=92)
  'Psr\Log\InvalidArgumentException' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/InvalidArgumentException.php' (length=102)
  'Psr\Log\LoggerAwareInterface' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LoggerAwareInterface.php' (length=98)
  'Psr\Log\LoggerAwareTrait' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LoggerAwareTrait.php' (length=94)
  'Psr\Log\LoggerInterface' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LoggerInterface.php' (length=93)
  'Psr\Log\LoggerTrait' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LoggerTrait.php' (length=89)
  'Psr\Log\LogLevel' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LogLevel.php' (length=86)
  'Psr\Log\NullLogger' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/NullLogger.php' (length=88)
  'Laminas\Escaper\Escaper' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/Escaper/Escaper.php' (length=85)
  'Composer\InstalledVersions' => string 'D:\www\mix\server\vendor\composer/../composer/InstalledVersions.php' (length=67)
      

serviceExists
buildServicesCache
  $locator = static::locator();
  $files   = $locator->search('Config/Services');


D:\www\mix\server\vendor\codeigniter4\framework\system\Autoloader\FileLocator.php:213:
array (size=2)
  0 => string 'D:\www\mix\server\app\Config\Services.php' (length=41)
  1 => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\Config\Services.php' (length=74)
  


__callStatic

serviceExists

getSharedInstance



array (size=7)
  'autoloader' => 
    object(CodeIgniter\Autoloader\Autoloader)[2]
      protected 'prefixes' => 
        array (size=7)
          'CodeIgniter' => 
            array (size=1)
              ...
          'App' => 
            array (size=1)
              ...
          'Config' => 
            array (size=1)
              ...
          'Psr\Log' => 
            array (size=1)
              ...
          'Laminas\ZendFrameworkBridge' => 
            array (size=1)
              ...
          'Laminas\Escaper' => 
            array (size=1)
              ...
          'Kint' => 
            array (size=1)
              ...
      protected 'classmap' => 
        array (size=10)
          'Psr\Log\AbstractLogger' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/AbstractLogger.php' (length=92)
          'Psr\Log\InvalidArgumentException' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/InvalidArgumentException.php' (length=102)
          'Psr\Log\LoggerAwareInterface' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LoggerAwareInterface.php' (length=98)
          'Psr\Log\LoggerAwareTrait' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LoggerAwareTrait.php' (length=94)
          'Psr\Log\LoggerInterface' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LoggerInterface.php' (length=93)
          'Psr\Log\LoggerTrait' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LoggerTrait.php' (length=89)
          'Psr\Log\LogLevel' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/LogLevel.php' (length=86)
          'Psr\Log\NullLogger' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/PSR/Log/NullLogger.php' (length=88)
          'Laminas\Escaper\Escaper' => string 'D:\www\mix\server\vendor\codeigniter4\framework\system\ThirdParty/Escaper/Escaper.php' (length=85)
          'Composer\InstalledVersions' => string 'D:\www\mix\server\vendor\composer/../composer/InstalledVersions.php' (length=67)
      protected 'files' => 
        array (size=0)
          empty
  'locator' => 
    object(CodeIgniter\Autoloader\FileLocator)[3]
      protected 'autoloader' => 
        object(CodeIgniter\Autoloader\Autoloader)[2]
          protected 'prefixes' => 
            array (size=7)
              ...
          protected 'classmap' => 
            array (size=10)
              ...
          protected 'files' => 
            array (size=0)
              ...
  'codeigniter' => 
    object(CodeIgniter\CodeIgniter)[12]
      protected 'startTime' => float 1624544794.496
      protected 'totalTime' => null
      protected 'config' => 
        object(Config\App)[11]
          public 'baseURL' => string 'http://localhost:8080/' (length=22)
          public 'indexPage' => string 'index.php' (length=9)
          public 'uriProtocol' => string 'REQUEST_URI' (length=11)
          public 'defaultLocale' => string 'en' (length=2)
          public 'negotiateLocale' => boolean false
          public 'supportedLocales' => 
            array (size=1)
              ...
          public 'appTimezone' => string 'America/Chicago' (length=15)
          public 'charset' => string 'UTF-8' (length=5)
          public 'forceGlobalSecureRequests' => boolean false
          public 'sessionDriver' => string 'CodeIgniter\Session\Handlers\FileHandler' (length=40)
          public 'sessionCookieName' => string 'ci_session' (length=10)
          public 'sessionExpiration' => int 7200
          public 'sessionSavePath' => string 'D:\www\mix\server\writable\session' (length=34)
          public 'sessionMatchIP' => boolean false
          public 'sessionTimeToUpdate' => int 300
          public 'sessionRegenerateDestroy' => boolean false
          public 'cookiePrefix' => string '' (length=0)
          public 'cookieDomain' => string '' (length=0)
          public 'cookiePath' => string '/' (length=1)
          public 'cookieSecure' => boolean false
          public 'cookieHTTPOnly' => boolean true
          public 'cookieSameSite' => string 'Lax' (length=3)
          public 'proxyIPs' => string '' (length=0)
          public 'CSRFTokenName' => string 'csrf_test_name' (length=14)
          public 'CSRFHeaderName' => string 'X-CSRF-TOKEN' (length=12)
          public 'CSRFCookieName' => string 'csrf_cookie_name' (length=16)
          public 'CSRFExpire' => int 7200
          public 'CSRFRegenerate' => boolean true
          public 'CSRFRedirect' => boolean true
          public 'CSRFSameSite' => string 'Lax' (length=3)
          public 'CSPEnabled' => boolean false
      protected 'benchmark' => null
      protected 'request' => null
      protected 'response' => null
      protected 'router' => null
      protected 'controller' => null
      protected 'method' => null
      protected 'output' => null
      protected 'path' => null
      protected 'useSafeOutput' => boolean false
  'uri' => 
    object(CodeIgniter\HTTP\URI)[15]
      protected 'uriString' => null
      protected 'segments' => 
        array (size=0)
          empty
      protected 'scheme' => string 'http' (length=4)
      protected 'user' => null
      protected 'password' => null
      protected 'host' => string 'localhost' (length=9)
      protected 'port' => int 8080
      protected 'path' => string '/' (length=1)
      protected 'fragment' => string '' (length=0)
      protected 'query' => 
        array (size=0)
          empty
      protected 'defaultPorts' => 
        array (size=4)
          'http' => int 80
          'https' => int 443
          'ftp' => int 21
          'sftp' => int 22
      protected 'showPassword' => boolean false
      protected 'silent' => boolean false
      protected 'rawQueryString' => boolean false
  'request' => 
    object(CodeIgniter\HTTP\IncomingRequest)[14]
      protected 'enableCSRF' => boolean false
      public 'uri' => 
        object(CodeIgniter\HTTP\URI)[15]
          protected 'uriString' => null
          protected 'segments' => 
            array (size=0)
              ...
          protected 'scheme' => string 'http' (length=4)
          protected 'user' => null
          protected 'password' => null
          protected 'host' => string 'localhost' (length=9)
          protected 'port' => int 8080
          protected 'path' => string '/' (length=1)
          protected 'fragment' => string '' (length=0)
          protected 'query' => 
            array (size=0)
              ...
          protected 'defaultPorts' => 
            array (size=4)
              ...
          protected 'showPassword' => boolean false
          protected 'silent' => boolean false
          protected 'rawQueryString' => boolean false
      protected 'path' => string '/' (length=1)
      protected 'files' => null
      protected 'negotiator' => null
      protected 'defaultLocale' => string 'en' (length=2)
      protected 'locale' => string 'en' (length=2)
      protected 'validLocales' => 
        array (size=1)
          0 => string 'en' (length=2)
      public 'config' => 
        object(Config\App)[11]
          public 'baseURL' => string 'http://localhost:8080/' (length=22)
          public 'indexPage' => string 'index.php' (length=9)
          public 'uriProtocol' => string 'REQUEST_URI' (length=11)
          public 'defaultLocale' => string 'en' (length=2)
          public 'negotiateLocale' => boolean false
          public 'supportedLocales' => 
            array (size=1)
              ...
          public 'appTimezone' => string 'America/Chicago' (length=15)
          public 'charset' => string 'UTF-8' (length=5)
          public 'forceGlobalSecureRequests' => boolean false
          public 'sessionDriver' => string 'CodeIgniter\Session\Handlers\FileHandler' (length=40)
          public 'sessionCookieName' => string 'ci_session' (length=10)
          public 'sessionExpiration' => int 7200
          public 'sessionSavePath' => string 'D:\www\mix\server\writable\session' (length=34)
          public 'sessionMatchIP' => boolean false
          public 'sessionTimeToUpdate' => int 300
          public 'sessionRegenerateDestroy' => boolean false
          public 'cookiePrefix' => string '' (length=0)
          public 'cookieDomain' => string '' (length=0)
          public 'cookiePath' => string '/' (length=1)
          public 'cookieSecure' => boolean false
          public 'cookieHTTPOnly' => boolean true
          public 'cookieSameSite' => string 'Lax' (length=3)
          public 'proxyIPs' => string '' (length=0)
          public 'CSRFTokenName' => string 'csrf_test_name' (length=14)
          public 'CSRFHeaderName' => string 'X-CSRF-TOKEN' (length=12)
          public 'CSRFCookieName' => string 'csrf_cookie_name' (length=16)
          public 'CSRFExpire' => int 7200
          public 'CSRFRegenerate' => boolean true
          public 'CSRFRedirect' => boolean true
          public 'CSRFSameSite' => string 'Lax' (length=3)
          public 'CSPEnabled' => boolean false
      protected 'oldInput' => 
        array (size=0)
          empty
      protected 'userAgent' => 
        object(CodeIgniter\HTTP\UserAgent)[16]
          protected 'agent' => string 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36' (length=115)
          protected 'isBrowser' => boolean true
          protected 'isRobot' => boolean false
          protected 'isMobile' => boolean false
          protected 'config' => 
            object(Config\UserAgents)[17]
              ...
          protected 'platform' => string 'Windows 10' (length=10)
          protected 'browser' => string 'Chrome' (length=6)
          protected 'version' => string '90.0.4430.212' (length=13)
          protected 'mobile' => string '' (length=0)
          protected 'robot' => string '' (length=0)
          protected 'referrer' => null
      protected 'proxyIPs' => string '' (length=0)
      protected 'method' => string 'GET' (length=3)
      protected 'protocolVersion' => null
      protected 'validProtocolVersions' => 
        array (size=3)
          0 => string '1.0' (length=3)
          1 => string '1.1' (length=3)
          2 => string '2.0' (length=3)
      protected 'body' => null
      protected 'headers' => 
        array (size=15)
          'Host' => 
            object(CodeIgniter\HTTP\Header)[18]
              ...
          'Connection' => 
            object(CodeIgniter\HTTP\Header)[19]
              ...
          'Cache-Control' => 
            object(CodeIgniter\HTTP\Header)[20]
              ...
          'Sec-Ch-Ua' => 
            object(CodeIgniter\HTTP\Header)[21]
              ...
          'Sec-Ch-Ua-Mobile' => 
            object(CodeIgniter\HTTP\Header)[22]
              ...
          'Upgrade-Insecure-Requests' => 
            object(CodeIgniter\HTTP\Header)[23]
              ...
          'User-Agent' => 
            object(CodeIgniter\HTTP\Header)[24]
              ...
          'Accept' => 
            object(CodeIgniter\HTTP\Header)[25]
              ...
          'Sec-Fetch-Site' => 
            object(CodeIgniter\HTTP\Header)[26]
              ...
          'Sec-Fetch-Mode' => 
            object(CodeIgniter\HTTP\Header)[27]
              ...
          'Sec-Fetch-User' => 
            object(CodeIgniter\HTTP\Header)[28]
              ...
          'Sec-Fetch-Dest' => 
            object(CodeIgniter\HTTP\Header)[29]
              ...
          'Accept-Encoding' => 
            object(CodeIgniter\HTTP\Header)[30]
              ...
          'Accept-Language' => 
            object(CodeIgniter\HTTP\Header)[31]
              ...
          'Cookie' => 
            object(CodeIgniter\HTTP\Header)[32]
              ...
      protected 'headerMap' => 
        array (size=15)
          'host' => string 'Host' (length=4)
          'connection' => string 'Connection' (length=10)
          'cache-control' => string 'Cache-Control' (length=13)
          'sec-ch-ua' => string 'Sec-Ch-Ua' (length=9)
          'sec-ch-ua-mobile' => string 'Sec-Ch-Ua-Mobile' (length=16)
          'upgrade-insecure-requests' => string 'Upgrade-Insecure-Requests' (length=25)
          'user-agent' => string 'User-Agent' (length=10)
          'accept' => string 'Accept' (length=6)
          'sec-fetch-site' => string 'Sec-Fetch-Site' (length=14)
          'sec-fetch-mode' => string 'Sec-Fetch-Mode' (length=14)
          'sec-fetch-user' => string 'Sec-Fetch-User' (length=14)
          'sec-fetch-dest' => string 'Sec-Fetch-Dest' (length=14)
          'accept-encoding' => string 'Accept-Encoding' (length=15)
          'accept-language' => string 'Accept-Language' (length=15)
          'cookie' => string 'Cookie' (length=6)
      protected 'ipAddress' => string '' (length=0)
      protected 'globals' => 
        array (size=2)
          'server' => 
            array (size=43)
              ...
          'get' => 
            array (size=0)
              ...
  'response' => 
    object(CodeIgniter\HTTP\Response)[33]
      protected 'reason' => string '' (length=0)
      protected 'statusCode' => int 200
      protected 'pretend' => boolean false
      protected 'protocolVersion' => null
      protected 'validProtocolVersions' => 
        array (size=3)
          0 => string '1.0' (length=3)
          1 => string '1.1' (length=3)
          2 => string '2.0' (length=3)
      protected 'body' => null
      protected 'headers' => 
        array (size=2)
          'Cache-control' => 
            object(CodeIgniter\HTTP\Header)[34]
              ...
          'Content-Type' => 
            object(CodeIgniter\HTTP\Header)[38]
              ...
      protected 'headerMap' => 
        array (size=2)
          'cache-control' => string 'Cache-control' (length=13)
          'content-type' => string 'Content-Type' (length=12)
      protected 'CSPEnabled' => boolean false
      public 'CSP' => 
        object(CodeIgniter\HTTP\ContentSecurityPolicy)[35]
          protected 'baseURI' => null
          protected 'childSrc' => string 'self' (length=4)
          protected 'connectSrc' => string 'self' (length=4)
          protected 'defaultSrc' => null
          protected 'fontSrc' => null
          protected 'formAction' => string 'self' (length=4)
          protected 'frameAncestors' => null
          protected 'frameSrc' => null
          protected 'imageSrc' => string 'self' (length=4)
          protected 'mediaSrc' => null
          protected 'objectSrc' => string 'self' (length=4)
          protected 'pluginTypes' => null
          protected 'reportURI' => null
          protected 'sandbox' => null
          protected 'scriptSrc' => string 'self' (length=4)
          protected 'styleSrc' => string 'self' (length=4)
          protected 'manifestSrc' => null
          protected 'upgradeInsecureRequests' => boolean false
          protected 'reportOnly' => boolean false
          protected 'validSources' => 
            array (size=4)
              ...
          protected 'nonces' => 
            array (size=0)
              ...
          protected 'tempHeaders' => 
            array (size=0)
              ...
          protected 'reportOnlyHeaders' => 
            array (size=0)
              ...
      protected 'cookieStore' => 
        object(CodeIgniter\Cookie\CookieStore)[36]
          protected 'cookies' => 
            array (size=0)
              ...
      protected 'cookiePrefix' => string '' (length=0)
      protected 'cookieDomain' => string '' (length=0)
      protected 'cookiePath' => string '/' (length=1)
      protected 'cookieSecure' => boolean false
      protected 'cookieHTTPOnly' => boolean true
      protected 'cookieSameSite' => string 'Lax' (length=3)
      protected 'cookies' => 
        array (size=0)
          empty
      protected 'bodyFormat' => string 'html' (length=4)
  'exceptions' => 
    object(CodeIgniter\Debug\Exceptions)[39]
      public 'ob_level' => int 1
      protected 'viewPath' => string 'D:\www\mix\server\app\Views/errors\' (length=35)
      protected 'config' => 
        object(Config\Exceptions)[13]
          public 'log' => boolean true
          public 'ignoreCodes' => 
            array (size=1)
              ...
          public 'errorViewPath' => string 'D:\www\mix\server\app\Views/errors' (length=34)
          public 'sensitiveDataInTrace' => 
            array (size=0)
              ...
      protected 'request' => 
        object(CodeIgniter\HTTP\IncomingRequest)[14]
          protected 'enableCSRF' => boolean false
          public 'uri' => 
            object(CodeIgniter\HTTP\URI)[15]
              ...
          protected 'path' => string '/' (length=1)
          protected 'files' => null
          protected 'negotiator' => null
          protected 'defaultLocale' => string 'en' (length=2)
          protected 'locale' => string 'en' (length=2)
          protected 'validLocales' => 
            array (size=1)
              ...
          public 'config' => 
            object(Config\App)[11]
              ...
          protected 'oldInput' => 
            array (size=0)
              ...
          protected 'userAgent' => 
            object(CodeIgniter\HTTP\UserAgent)[16]
              ...
          protected 'proxyIPs' => string '' (length=0)
          protected 'method' => string 'GET' (length=3)
          protected 'protocolVersion' => null
          protected 'validProtocolVersions' => 
            array (size=3)
              ...
          protected 'body' => null
          protected 'headers' => 
            array (size=15)
              ...
          protected 'headerMap' => 
            array (size=15)
              ...
          protected 'ipAddress' => string '' (length=0)
          protected 'globals' => 
            array (size=2)
              ...
      protected 'response' => 
        object(CodeIgniter\HTTP\Response)[33]
          protected 'reason' => string '' (length=0)
          protected 'statusCode' => int 200
          protected 'pretend' => boolean false
          protected 'protocolVersion' => null
          protected 'validProtocolVersions' => 
            array (size=3)
              ...
          protected 'body' => null
          protected 'headers' => 
            array (size=2)
              ...
          protected 'headerMap' => 
            array (size=2)
              ...
          protected 'CSPEnabled' => boolean false
          public 'CSP' => 
            object(CodeIgniter\HTTP\ContentSecurityPolicy)[35]
              ...
          protected 'cookieStore' => 
            object(CodeIgniter\Cookie\CookieStore)[36]
              ...
          protected 'cookiePrefix' => string '' (length=0)
          protected 'cookieDomain' => string '' (length=0)
          protected 'cookiePath' => string '/' (length=1)
          protected 'cookieSecure' => boolean false
          protected 'cookieHTTPOnly' => boolean true
          protected 'cookieSameSite' => string 'Lax' (length=3)
          protected 'cookies' => 
            array (size=0)
              ...
          protected 'bodyFormat' => string 'html' (length=4)
      protected 'codes' => 
        array (size=28)
          'created' => int 201
          'deleted' => int 200
          'updated' => int 200
          'no_content' => int 204
          'invalid_request' => int 400
          'unsupported_response_type' => int 400
          'invalid_scope' => int 400
          'temporarily_unavailable' => int 400
          'invalid_grant' => int 400
          'invalid_credentials' => int 400
          'invalid_refresh' => int 400
          'no_data' => int 400
          'invalid_data' => int 400
          'access_denied' => int 401
          'unauthorized' => int 401
          'invalid_client' => int 401
          'forbidden' => int 403
          'resource_not_found' => int 404
          'not_acceptable' => int 406
          'resource_exists' => int 409
          'conflict' => int 409
          'resource_gone' => int 410
          'payload_too_large' => int 413
          'unsupported_media_type' => int 415
          'too_many_requests' => int 429
          'server_error' => int 500
          'unsupported_grant_type' => int 501
          'not_implemented' => int 501
      protected 'format' => string 'json' (length=4)
      protected 'formatter' => null
D:\www\mix\server\vendor\codeigniter4\framework\system\Config\BaseService.php:401:
array (size=1)
  0 => string 'Config\Services' (length=15)
D:\www\mix\server\vendor\codeigniter4\framework\system\Config\BaseService.php:402:
array (size=1)
  0 => 
    object(Config\Services)[10]
    
    
```


```
服务
$name = logger

Services::$name(...$params);

use Config\Services;

class Services extends BaseService

use CodeIgniter\Config\BaseService;
  protected static $instances = [];
  protected static $mocks = [];
  protected static $discovered = false;
  protected static $services = [];
  private static $serviceNames = [];

  public static function __callStatic(string $name, array $arguments)

    $service = static::serviceExists($name);

      static::buildServicesCache();
      
        $locator = static::locator();
        $files   = $locator->search('Config/Services');
        
      $services = array_merge(self::$serviceNames, [Services::class]);
      $name     = strtolower($name);
      
      foreach ($services as $service) {
              if (method_exists($service, $name)) {
                  return $service;
              }
      }


到Config\Services，CodeIgniter\Config\Services中去查找。

找到同名方法后，创建并返回 $name类 实例

```