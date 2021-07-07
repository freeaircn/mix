# Wiki

---
### 简介  
1. 第三方模块加入app中，所需配置
```
[G] [v] [c]
```
---
### 1. GitHub
```
1. 添加用户信息

cmd /c "git config --global user.name "freeaircn""
cmd /c "git config --global user.email "freeaircn@163.com""


2. 添加SSH

ssh-keygen -t rsa -C freeaircn@163.com
# id_rsa是私钥，id_rsa.pub是公钥，登陆GitHub，打开“Account settings”，“SSH Keys”页面在Key文本框里粘贴id_rsa.pub文件的内容


3. 创建并推送本地仓库

1) GitHub创建binglang.git

2) 创建本地仓库，D:\www\binglang，存放项目代码
git init

3) 本地仓库关联远程仓库。项目根目录路径下，执行命令
git remote add origin git@github.com:freeaircn/binglang.git

4) 本地仓库推送至远程仓库
git push -u origin master


4. 复制GitHub仓库 

git clone git@github.com:freeaircn/binglang.git


5. 更新本地仓库  

git pull

hello git!

version 1.0 done

f1 branch {
    part 1
}

f2 branch {
    part 1
}

PC2 f2 branch {
    part 1
}

【使用】
1 PC1新建仓库

2 PC1关联remote，用cmd

3 PC1本地master提交版本，push至remote master

4 PC1本地创建分支 f1，PC1本地提交分支f1，并push分支f1至remote

5 PC1本地合并分支f1，用cmd

6 PC1本地删除分支f1，用cmd，remote侧分支无变化

7 PC1发起remote删除分支f1


8 PC2 拉取remote 所有分支，含master，其他分支
  1 PC2创建仓库，用cmd
  2 PC2创建本地分支（分支名对应远程分支），并建立本地分支与远程分支的关联。git创建仓库时，默认有了master分支。
    # 关联目的是如果在本地分支下操作： git pull, git push ，不需要指定在命令行指定远程的分支
    1 本地拉取remote master分支
      git pull origin master
    2 本地master 关联remote master分支
      git branch --set-upstream-to=origin/master
    3 本地创建f2分支，并拉取remote f2分支
      git switch -c f2
      git pull origin f2
    4 本地f2 关联remote f2分支
      git branch --set-upstream-to=origin/f2
    
9 PC2 在f2分支上修改，本地提交，推送至remote

10. PC2 将f2分支合并至master分支，本地提交，推送至remote
    git merge --no-ff -m "merge from f2 branch" f2
    git push origin


11. 两条线：master dev
    1 基于master 创建dev分支
    2 本地pull remote/dev分支进行修改，本地push 至remote/dev
    3 本地修改dev完毕，本地合并dev分支至master，再push至remote/master

```

---
### 2. vs code
```
1 共享vs setting  
使用Settings Sync 插件，实现vs setting上传至GitHub，多地共享vs setting
# Upload Key : Shift + Alt + U
# Download Key : Shift + Alt + D


2 常用插件
1) editorconfig
    插件功能不用手工启动。
    root = true
    [*]
    charset = utf-8
    indent_style = space
    indent_size = 2
    end_of_line = lf
    insert_final_newline = true
    trim_trailing_whitespace = true
    # end_of_line 保存文件时，触发
    # insert_final_newline 保存文件时，触发
    # trim_trailing_whitespace 保存文件时，触发

    [*.md]
    insert_final_newline = false
    trim_trailing_whitespace = false

2) Auto Close Tag
  
3) Auto Rename Tag
  
4) Prettier - Code formatter
    Using Command Palette (CMD/CTRL + Shift + P)
    1. CMD + Shift + P -> Format Document
    OR
    1. Select the text you want to Prettify
    2. CMD + Shift + P -> Format Selection
  
5) Better Align
    对齐赋值符号和注释
    Place your cursor at where you want your code to be aligned, and invoke the Align command via Command Palette or customized shortcut. Then the code will be automatically aligned
    There's no built-in shortcut comes with the extension, you have to add shotcuts by yourself:
    Open Command Palette and type open shortcuts to open keybinding settings
    Add something similar like this:
    { 
    "key": "ctrl+alt+A",  
    "command": "wwm.aligncode",
    "when": "editorTextFocus && !editorReadonly" 
    }

6) koroFileHeader
    文件头部添加注释:
    在文件开头添加注释，记录文件信息
    支持用户高度自定义注释选项
    保存文件的时候，自动更新最后的编辑时间和编辑人
    快捷键：window：ctrl+alt+i,mac：ctrl+cmd+i
    
    在光标处添加函数注释:
    在光标处自动生成一个注释模板，下方有栗子
    支持用户高度自定义注释选项
    快捷键：window：ctrl+alt+t,mac：ctrl+cmd+t

7) Better Comments
  
    注释添加颜色
      /**
       * * A
       * ! B
       * ? C
       * TODO: D
       * @param F
       */

8) Bookmarks
    鼠标右键菜单操作
  
9) Bracket Pair Colorizer

10) Code Spell Checker

11) Highlight matching tag
  
12) gitignore
  
13) Prettify JSON
  
14) String Manipulation
  
15) TODO Highlight

16) TODO Parser
    解析注释TODO
    We support both single-line and multi-line comments. For example:
    // TODO: this todo is valid
    /* TODO: this is also ok */
    /* It's a nice day today
     * Todo: multi-line TODOs are
     * supported too!
     */
    使用：
    状态栏显示当前文件的TODO数目；
    F1输入栏，输入：Parse TODOs
    
17) Vetur

18) Vscode-element-helper
  
19) Phpfmt
  
20) PHP DocBlocker

21) Settings Sync Vscode 
  0599be7e63495e01759ed12907a26cb0
```

---


---
##### 3.2 vue-cli3 引入自定义scss样式变量
```
(https://stackoverflow.com/questions/49086021/using-sass-resources-loader-with-vue-cli-v3-x)
# vue.config.js文件：
  # 文件末尾添加
    css: {
      loaderOptions: {
        sass: {
          data: `
            @import "~@/styles/app-variables.scss";
            @import "~@/styles/app-layout.scss";
          `
        }
      }
    }
```

---
##### 3.3 添加logo-svg组件
```
模仿icons组件 https://juejin.im/post/59bb864b5188257e7a427c09
# src/components/，加入SvgLogo/
# src/，加入logos/
# src/main.js文件
  import './logos' // logo
# vue.config.js文件：
  // for logo svg
  config.module
    .rule('svg')
    .exclude.add(resolve('src/logos'))
    .end()
  config.module
    .rule('logos')
    .test(/\.svg$/)
    .include.add(resolve('src/logos'))
    .end()
    .use('svg-sprite-loader')
    .loader('svg-sprite-loader')
    .options({
      symbolId: 'logo-[name]'
    })
    .end()
# 在src/logos/中，存放自定义svg图片
```





---
### 7. Sql  
##### 7.1 外键
```
student和grade，学生表中的gid是学生所在的班级id，是引入了班级表grade中的主键id。
那么gid就可以作为表student表的外键。
被引用的表，即表grade是主表，使用外键的表，即student，是从表。


https://github.com/vueComponent/ant-design-vue-pro/issues/91
```