<template>
  <div>
    <div class="kwh-year-card-content">
      <a-row :gutter="24" type="flex">
        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24" >
          <a-table
            ref="table"
            rowKey="id"
            :columns="columns"
            :data-source="statisticListData"
            :loading="loading"
            :pagination="false"
          >
            <template slot="title" >
              <span style="color: #303133;">{{ '截止' + date + '（万kWh）' }}</span>
            </template>
          </a-table>
        </a-col>

        <a-col :xl="16" :lg="24" :md="24" :sm="24" :xs="24" >
          <div style="height: 53px; padding: 16px 0px;" >
            <span style="color: #303133">
              <a-tooltip>
                <template slot="title">
                  {{ '截止' + date }}
                </template>
                {{ year }}年发电（万kWh）<a-icon type="info-circle" />
              </a-tooltip>
            </span>
            <span style="float:right">
              <a type="link" @click="handleChangeContent('30Days')">30天</a>
              <a-divider type="vertical" />
              <a type="link" @click="handleChangeContent('month')">月度</a>
              <a-divider type="vertical" />
              <a type="link" @click="handleChangeContent('quarter')">季度</a>
            </span>
          </div>

          <div v-show="chartName == 'month'">
            <div id="kwh-basic-month-chart" :style="{height: '430px'}"></div>
          </div>
          <div v-show="chartName == '30Days'">
            <div id="kwh-basic-days-chart" :style="{height: '430px'}"></div>
          </div>

          <div v-show="chartName == 'quarter'">
            <div id="kwh-basic-quarter-chart" :style="{height: '430px'}"></div>
          </div>
        </a-col>
      </a-row>
    </div>
  </div>
</template>

<script>

import { Area, Column } from '@antv/g2plot'

const DataSet = require('@antv/data-set')

export default {
  name: 'MetersBasicStatistic',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    changed: {
      type: Boolean,
      default: false
    },
    date: {
      type: String,
      default: ''
    },
    statisticListData: {
      type: Array,
      default: () => []
    },
    thirtyDaysData: {
      type: Array,
      default: () => []
    },
    monthlyData: {
      type: Array,
      default: () => []
    },
    quarterlyData: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      columns: [
        {
          title: '#',
          dataIndex: 'rowHeader'
        },
        {
          title: '上网电量',
          dataIndex: 'onGrid'
        },
        {
          title: '发电量',
          dataIndex: 'genEnergy'
        },
        {
          title: '完成计划',
          dataIndex: 'rate'
        }
      ],
      year: '',
      chartName: 'month',
      mountedDone: false,
      daysChart: null,
      monthlyChart: null,
      quarterlyChart: null
    }
  },
  mounted () {
    // 30天
    this.initThirtyDaysChart()
    // 月度
    this.initMonthlyChart()
    // 季度
    this.initQuarterlyChart()
    //
    this.mountedDone = true
  },
  watch: {
    changed: {
      handler: function (val) {
        if (this.mountedDone) {
          this.updateDaysChart()
          this.updateMonthlyChart()
          this.updateQuarterlyChart()
        }
      },
      immediate: true
    },
    date: {
      handler: function (val) {
        if (this.mountedDone) {
          this.year = val.substring(0, 4)
        }
      },
      immediate: true
    }
  },
  methods: {

    handleChangeContent (type) {
      this.chartName = type
    },

    initThirtyDaysChart () {
      this.daysChart = new Column('kwh-basic-days-chart', {
        data: this.thirtyDaysData,
        xField: 'date',
        yField: 'real',
        xAxis: {
          tickCount: 5
        },
        color: '#61DDAA',
        meta: {
          date: {
            alias: '日期'
          },
          real: {
            alias: '发电量'
          }
        },
        minColumnWidth: 5,
        maxColumnWidth: 20,
        animation: false,
        slider: {
          start: 0,
          end: 1
          // trendCfg: {
          //   isArea: true
          // }
        }
      })
      this.daysChart.render()
    },

    updateDaysChart () {
      this.daysChart.changeData(this.thirtyDaysData)
    },

    initMonthlyChart () {
      const dv = new DataSet.View().source(this.monthlyData)
      dv.transform({
        type: 'fold',
        fields: ['planning', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.monthlyChart = new Area('kwh-basic-month-chart', {
        data: chartData,
        xField: 'month',
        yField: 'kWh',
        seriesField: 'type',
        isStack: false,
        point: {},
        legend: {
          position: 'bottom',
          itemName: {
            formatter: (text) => {
              let alias = ''
              if (text === 'planning') alias = '计划'
              if (text === 'real') alias = '实际'
              return alias
            }
          }
        },
        tooltip: {
          formatter: (item) => {
            let alias = ''
            if (item.type === 'planning') alias = '计划'
            if (item.type === 'real') alias = '实际'
            return { name: alias, value: item.kWh }
          },
          customItems: (originalItems) => {
            if (originalItems.length === 2) {
              const rate = Object.assign({}, originalItems[1])
              rate.name = '完成率'
              if (originalItems[0].data.type === 'planning') {
                rate.value = (originalItems[1].data.kWh / originalItems[0].data.kWh * 100).toPrecision(4) + '%'
              } else {
                rate.value = (originalItems[0].data.kWh / originalItems[1].data.kWh * 100).toPrecision(4) + '%'
              }
              originalItems.push(rate)
            }

            return originalItems
          }
        }
      })

      this.monthlyChart.render()
    },

    updateMonthlyChart () {
      const dv = new DataSet.View().source(this.monthlyData)
      dv.transform({
        type: 'fold',
        fields: ['planning', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.monthlyChart.changeData(chartData)
    },

    initQuarterlyChart () {
      const dv = new DataSet.View().source(this.quarterlyData)
      dv.transform({
        type: 'fold',
        fields: ['planning', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.quarterlyChart = new Column('kwh-basic-quarter-chart', {
        data: chartData,
        isGroup: true,
        xField: 'quarter',
        yField: 'kWh',
        seriesField: 'type',
        legend: {
          position: 'bottom',
          itemName: {
            formatter: (text) => {
              let alias = ''
              if (text === 'planning') alias = '计划'
              if (text === 'real') alias = '实际'
              return alias
            }
          }
        },
        tooltip: {
          formatter: (item) => {
            let alias = ''
            if (item.type === 'planning') alias = '计划'
            if (item.type === 'real') alias = '实际'
            return { name: alias, value: item.kWh }
          },
          customItems: (originalItems) => {
            if (originalItems.length === 2) {
              const rate = Object.assign({}, originalItems[1])
              rate.name = '完成率'
              if (originalItems[0].data.type === 'planning') {
                rate.value = (originalItems[1].data.kWh / originalItems[0].data.kWh * 100).toPrecision(4) + '%'
              } else {
                rate.value = (originalItems[0].data.kWh / originalItems[1].data.kWh * 100).toPrecision(4) + '%'
              }
              originalItems.push(rate)
            }
            return originalItems
          }
        }
      })
      this.quarterlyChart.render()
    },

    updateQuarterlyChart () {
      const dv = new DataSet.View().source(this.quarterlyData)
      dv.transform({
        type: 'fold',
        fields: ['planning', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.quarterlyChart.changeData(chartData)
    }
  }
}
</script>
