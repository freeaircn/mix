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
                图表（万kWh）<a-icon type="info-circle" />
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
            <div id="month-chart" :style="{height: '430px'}"></div>
          </div>
          <div v-show="chartName == '30Days'">
            <div id="days-chart" :style="{height: '430px'}"></div>
          </div>

          <div v-show="chartName == 'quarter'">
            <div id="quarter-chart" :style="{height: '430px'}"></div>
          </div>
        </a-col>
      </a-row>
    </div>
  </div>
</template>

<script>
// import moment from 'moment'
// import { Area, Line, Column } from '@antv/g2plot'
import { Line, Column } from '@antv/g2plot'

const DataSet = require('@antv/data-set')

// const thirtyDaysDataTemp = [
//   { date: '2021-08-01', real: 100 },
//   { date: '2021-08-02', real: 200 },
//   { date: '2021-08-03', real: 300 },
//   { date: '2021-08-04', real: 400 },
//   { date: '2021-08-05', real: 500 },
//   { date: '2021-08-06', real: 400 },
//   { date: '2021-08-07', real: 300 },
//   { date: '2021-08-08', real: 200 },
//   { date: '2021-08-09', real: 100 }
// ]

// const monthDataTemp = [
//   { month: '1月', plan: 7.0, real: 3.9 },
//   { month: '2月', plan: 6.9, real: 4.2 },
//   { month: '3月', plan: 9.5, real: 5.7 },
//   { month: '4月', plan: 14.5, real: 8.5 },
//   { month: '5月', plan: 18.4, real: 11.9 },
//   { month: '6月', plan: 21.5, real: 15.2 },
//   { month: '7月', plan: 25.2, real: 17.0 },
//   { month: '8月', plan: 26.5, real: 16.6 },
//   { month: '9月', plan: 23.3, real: 0 },
//   { month: '10月', plan: 18.3, real: 0 },
//   { month: '11月', plan: 13.9, real: 0 },
//   { month: '12月', plan: 9.6, real: 0 }
// ]

// const quarterDataTemp = [
//   { quarter: '1季度', plan: 7.0, real: 3.9 },
//   { quarter: '2季度', plan: 6.9, real: 4.2 },
//   { quarter: '3季度', plan: 9.5, real: 5.7 },
//   { quarter: '4季度', plan: 14.5, real: 0 }
// ]

export default {
  name: 'KWhStatistic',
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
    }
  },
  methods: {

    handleChangeContent (type) {
      this.chartName = type
    },

    initThirtyDaysChart () {
      this.daysChart = new Column('days-chart', {
        data: this.thirtyDaysData,
        xField: 'date',
        yField: 'real',
        xAxis: {
          tickCount: 5
        },
        meta: {
          date: {
            alias: '日期'
          },
          real: {
            alias: '发电量'
          }
        },
        minColumnWidth: 20,
        maxColumnWidth: 20,
        animation: false,
        slider: {
          start: 0.7,
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

      this.monthlyChart = new Line('month-chart', {
        data: chartData,
        xField: 'month',
        yField: 'kWh',
        seriesField: 'type',
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
        // xAxis: {
        //   type: 'time',
        // },
        // yAxis: {
        //   label: {
        //     // 数值格式化为千分位
        //     formatter: (v) => `${v}`.replace(/\d{1,3}(?=(\d{3})+$)/g, (s) => `${s},`),
        //   },
        // },
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

      this.quarterlyChart = new Column('quarter-chart', {
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
