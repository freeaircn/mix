<template>
  <div class="main user-layout-register">
    <h3><span>忘记密码</span></h3>
    <a-form ref="form" :form="form" id="form">

      <a-form-item>
        <a-input size="large" :placeholder="$t('user.login.mobile.placeholder')" v-decorator="['mobile', {rules: [{ required: true, message: $t('user.phone-number.required'), pattern: /^1[3456789]\d{9}$/ } ], validateTrigger: ['blur'] }]">
        </a-input>
      </a-form-item>

      <a-row :gutter="16">
        <a-col class="gutter-row" :span="16">
          <a-form-item>
            <a-input size="large" type="text" :placeholder="$t('user.login.mobile.verification-code.placeholder')" v-decorator="['captcha', {rules: [{ required: true, message: '请输入验证码' }], validateTrigger: 'blur'}]">
              <a-icon slot="prefix" type="mail" :style="{ color: 'rgba(0,0,0,.25)' }"/>
            </a-input>
          </a-form-item>
        </a-col>
        <a-col class="gutter-row" :span="8">
          <a-button
            class="getCaptcha"
            size="large"
            :disabled="state.smsSendBtn"
            @click.stop.prevent="getCaptcha"
            v-text="!state.smsSendBtn && $t('user.register.get-verification-code')||(state.time+' s')"></a-button>
        </a-col>
      </a-row>

      <a-form-item>
        <a-input-password
          size="large"
          :placeholder="'输入至少8位密码'"
          v-decorator="['password', {rules: [{ required: true, message: '密码至少8位，含大小写字母和数字', pattern: /^[a-zA-Z0-9]{8,16}$/ }], validateTrigger: ['blur']}]"
        ></a-input-password>
      </a-form-item>

      <a-form-item>
        <a-input-password
          size="large"
          :placeholder="$t('user.register.confirm-password.placeholder')"
          v-decorator="['password2', {rules: [{ required: true, message: $t('user.password.required') }, { validator: this.handlePasswordCheck }], validateTrigger: ['blur']}]"
        ></a-input-password>
      </a-form-item>

      <a-form-item>
        <a-button
          size="large"
          type="primary"
          htmlType="submit"
          class="register-button"
          :loading="registerBtn"
          @click.stop.prevent="handleSubmit"
          :disabled="registerBtn">提 交
        </a-button>
        <router-link class="login" :to="{ name: 'login' }">使用已有账户登录</router-link>
      </a-form-item>
    </a-form>
    <div class="notice">1. 验证码发送至绑定的邮箱，不支持手机短信。</div>
    <div class="notice">2. 密码包含字母和数字，勿包含银行支付密码。</div>
  </div>
</template>

<script>
import md5 from 'md5'
import { getSmsCaptcha, resetPassword } from '@/api/login'
import { deviceMixin } from '@/store/device-mixin'
// import { scorePassword } from '@/utils/util'

export default {
  name: 'ForgetPassword',
  mixins: [deviceMixin],
  data () {
    return {
      form: this.$form.createForm(this),

      state: {
        time: 60,
        level: 0,
        smsSendBtn: false
      },
      registerBtn: false
    }
  },
  methods: {

    getCaptcha (e) {
      e.preventDefault()
      const { form: { validateFields }, state, $message, $notification } = this

      validateFields(['mobile'], { force: true },
        (err, values) => {
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

            getSmsCaptcha({ phone: values.mobile }).then(res => {
              setTimeout(hide, 2500)
              $notification['success']({
                message: '提示',
                description: '验证码发送至邮箱：' + res.email,
                duration: 8
              })
            }).catch(err => {
              setTimeout(hide, 1)
              this.$notification['error']({
                message: '错误',
                description: ((err.response || {}).data || {}).message || '请求出现错误，请稍后再试',
                duration: 4
              })
              // this.registerBtn = false
            })
          }
        }
      )
    },

    handlePasswordCheck (rule, value, callback) {
      const password = this.form.getFieldValue('password')
      if (value === undefined) {
        callback(new Error(this.$t('user.password.required')))
      }
      if (value && password && value.trim() !== password.trim()) {
        callback(new Error(this.$t('user.password.twice.msg')))
      }
      callback()
    },

    handleSubmit () {
      const { form: { validateFields }, $router } = this
      validateFields({ force: true }, (err, values) => {
        if (!err) {
          const reqData = {
            phone: values.mobile,
            code: values.captcha,
            password: md5(values.password)
          }
          resetPassword(reqData)
            .then(() => {
              // this.$router.replace({ path: '/login' })
              $router.push({ name: 'ChangePwdResult', params: { phone: values.mobile } })
            })
            .catch(err => {
              if (err.code) { }
            })
        }
      })
    }
  }
}
</script>
<style lang="less">
  .user-register {

    &.error {
      color: #ff0000;
    }

    &.warning {
      color: #ff7e05;
    }

    &.success {
      color: #52c41a;
    }

  }

  .user-layout-register {
    .ant-input-group-addon:first-child {
      background-color: #fff;
    }
  }
</style>
<style lang="less" scoped>
  .user-layout-register {

    & > h3 {
      font-size: 16px;
      margin-bottom: 20px;
    }

    .getCaptcha {
      display: block;
      width: 100%;
      height: 40px;
    }

    .register-button {
      width: 50%;
    }

    .login {
      float: right;
      line-height: 40px;
    }

    .notice {
      color: rgba(0, 0, 0, 0.45);
      font-size: 14px;
    }
  }
</style>
