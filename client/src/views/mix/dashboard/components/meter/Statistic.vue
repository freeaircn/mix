<!--
 * @Description:
 * @Author: freeair
 * @Date: 2022-04-30 23:24:45
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-01 01:23:55
-->
<template>
  <page-header-wrapper :title="false">

    <charts :style="{marginBottom: '16px'}"></charts>
    <overall :style="{marginBottom: '8px'}"></overall>

  </page-header-wrapper>
</template>

<script>
import { Charts, Overall } from './modules'

export default {
  name: 'MeterStatistic',
  components: {
    Charts,
    Overall
  }
}
</script>
