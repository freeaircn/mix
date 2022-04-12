<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-12 20:01:35
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
      <a-form-model-item label="部门" prop="name">
        <a-input v-model="record.name" readOnly></a-input>
      </a-form-model-item>

      <a-form-model-item label="数据写权限" prop="data_writable">
        <a-select v-model="record.data_writable" placeholder="请选择" >
          <a-select-option value="0">否</a-select-option>
          <a-select-option value="1">是</a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="默认部门（多部门）" prop="is_default">
        <a-select v-model="record.is_default" placeholder="请选择" >
          <a-select-option value="0">否</a-select-option>
          <a-select-option value="1">是</a-select-option>
        </a-select>
      </a-form-model-item>

    </a-form-model>
  </a-modal>
</template>

<script>

export default {
  name: 'BlankForm2',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    record: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      title: '新建',
      modalVisible: false,
      labelCol: {
        xs: { span: 24 },
        sm: { span: 7 }
      },
      wrapperCol: {
        xs: { span: 24 },
        sm: { span: 13 }
      },
      rules: {
        data_writable: [{ required: true, message: '请选择', trigger: 'blur' }],
        is_default: [{ required: true, message: '请选择', trigger: 'blur' }]
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
