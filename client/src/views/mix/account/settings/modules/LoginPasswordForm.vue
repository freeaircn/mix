<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-17 00:10:14
-->
<template>
  <a-modal
    title="修改登录密码"
    :visible="modalVisible"
    :width="600"
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
      <a-form-model-item label="当前密码" prop="password">
        <a-input-password v-model="record.password" autocomplete="off"></a-input-password>
      </a-form-model-item>

      <a-form-model-item label="新密码" prop="newPassword">
        <a-input-password v-model="record.newPassword" autocomplete="off"></a-input-password>
      </a-form-model-item>

      <a-form-model-item label="新密码确认" prop="newPassword2">
        <a-input-password v-model="record.newPassword2" autocomplete="off"></a-input-password>
      </a-form-model-item>

    </a-form-model>
  </a-modal>
</template>

<script>
import * as pattern from '@/utils/validateRegex'

export default {
  name: 'LoginPasswordForm',
  props: {
    visible: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      record: {
        password: '',
        newPassword: '',
        newPassword2: ''
      },
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
        password: [ { required: true, message: '请输入当前的密码', trigger: ['blur'] } ],
        newPassword: [
          { required: true, message: '请输入新密码', trigger: ['blur'] },
          { pattern: pattern.PASSWORD.regex, message: pattern.PASSWORD.msg, trigger: ['blur'] }
        ],
        newPassword2: [
            { required: true, message: '请再输入一次新密码', trigger: ['blur'] },
            { validator: this.validatePass2, trigger: ['blur'] }
          ]
      }
    }
  },
  watch: {
    visible: {
      handler: function (val) {
        this.modalVisible = val
      },
      immediate: true
    }
  },
  methods: {
    handleOk () {
      this.$refs.form.validate(valid => {
          if (valid) {
            const res = { ...this.record }
            this.$emit('res', res)
            this.modalVisible = false
            this.$emit('update:visible', false)
            this.$refs.form.resetFields()
          }
        })
    },

    handleVisibleChange (visible) {
      this.$emit('update:visible', visible)
      this.$refs.form.resetFields()
    },

    validatePass2 (rule, value, callback) {
      const password = this.record.newPassword
      if (value === undefined) {
        callback(new Error('请输入新密码'))
      }
      if (value && password && value.trim() !== password.trim()) {
        callback(new Error('与新密码不一致'))
      }
      callback()
    }
  }
}
</script>
