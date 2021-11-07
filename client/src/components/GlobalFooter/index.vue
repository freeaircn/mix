<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-11-06 13:12:42
-->
<template>
  <global-footer class="footer custom-render">
    <template v-slot:links>
      <!-- <a href="https://www.github.com/vueComponent/pro-layout" target="_blank">Pro Layout</a> -->
      <a href="#" >松山河口</a>
      <!-- <a href="https://www.github.com/sendya/" target="_blank">@Sendya</a> -->
    </template>
    <template v-slot:copyright>
      <a href="#" >Mix 2021.11.06</a>
    </template>
  </global-footer>
</template>

<script>
import { GlobalFooter } from '@ant-design-vue/pro-layout'

export default {
  name: 'ProGlobalFooter',
  components: {
    GlobalFooter
  }
}
</script>
