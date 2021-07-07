# VUE 引入Element UI和Vant UI组件
---

---
### 1 Vant 2
```
  1 安装 Vant 2
    npm i vant -S
    
  2 自动按需引入组件
    # babel-plugin-import 是一款 babel 插件
    npm i babel-plugin-import -D
    
    # 对于使用 babel7 的用户，可以在 babel.config.js 中配置
    module.exports = {
      plugins: [
        ['import', {
          libraryName: 'vant',
          libraryDirectory: 'es',
          style: true
        }, 'vant']
      ]
    };
    
  3 引入组件
    // main.js
    import { Button as VanButton } from 'vant';
    Vue.component(VanButton.name, VanButton)
    
    // template
    <van-button>按钮</van-button>
    
```

---
### 2 Antd
```
  1 安装 Vant 2
    npm i --save ant-design-vue
    
    npm install less less-loader --save-dev
    
  2 自动按需引入组件
    
    # 对于使用 babel7 的用户，可以在 babel.config.js 中配置
    module.exports = {
      plugins: [
        ['import', {
          libraryName: 'ant-design-vue',
          libraryDirectory: 'lib',
          style: true
        }, 'antd']
      ]
    };
    
  3 引入组件
    // main.js
    import { Button as VanButton } from 'vant';
    Vue.component(VanButton.name, VanButton)
    
    // template
    <van-button>按钮</van-button>
    
```