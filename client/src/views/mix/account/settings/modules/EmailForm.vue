<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-17 00:11:15
-->
<template>
  <a-modal
    title="修改邮箱"
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
      <a-form-model-item label="新邮箱&quot;" prop="email">
        <a-input v-model="record.email" placeholder="输入新邮箱" autocomplete="off"></a-input>
      </a-form-model-item>

      <a-form-model-item label="验证码" prop="code">
        <a-input v-model="record.code" placeholder="输入验证码">
          <a-button
            slot="addonAfter"
            class="getCaptcha"
            type="link"
            :disabled="state.smsSendBtn"
            @click.stop.prevent="getCaptcha"
            v-text="!state.smsSendBtn && '获取验证码'||(state.time+' s')"
          >
          </a-button>
        </a-input>
      </a-form-model-item>

      <a-form-model-item label="密码" prop="password">
        <a-input-password v-model="record.password" autocomplete="off" placeholder="输入登录密码"></a-input-password>
      </a-form-model-item>

    </a-form-model>
  </a-modal>
</template>

<script>
import { getSmsCaptcha } from '@/api/mix/account'
import * as pattern from '@/utils/validateRegex'

export default {
  name: 'EmailForm',
  props: {
    visible: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      // form: this.$form.createForm(this),
      record: {
        email: '',
        password: '',
        code: ''
      },
      state: {
        time: 60,
        level: 0,
        smsSendBtn: false
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
        email: [
          { required: true, message: '请输入邮箱地址', trigger: ['blur'] },
          { pattern: pattern.EMAIL.regex, message: pattern.EMAIL.msg, trigger: ['blur'] }
        ],
        code: [
          { required: true, message: '请输入验证码', trigger: ['blur'] },
          { pattern: pattern.SMS_CODE.regex, message: pattern.SMS_CODE.msg, trigger: ['blur'] }
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
    getCaptcha (e) {
      e.preventDefault()
      const { record, state, $message, $notification } = this

      this.$refs.form.validateField(['email'], (err) => {
        if (!err) {
          state.smsSendBtn = true
          const interval = window.setInterval(() => {
              if (state.time-- <= 0) {
                state.time = 60
                state.smsSendBtn = false
                window.clearInterval(interval)
              }
            }, 1000)

          const hide = $message.loading('验证码发送中..', 0)

          const data = { email: record.email }
          getSmsCaptcha(data)
            .then(res => {
              setTimeout(hide, 2500)
              $notification['success']({
                message: '提示',
                description: '验证码发送至邮箱：' + res.email,
                duration: 8
              })
            }).catch(err => {
              setTimeout(hide, 1)
              if (err.response) { }
            })
        }
      })
    },

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

<style lang="less" scoped>

    .getCaptcha {
      display: block;
      width: 100%;
      height: 30px;
    }

</style>
