<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-08 21:26:54
-->
<template>
  <a-modal
    :title="title"
    :visible="modalVisible"
    :width="700"
    :centered="true"
    :maskClosable="false"
    @ok="handleOk"
    @change="handleVisibleChange"
  >
    <a-form-model
      ref="form"
      :model="record"
      :rules="rules"
      :label-col="labelCol"
      :wrapper-col="wrapperCol"
    >
      <a-form-model-item label="页面标题" prop="title">
        <a-input v-model="record.title"></a-input>
      </a-form-model-item>
      <a-form-model-item label="页面路由" prop="path">
        <a-input v-model="record.path"></a-input>
      </a-form-model-item>
      <a-form-model-item label="重定向" prop="redirect">
        <a-input v-model="record.redirect"></a-input>
      </a-form-model-item>
      <a-form-model-item label="路由名" prop="name">
        <a-input v-model="record.name"></a-input>
      </a-form-model-item>
      <a-form-model-item label="组件" prop="component">
        <a-input v-model="record.component"></a-input>
      </a-form-model-item>

      <a-form-model-item label="侧边栏隐藏" prop="hidden">
        <a-select v-model="record.hidden" placeholder="请选择" >
          <a-select-option value="0">否</a-select-option>
          <a-select-option value="1">是</a-select-option>
        </a-select>
      </a-form-model-item>
      <a-form-model-item label="强制菜单显示" prop="hideChildrenInMenu">
        <a-select v-model="record.hideChildrenInMenu" placeholder="请选择" >
          <a-select-option value="0">否</a-select-option>
          <a-select-option value="1">是</a-select-option>
        </a-select>
      </a-form-model-item>
      <a-form-model-item label="配合强制菜单显示" prop="meta_hidden">
        <a-select v-model="record.meta_hidden" placeholder="请选择" >
          <a-select-option value="0">否</a-select-option>
          <a-select-option value="1">是</a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="图标" prop="icon">
        <a-input v-model="record.icon"></a-input>
      </a-form-model-item>

      <a-form-model-item label="缓存路由" prop="keepAlive">
        <a-select v-model="record.keepAlive" placeholder="请选择" >
          <a-select-option value="0">否</a-select-option>
          <a-select-option value="1">是</a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="隐藏面包屑和页面标题栏" prop="hiddenHeaderContent">
        <a-select v-model="record.hiddenHeaderContent" placeholder="请选择" >
          <a-select-option value="0">否</a-select-option>
          <a-select-option value="1">是</a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="打开到新窗口" prop="target">
        <a-input v-model="record.target"></a-input>
      </a-form-model-item>

      <a-form-model-item label="上级菜单" prop="pid">
        <a-tree-select
          v-model="record.pid"
          style="width: 100%"
          :treeData="treeData"
          :replaceFields="{children:'children', title:'title', key:'id', value: 'id' }"
          :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
          allow-clear
          tree-default-expand-all
          :disabled="record.pid && record.pid === '0'"
        >
        </a-tree-select>
      </a-form-model-item>

    </a-form-model>
  </a-modal>
</template>

<script>

export default {
  name: 'Form',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    record: {
      type: Object,
      default: null
    },
    treeData: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      title: '新建',
      modalVisible: false,
      // pid: '',
      labelCol: {
        xs: { span: 24 },
        sm: { span: 7 }
      },
      wrapperCol: {
        xs: { span: 24 },
        sm: { span: 13 }
      },
      rules: {
        title: [{ required: true, message: '请输入标题', trigger: 'changed' }],
        path: [{ required: true, message: '请输入页面路由', trigger: 'changed' }]
      }
    }
  },
  watch: {
    visible: {
      handler: function (val) {
        this.title = this.record.id ? '修改' : '新建'
        this.modalVisible = val
      },
      immediate: true
    }
  },
  // mounted () {
  //   // this.record && this.form.setFieldsValue(pick(this.record, fields))
  //   this.form = Object.assign({}, this.record)
  // },
  methods: {
    handleOk () {
      this.$refs.form.validate(valid => {
          if (valid) {
            const res = Object.assign({}, this.record)
            this.$emit('res', res)
            this.modalVisible = false
            this.$emit('update:visible', false)
          }
        })
    },
    handleVisibleChange (visible) {
      this.$emit('update:visible', visible)
    }
  }
}
</script>
