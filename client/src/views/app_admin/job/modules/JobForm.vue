<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-04 22:35:16
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
      <a-form-model-item label="岗位名称" prop="name">
        <a-input v-model="record.name"></a-input>
      </a-form-model-item>

      <a-form-model-item label="状态" prop="status">
        <a-select v-model="record.status" placeholder="请选择启用或禁用" >
          <a-select-option value="0">禁用</a-select-option>
          <a-select-option value="1">启用</a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="描述" prop="description">
        <a-textarea v-model="record.description"></a-textarea>
      </a-form-model-item>
    </a-form-model>
  </a-modal>
</template>

<script>

export default {
  name: 'JobForm',
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
        name: [
          { required: true, message: '请输入名称', trigger: 'blur' }
        ],
        status: [{ required: true, message: '请选择启用或禁用', trigger: 'change' }]
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
