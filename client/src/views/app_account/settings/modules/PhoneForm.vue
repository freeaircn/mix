<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-20 23:44:36
-->
<template>
  <a-modal
    title="修改手机号"
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
      <a-form-model-item label="手机号" prop="phone">
        <a-input v-model="record.phone" autocomplete="off" placeholder="输入新手机号">
        </a-input>
      </a-form-model-item>

      <a-form-model-item label="密码" prop="password">
        <a-input-password v-model="record.password" autocomplete="off" placeholder="输入登录密码"></a-input-password>
      </a-form-model-item>

    </a-form-model>
  </a-modal>
</template>

<script>
import * as pattern from '@/utils/validateRegex'

export default {
  name: 'PhoneForm',
  props: {
    visible: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      record: {
        phone: '',
        password: ''
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
        phone: [
          { required: true, message: '请输入手机号', trigger: ['blur'] },
          { pattern: pattern.phone.regex, message: pattern.phone.msg, trigger: ['blur'] }
        ],
        password: [ { required: true, message: '请输入登录密码', trigger: ['blur'] } ]
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
    }
  }
}
</script>
