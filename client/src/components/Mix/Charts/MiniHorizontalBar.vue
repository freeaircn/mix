<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-28 23:48:12
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-29 05:37:58
 padding="[36, 5, 18, 5]"
-->
<template>
  <div class="antv-chart-mini">
    <div class="chart-wrapper" :style="{ height: 46 }">
      <v-chart
        :forceFit="true"
        height="100"
        :data="data"
        :scale="scale"
        :padding="[0, 25, 25, 30]"
      >
        <v-tooltip></v-tooltip>
        <v-interval
          position="name*value"
          opacity="1"
          :label="barLabel"
          :size="12"
          :color="color"
        >
        </v-interval>
        <v-axis
          dataKey="name"
          :label="label"
          :tickLine="tickLine"
          :line="line"
        >
        </v-axis>
        <v-coord type="rect" direction="LT"></v-coord>
        <v-axis
          dataKey="value"
          :label="null"
          :title="title"
        >
        </v-axis>
      </v-chart>
    </div>
  </div>
</template>

<script>
const scale = [{
  dataKey: 'value',
  max: 100,
  min: 0,
  nice: false,
  alias: '次数'
}]

const barLabel = ['value', {
  textStyle: {
    fill: '#8d8d8d'
  },
  offset: 10
}]

const label = {
  textStyle: {
    fill: '#8d8d8d',
    fontSize: 12
  }
}

const tickLine = {
  alignWithLabel: false,
  length: 0
}

const line = {
  lineWidth: 0
}

const title = {
  offset: 30,
  textStyle: {
    fontSize: 12,
    fontWeight: 300
  }
}

const tooltip = [
  'x*y',
  (x, y) => ({
    name: x,
    value: y
  })
]

export default {
  name: 'MiniHorizontalBar',
  props: {
    data: {
      type: Array,
      default: () => []
    },
    color: {
      type: String,
      default: '#1890ff'
    }
  },
  data () {
    return {
      scale,
      barLabel,
      label,
      tickLine,
      line,
      title,
      tooltip
    }
  },
  watch: {
    data: {
      handler: function (val) {
        if (val.length > 0) {
          let max = val[0].value
          for (var i = 0; i < val.length; i++) {
            if (val[i].value > max) {
              max = val[i].value // 最大值
            }
          }
          this.scale[0].max = max
        }
      },
      immediate: true
    }
  }
}
</script>

<style lang="less" scoped>
  @import "chart";
</style>
