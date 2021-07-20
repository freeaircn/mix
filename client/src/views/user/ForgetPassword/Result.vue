<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-18 15:44:10
-->
<template>
  <a-result
    :isSuccess="true"
    :content="false"
    :title="phone"
    :sub-title="description">

    <template #extra>
      <a-button size="large" style="margin-left: 8px" @click="goHomeHandle">返回登录页面</a-button>
    </template>

  </a-result>
</template>

<script>
export default {
  name: 'ChangePwdResult',
  data () {
    return {
      description: '账号登录密码已经修改，请使用新密码登录。',
      form: {}
    }
  },
  computed: {
    phone () {
      const v = this.form && this.form.phone || 'xxx'
      return `账号 ${v}`
    }
  },
  created () {
    this.form = this.$route.params
  },
  methods: {
    goHomeHandle () {
      this.$router.push({ name: 'login' })
    }
  }
}
</script>
