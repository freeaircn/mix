<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-28 23:48:12
 * @LastEditors: freeair
 * @LastEditTime: 2021-08-02 22:23:23
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

// const tooltip = [
//   'x*y',
//   (x, y) => ({
//     name: x,
//     value: y
//   })
// ]

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
    },
    scaleAlias: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      scale: [{
        dataKey: 'value',
        max: 100,
        min: 0,
        nice: false,
        alias: this.scaleAlias
      }],
      barLabel,
      label,
      tickLine,
      line,
      title
    }
  },
  watch: {
    data: {
      handler: function (val) {
        if (val.length > 0) {
          let max = val[0].value
          for (var i = 1; i < val.length; i++) {
            if (val[i].value > max) {
              max = val[i].value // 最大值
            }
          }
          this.scale[0].max = max + 1
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
