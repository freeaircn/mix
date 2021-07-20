<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-18 21:12:55
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-20 19:09:57
-->
<template>
  <a-row>
    <a-col :md="24" :lg="12">
      <a-list
        itemLayout="horizontal"
        :dataSource="listData"
      >
        <a-list-item slot="renderItem" slot-scope="item, index" :key="index">
          <a-list-item-meta>
            <a slot="title">{{ item.title }}</a>
            <span slot="description">
              <span class="security-list-description">{{ item.description }}</span>
              <!-- <span v-if="item.value"> : </span> -->
              <span class="security-list-value">{{ item.value }}</span>
            </span>
          </a-list-item-meta>
          <template v-if="item.actions">
            <a slot="actions" @click="item.actions.callback">{{ item.actions.title }}</a>
          </template>

        </a-list-item>
      </a-list>
    </a-col>
    <a-col :md="24" :lg="12">
    </a-col>

    <login-password-form :visible.sync="loginPwdFormVisible" @res="handleUpdateLoginPassword"></login-password-form>
    <phone-form :visible.sync="phoneFormVisible" @res="handleUpdatePhone"></phone-form>
    <email-form :visible.sync="emailFormVisible" @res="handleUpdateEmail"></email-form>
  </a-row>
</template>

<script>
import md5 from 'md5'
import LoginPasswordForm from './modules/LoginPasswordForm'
import PhoneForm from './modules/PhoneForm'
import EmailForm from './modules/EmailForm'
import { mapGetters, mapActions } from 'vuex'

export default {
  components: {
    LoginPasswordForm,
    PhoneForm,
    EmailForm
  },
  data () {
    return {
      loginPwdFormVisible: false,
      phoneFormVisible: false,
      emailFormVisible: false
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ]),
    listData () {
        return [
        { title: '登录密码', description: '已设置', value: '', actions: { title: '修改', callback: this.handleModLoginPassword } },
        { title: '绑定手机', description: '已绑定手机：', value: this.userInfo.phone, actions: { title: '修改', callback: this.handleModPhone } },
        { title: '绑定邮箱', description: '已绑定邮箱：', value: this.userInfo.email, actions: { title: '修改', callback: this.handleModEmail } }
      ]
    }
  },
  methods: {
    ...mapActions(['UpdateUserLoginPassword', 'UpdateUserPhone', 'UpdateUserEmail']),

    handleModLoginPassword () {
      this.loginPwdFormVisible = true
    },
    handleUpdateLoginPassword (record) {
      const data = { password: md5(record.password), newPassword: md5(record.newPassword) }
      this.UpdateUserLoginPassword(data)
        .then(() => {

        })
        //  网络异常，清空页面数据显示，防止错误的操作
        .catch((err) => {
          if (err.response) { }
        })
    },

    handleModPhone () {
      this.phoneFormVisible = true
    },
    handleUpdatePhone (record) {
      const data = { password: md5(record.password), phone: record.phone }
      this.UpdateUserPhone(data)
        .then(() => {

        })
        //  网络异常，清空页面数据显示，防止错误的操作
        .catch((err) => {
          if (err.response) { }
        })
    },

    handleModEmail () {
      this.emailFormVisible = true
    },
    handleUpdateEmail (record) {
      const data = { password: md5(record.password), email: record.email, code: record.code }
      this.UpdateUserEmail(data)
        .then(() => {

        })
        //  网络异常，清空页面数据显示，防止错误的操作
        .catch((err) => {
          if (err.response) { }
        })
    }
  }
}
</script>

<style scoped>

</style>
