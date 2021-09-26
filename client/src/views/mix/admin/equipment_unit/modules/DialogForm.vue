<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-26 21:34:06
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
      <a-form-model-item label="名称" prop="name">
        <a-input v-model="record.name"></a-input>
      </a-form-model-item>

      <a-form-model-item label="描述" prop="description">
        <a-textarea v-model="record.description"></a-textarea>
      </a-form-model-item>

      <a-form-model-item label="属于" prop="pid">
        <a-tree-select
          v-model="record.pid"
          style="width: 100%"
          :treeData="treeData"
          :replaceFields="{children:'children', title:'name', key:'id', value: 'id' }"
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
  name: 'EquipmentUnitForm',
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
      // record: {},
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
        name: [{ required: true, message: '请输入名称', trigger: 'blur' }]
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

  methods: {
    handleOk () {
      this.$refs.form.validate(valid => {
          if (valid) {
            this.$emit('submit', this.record)
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
