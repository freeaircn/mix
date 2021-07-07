# VUE ����Element UI��Vant UI���
---

---
### 1 Vant 2
```
  1 ��װ Vant 2
    npm i vant -S
    
  2 �Զ������������
    # babel-plugin-import ��һ�� babel ���
    npm i babel-plugin-import -D
    
    # ����ʹ�� babel7 ���û��������� babel.config.js ������
    module.exports = {
      plugins: [
        ['import', {
          libraryName: 'vant',
          libraryDirectory: 'es',
          style: true
        }, 'vant']
      ]
    };
    
  3 �������
    // main.js
    import { Button as VanButton } from 'vant';
    Vue.component(VanButton.name, VanButton)
    
    // template
    <van-button>��ť</van-button>
    
```

---
### 2 Antd
```
  1 ��װ Vant 2
    npm i --save ant-design-vue
    
    npm install less less-loader --save-dev
    
  2 �Զ������������
    
    # ����ʹ�� babel7 ���û��������� babel.config.js ������
    module.exports = {
      plugins: [
        ['import', {
          libraryName: 'ant-design-vue',
          libraryDirectory: 'lib',
          style: true
        }, 'antd']
      ]
    };
    
  3 �������
    // main.js
    import { Button as VanButton } from 'vant';
    Vue.component(VanButton.name, VanButton)
    
    // template
    <van-button>��ť</van-button>
    
```