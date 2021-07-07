# Wiki - JS, PHP

---
### 1. JS 数据类型
```
1. 数据类型

  值类型(基本类型)：字符串（String）、数字(Number)、布尔(Boolean)、对空（Null）、未定义（Undefined）、Symbol。
  
  引用数据类型：对象(Object)、数组(Array)、函数(Function)。

2. 数组

  下面的代码创建名为 cars 的数组：
  var cars=new Array();
  cars[0]="Saab";
  cars[1]="Volvo";
  
  或者：var cars=new Array("Saab","Volvo","BMW");
  
  或者：var cars=["Saab","Volvo","BMW"];
  
  数组下标是基于零的，所以第一个项目是 [0]，第二个是 [1]，以此类推。

3. 对象

  对象由花括号分隔。在括号内部，对象的属性以名称和值对的形式 (name : value) 来定义。属性由逗号分隔：
  
  var person={firstname:"John", lastname:"Doe", id:5566};
  
  对象属性有两种寻址方式：
  name=person.lastname;
  name=person["lastname"];
  
  对象的方法定义了一个函数，并作为对象的属性存储。
  对象方法通过添加 () 调用 (作为一个函数)。
  可以使用以下语法创建对象方法：
  methodName : function() {
      // 代码 
  }
  你可以使用以下语法访问对象方法：
  objectName.methodName()
  
  
  原型：原型上包括了继承属性，有可以枚举的属性和不可以枚举的属性。默认对象都继承了Object。
  自身：自身属性同样包括了可枚举的属性和不可枚举的属性。
    
    Object.keys(obj)返回不包括原型上的可枚举属性，即自身的可枚举属性。
      Object.keys(data).length === 0;
    Objcet.getOwnPropertyNames(obj)返回不包括原型上的所有自身属性(包括不可枚举的属性)
      Object.getOwnPropertyNames(data)===0;


4. Undefined 和 Null 

  Undefined 这个值表示变量不含有值。
  可以通过将变量的值设置为 null 来清空变量。

5. 声明变量类型

  可以使用关键词 "new" 来声明其类型：
  var carname=new String;
  var x=      new Number;
  var y=      new Boolean;
  var cars=   new Array;
  var person= new Object;

```

---
### 2. JS 循环
```
1 For in 循环
  
  for/in 语句循环遍历对象的属性：
  
  var person={fname:"Bill",lname:"Gates",age:56}; 
  for (x in person)  // x 为属性名
  {
      txt=txt + person[x];
  }

```

---
### 3. JS typeof, null, 和 undefined
```
1 使用 typeof 操作符来检测变量的数据类型。

  typeof "John"                // 返回 string
  typeof 3.14                  // 返回 number
  typeof false                 // 返回 boolean
  typeof [1,2,3,4]             // 返回 object
  typeof {name:'John', age:34} // 返回 object

2 null是一个只有一个值的特殊类型。表示一个空对象引用。

  用 typeof 检测 null 返回是object。
  可以设置为 null 来清空对象:
  var person = null;           // 值为 null(空), 但类型为对象

  可以设置为 undefined 来清空对象:
  var person = undefined;     // 值为 undefined, 类型为 undefined

3 undefined 是一个没有设置值的变量。

  typeof 一个没有值的变量会返回 undefined。
  var person;                  // 值为 undefined(空), 类型是undefined

  任何变量都可以通过设置值为 undefined 来清空。 类型为 undefined.
  person = undefined;          // 值为 undefined, 类型是undefined

4 null 和 undefined 的值相等，但类型不等：

  typeof undefined             // undefined
  typeof null                  // object
  null === undefined           // false
  null == undefined            // true

```

---
### 4 JS import / require
```

1 require 运行时加载，因为只有运行时才能得到这个对象，不能在编译时做到静态化。
  import ES6模块不是对象，而是通过export命令显示指定输出代码，再通过import输入。
  
  加载方式	    规范	          命令	      特点
  运行时加载	  CommonJS/AMD	  require	    社区方案，提供了服务器/浏览器的模块加载方案。非语言层面的标准。只能在运行时确定模块的依赖关系及输入/输出的变量，无法进行静态优化。
  编译时加载	  ESMAScript6+	  import	    语言规格层面支持模块功能。支持编译时静态分析，便于JS引入宏和类型检验。动态绑定。


2 使用
  导出lib.js
    export function func1(x) {
        return x * x;
    }
    
    export function func2(x, y) {
        return ;
    }
  
  引用
  //方法一
  import { func1, func2 } from 'lib';
  console.log(func1());
  console.log(func2());

  //方法二
  import * as utils from 'lib';
  utils.func1();

```

---
### 5. PHP 数据类型
```
1. 数据类型

  String（字符串）, Integer（整型）, Float（浮点型）, Boolean（布尔型）, Array（数组）, Object（对象）, NULL（空值）。

2. 数组

  在 PHP 中，有三种类型的数组：
    数值数组 - 带有数字 ID 键的数组
    关联数组 - 带有指定的键的数组，每个键关联一个值
    多维数组 - 包含一个或多个数组的数组

  数值数组
    自动分配 ID 键（ID 键总是从 0 开始）：
    $cars=array("Volvo","BMW","Toyota");
    
    人工分配 ID 键：
    $cars[0]="Volvo";
    $cars[1]="BMW";
    $cars[2]="Toyota";
  
  遍历数值数组
    使用 for 循环，如下所示：
      <?php
      $cars=array("Volvo","BMW","Toyota");
      $arrlength=count($cars);
       
      for($x=0;$x<$arrlength;$x++)
      {
          echo $cars[$x];
          echo "<br>";
      }
  
  关联数组
    有两种创建关联数组的方法：
    $age=array("Peter"=>"35","Ben"=>"37","Joe"=>"43");
    
    或者
    $age['Peter']="35";
    $age['Ben']="37";
    $age['Joe']="43";
  
  遍历关联数组
    使用 foreach 循环，如下所示：
      <?php
      $age=array("Peter"=>"35","Ben"=>"37","Joe"=>"43");
       
      foreach($age as $x=>$x_value)
      {
          echo "Key=" . $x . ", Value=" . $x_value;
          echo "<br>";
      }
      ?>
  
  

3. 对象


4. Null 

  NULL 值表示变量没有值。NULL 是数据类型为 NULL 的值。
  NULL 值指明一个变量是否为空值。 同样可用于数据空值和NULL值的区别。
  可以通过设置变量值为 NULL 来清空变量数据：

5. 使用 PHP 函数对变量 $x 进行比较

  表达式	        gettype()	  empty()	  is_null()	  isset()	  boolean : if($x)
  $x = "";	      string	    TRUE	      FALSE	    TRUE	    FALSE
  $x = null;	    NULL	      TRUE	      TRUE	    FALSE	    FALSE
  var $x;	        NULL	      TRUE	      TRUE	    FALSE	    FALSE
  $x is undefined	NULL	      TRUE	      TRUE	    FALSE	    FALSE
  $x = array();	  array	      TRUE	      FALSE	    TRUE	    FALSE
  $x = FALSE;	    boolean	    TRUE	      FALSE	    TRUE	    FALSE
  $x = TRUE;	    boolean	    FALSE	      FALSE	    TRUE	    TRUE
  $x = 1;	        integer	    FALSE	      FALSE	    TRUE	    TRUE
  $x = 42;	      integer	    FALSE	      FALSE	    TRUE	    TRUE
  $x = 0;	        integer	    TRUE	      FALSE	    TRUE	    FALSE
  $x = -1;	      integer	    FALSE	      FALSE	    TRUE	    TRUE
  $x = "1";	      string	    FALSE	      FALSE	    TRUE	    TRUE
  $x = "0";	      string	    TRUE	      FALSE	    TRUE	    FALSE
  $x = "-1";	    string	    FALSE	      FALSE	    TRUE	    TRUE
  $x = "php";	    string	    FALSE	      FALSE	    TRUE	    TRUE
  $x = "TRUE";	  string	    FALSE	      FALSE	    TRUE	    TRUE
  $x = "FALSE";	  string	    FALSE	      FALSE	    TRUE	    TRUE

  多维数组入参。比如；a = [[], []], empty(a) false

6 PHP 常量

  常量值被定义后，在脚本的其他任何地方都不能被改变。
  一个常量由英文字母、下划线、和数字组成,但数字不能作为首字母出现。 (常量名不需要加 $ 修饰符)。

  设置常量，使用 define() 函数，函数语法如下：
  bool define ( string $name , mixed $value [, bool $case_insensitive = false ] )

  常量在定义后，默认是全局变量，可以在整个运行的脚本的任何地方使用。即便常量定义在函数外也可以正常使用常量。

```

---
### 6. PHP 循环
```
1 foreach  循环
  
  foreach 循环用于遍历数组。
  语法
  foreach ($array as $value)
  {
      要执行代码;
  }
  每进行一次循环，当前数组元素的值就会被赋值给 $value 变量（数组指针会逐一地移动），在进行下一次循环时，您将看到数组中的下一个值。

  foreach ($array as $key => $value)
  {
      要执行代码;
  }
  每一次循环，当前数组元素的键与值就都会被赋值给 $key 和 $value 变量（数字指针会逐一地移动），在进行下一次循环时，你将看到数组中的下一个键与值。

```

---
### 7. php，js，Python时间戳的比较
```
1 单位问题：
  php中取时间戳时，大多通过time()方法来获得，它获取到数值是以秒作为单位的。
  
  javascript中，从Date对象的getTime()方法中获得的数值是以毫秒为单位。
  
  Python time time() 返回当前时间的时间戳（1970纪元后经过的浮点秒数）。

2 时区问题：
  在php代码中会设置好当前服务器所在的时区，如中国大陆的服务器通常会设置成东八区。
    php.ini 文件
    [Date]
    ; Defines the default timezone used by the date functions
    ; http://php.net/date.timezone
    date.timezone = "Asia/Shangha"
  time()方法获得的方法就不再是从1970年1月1日0时0分0秒起，而是从1970年1月1日8时0分0秒起。

  js中通常没有作时区相关的设置，以1970年1月1日0时0分0秒为计算的起点的，所以容易在这个地方造成不一致。

```

---

### 8. HTTP请求和响应
```
1 axios params和data
  在使用axios时，配置选项中包含params和data两者:
  
  params是添加到url的请求字符串中的，用于get请求。 
   
  data是添加到请求体（body）中的， 用于post请求。

2 Content-Type
  在Http协议消息头中，使用Content-Type来表示具体请求中的媒体类型信息.
  
  常见的媒体格式类型如下：
    text/html ： HTML格式
    text/plain ：纯文本格式      
    text/xml ：  XML格式
    image/gif ：gif图片格式    
    image/jpeg ：jpg图片格式 
    image/png：png图片格式
  以application开头的媒体格式类型：
    application/xhtml+xml ：XHTML格式
    application/xml     ： XML数据格式
    application/atom+xml  ：Atom XML聚合格式    
    application/json    ： JSON数据格式
    application/pdf       ：pdf格式  
    application/msword  ： Word文档格式
    application/octet-stream ： 二进制流数据（如常见的文件下载）
    application/x-www-form-urlencoded ： <form encType=””>中默认的encType，form表单数据被编码为key/value格式发送到服务器（表单默认的提交数据的格式

3 axios的post 不采用qs 序列化，采用转换json
  undefined或空数组，axios post 提交时，qs不填入http body。

  // Post请求，指定转换请求方法，使用json
  axios.defaults.transformRequest = [function(data) {
    // return qs.stringify(data, { arrayFormat: 'indices' })
    return JSON.stringify(data)
  }]

  // Get请求，指定请求参数序列号方法
  axios.defaults.paramsSerializer = function(params) {
    return qs.stringify(params, { arrayFormat: 'indices' })
  }

4 axios post 的data类型是“对象”，使用JSON.stringify(data)转换后，在http的请求payload中变为对象的键值对形式，form表单的input输入都当作字符串，所以值的数据类型都是string：
  {
    key1 : "123",
    key2 : "abc"
  }

5 CI、restful、DB
  
  1 接收get请求，$client的类型是“关联数组”。
    $client = $this->get();

  2 接收post，put，delete请求，$client的类型是“关联数组”。
    $stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
    
    $client = json_decode($stream_clean, true);
    
  3 写DB
    ?? php的弱类型
    DB数据类型是”int“，但写入的数据类型是”string“，例如：
    DB的sort数据类型是int，写入DB sort = "123"，DB表中也能正确存储123。
    
  4 读DB
    ci 查询数据表结果->result_array()，为二维数组结构。即使查询结果只有一行，也是二维数组。第一维索引是数字，每一行是一维数组，且是关联数组。例如：
    一条结果
    [
      [0] => [
        "id" => "2",
        "name" => "A"
      ]
    ]
    多条结果
    [
      [0] => [
        "id" => "2",
        "name" => "A"
      ],
      [1] => [
        "id" => "3",
        "name" => "B"
      [
    ]
    
    CI的DB API读取的结果，所有值的数据类型都是”string“，例如：
    DB数据表的sort数据类型int，sort：13，CI的读取结果是sort：“13”。

  5 发送响应，$res的类型是“关联数组”。
    $res['code'] = App_Code::SUCCESS;
    $res['msg']  = App_Msg::SUCCESS;

    $this->response($res, 200);
    
    同样，在http reponse中转换为json，值的“数据类型”
    {
      "key1" : "123",
      "key2" : 12
    }

6 axios接收响应，response.data的数据类型是”对象“
  console.log(response.data)
  __proto__: Object
  
```

---