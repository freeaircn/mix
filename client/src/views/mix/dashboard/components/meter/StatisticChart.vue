<template>
  <div>
    <a-card title="统计" :bordered="false" :body-style="{marginBottom: '8px'}">
      <div :style="{marginBottom: '16px'}">
        <a-form-model ref="queryForm" layout="inline" :model="query" @submit.native.prevent>
          <a-form-model-item>
            <a-select v-model="query.date" style="width: 100px" >
              <a-select-option v-for="d in yearRange" :key="d.value" :value="d.value" >
                {{ d.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item>
            <a-button type="primary" @click="onGetChartData">查询</a-button>
          </a-form-model-item>
        </a-form-model>
      </div>

      <div :style="{marginBottom: '16px'}">{{ '截至：' + date }}</div>

      <div :style="{marginBottom: '32px'}">
        <a-row :gutter="[16, 16]" >
          <a-col :xl="4" :lg="4" :md="12" :sm="12" :xs="12">
            <a-statistic title="全年计划 / 万kWh" :value="yearPlan" :value-style="{ color: '#FF4500' }">
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="0" :sm="0" :xs="0">
            <a-statistic title="完成计划 / %" :value="yearPlanRate" :value-style="{ color: '#1AAF8B' }">
              <template #suffix>
                <span>(全年)</span>
              </template>
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="0" :sm="0" :xs="0">
            <a-statistic :value="monthPlanRate" :value-style="{ color: '#1AAF8B' }">
              <template #title>
                <span>
                  完成计划
                  <a-tooltip slot="action"><template slot="title">实发电量 / 计划发电量</template>
                    <a-icon type="info-circle-o" />
                  </a-tooltip>
                  / %
                </span>
              </template>
              <template #suffix>
                <span>(本月)</span>
              </template>
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="12" :sm="12" :xs="12">
            <a-statistic title="累计成交 / 万kWh" :value="yearDeal" :value-style="{ color: '#FF4500' }">
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="0" :sm="0" :xs="0">
            <a-statistic title="完成成交 / %" :value="yearDealRate" :value-style="{ color: '#1AAF8B' }">
              <template #suffix>
                <span>(全年)</span>
              </template>
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="0" :sm="0" :xs="0">
            <a-statistic :value="monthDealRate" :value-style="{ color: '#1AAF8B' }">
              <template #title>
                <span>
                  完成成交
                  <a-tooltip slot="action"><template slot="title">上网电量 / 成交电量</template>
                    <a-icon type="info-circle-o" />
                  </a-tooltip>
                  / %
                </span>
              </template>
              <template #suffix>
                <span>(本月)</span>
              </template>
            </a-statistic>
          </a-col>
        </a-row>
      </div>

      <div :style="{marginBottom: '32px'}">
        <a-radio-group v-model="chartType" default-value="a" button-style="solid">
          <a-radio-button value="a">
            季度
          </a-radio-button>
          <a-radio-button value="b">
            月度
          </a-radio-button>
          <a-radio-button value="c">
            近30天
          </a-radio-button>
        </a-radio-group>
      </div>

      <div>
        <div v-show="chartType == 'a'">
          <div id="quarters-chart" :style="{height: '430px'}"></div>
        </div>
        <div v-show="chartType == 'b'">
          <div id="months-chart" :style="{height: '430px'}"></div>
        </div>
        <div v-show="chartType == 'c'">
          <div id="days-chart" :style="{height: '430px'}"></div>
        </div>
      </div>
    </a-card>
  </div>
</template>

<script>
import moment from 'moment'
import { Column } from '@antv/g2plot'
import { getStatisticChartData } from '@/api/mix/meter'

const DataSet = require('@antv/data-set')

export default {
  name: 'StatisticChart',
  props: {
    stationId: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      query: {
        // moment YYYY
        date: null
      },
      yearRange: [],

      //
      isMounted: false,
      date: '',
      // 2021-12-14
      yearPlan: '0',
      yearPlanRate: '0',
      monthPlanRate: '0',
      yearDeal: '0',
      yearDealRate: '0',
      monthDealRate: '0',
      //
      chartType: 'a',
      daysData: [],
      monthsData: [],
      quartersData: [],
      //
      daysChart: null,
      monthsChart: null,
      quartersChart: null
    }
  },
  created () {
    const year = moment().year()
    for (let i = 2013; i <= year; i++) {
      this.yearRange.push({
        name: i + '年',
        value: i
      })
    }
    this.query.date = year
  },
  mounted () {
    // 2021-12-14
    this.initDaysChart()
    this.initMonthsChart()
    this.initQuartersChart()
    //
    this.isMounted = true
    //
    this.onGetChartData()
  },
  methods: {

    onGetChartData () {
      const query = {
        station_id: this.stationId,
        date: this.query.date
      }
      getStatisticChartData(query)
        .then(res => {
          this.date = res.date
          //
          this.yearPlan = res.year.plan
          this.yearPlanRate = res.rate.yearPlan
          this.monthPlanRate = res.rate.monthPlan
          this.yearDeal = res.year.deal
          this.yearDealRate = res.rate.yearDeal
          this.monthDealRate = res.rate.monthDeal
          //
          this.daysData = res.days
          this.monthsData = res.months
          this.quartersData = res.quarters
          //
          if (this.isMounted) {
            // 2021-12-14
            this.updateDaysChart()
            this.updateMonthsChart()
            this.updateQuartersChart()
          }
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    // 2021-12-14
    initDaysChart () {
      const dv = new DataSet.View().source(this.daysData)
      dv.transform({
        type: 'fold',
        fields: ['real', 'up'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.daysChart = new Column('days-chart', {
        data: chartData,
        xField: 'date',
        yField: 'kWh',
        seriesField: 'type',
        isStack: false,
        isGroup: true,
        point: {},
        legend: {
          position: 'bottom',
          itemName: {
            formatter: (text) => {
              let alias = ''
              if (text === 'real') alias = '实发'
              if (text === 'up') alias = '上网'
              return alias
            }
          }
        },
        slider: {
          start: 0,
          end: 1
          // trendCfg: {
          //   isArea: true
          // }
        },
        tooltip: {
          formatter: (item) => {
            let alias = ''
            if (item.type === 'real') alias = '实发'
            if (item.type === 'up') alias = '上网'
            return { name: alias, value: item.kWh }
          }
        }
      })

      this.daysChart.render()
    },

    updateDaysChart () {
      const dv = new DataSet.View().source(this.daysData)
      dv.transform({
        type: 'fold',
        fields: ['real', 'up'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.daysChart.changeData(chartData)
    },

    initMonthsChart () {
      const dv = new DataSet.View().source(this.monthsData)
      dv.transform({
        type: 'fold',
        fields: ['plan', 'deal', 'real', 'up', 'down'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.monthsChart = new Column('months-chart', {
        data: chartData,
        xField: 'month',
        yField: 'kWh',
        seriesField: 'type',
        isStack: false,
        isGroup: true,
        point: {},
        legend: {
          position: 'bottom',
          itemName: {
            formatter: (text) => {
              let alias = ''
              if (text === 'plan') alias = '计划'
              if (text === 'deal') alias = '成交'
              if (text === 'real') alias = '实发'
              if (text === 'up') alias = '上网'
              if (text === 'down') alias = '下网'
              return alias
            }
          }
        },
        slider: {
          start: 0,
          end: 1
          // trendCfg: {
          //   isArea: true
          // }
        },
        tooltip: {
          formatter: (item) => {
            let alias = ''
            if (item.type === 'plan') alias = '计划'
            if (item.type === 'deal') alias = '成交'
            if (item.type === 'real') alias = '实发'
            if (item.type === 'up') alias = '上网'
            if (item.type === 'down') alias = '下网'
            return { name: alias, value: item.kWh }
          },
          customItems: (originalItems) => {
            const cnt = originalItems.length
            if (cnt > 2) {
              const rate1 = Object.assign({}, originalItems[0])
              rate1.name = '完成计划'
              const rate2 = Object.assign({}, originalItems[1])
              rate2.name = '完成成交'
              let num11 = 0
              let num12 = 0
              let num21 = 0
              let num22 = 0
              for (let i = 0; i < cnt; i++) {
                if (originalItems[i].data.type === 'plan') {
                  num11 = originalItems[i].data.kWh
                }
                if (originalItems[i].data.type === 'real') {
                  num12 = originalItems[i].data.kWh
                }
                if (originalItems[i].data.type === 'deal') {
                  num21 = originalItems[i].data.kWh
                }
                if (originalItems[i].data.type === 'up') {
                  num22 = originalItems[i].data.kWh
                }
              }
              if (num11 > 0) {
                rate1.value = (num12 / num11 * 100).toPrecision(4) + '%'
              } else {
                rate1.value = '0.0%'
              }
              if (num21 > 0) {
                rate2.value = (num22 / num21 * 100).toPrecision(4) + '%'
              } else {
                rate2.value = '0.0%'
              }
              originalItems.push(rate1)
              originalItems.push(rate2)
            }

            return originalItems
          }
        }
      })

      this.monthsChart.render()
    },

    updateMonthsChart () {
      const dv = new DataSet.View().source(this.monthsData)
      dv.transform({
        type: 'fold',
        fields: ['plan', 'deal', 'real', 'up', 'down'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.monthsChart.changeData(chartData)
    },

    initQuartersChart () {
      const dv = new DataSet.View().source(this.quartersData)
      dv.transform({
        type: 'fold',
        fields: ['plan', 'deal', 'real', 'up'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.quartersChart = new Column('quarters-chart', {
        data: chartData,
        xField: 'quarter',
        yField: 'kWh',
        seriesField: 'type',
        isStack: false,
        isGroup: true,
        point: {},
        legend: {
          position: 'bottom',
          itemName: {
            formatter: (text) => {
              let alias = ''
              if (text === 'plan') alias = '计划'
              if (text === 'deal') alias = '成交'
              if (text === 'real') alias = '实发'
              if (text === 'up') alias = '上网'
              return alias
            }
          }
        },
        label: {
          // 可手动配置 label 数据标签位置
          position: 'middle', // 'top', 'middle', 'bottom'
          // 可配置附加的布局方法
          layout: [
            // 柱形图数据标签位置自动调整
            { type: 'interval-adjust-position' },
            // 数据标签防遮挡
            { type: 'interval-hide-overlap' },
            // 数据标签文颜色自动调整
            { type: 'adjust-color' }
          ]
        },
        slider: {
          start: 0,
          end: 1
          // trendCfg: {
          //   isArea: true
          // }
        },
        tooltip: {
          formatter: (item) => {
            let alias = ''
            if (item.type === 'plan') alias = '计划'
            if (item.type === 'deal') alias = '成交'
            if (item.type === 'real') alias = '实发'
            if (item.type === 'up') alias = '上网'
            return { name: alias, value: item.kWh }
          },
          customItems: (originalItems) => {
            const cnt = originalItems.length
            if (cnt > 2) {
              const rate1 = Object.assign({}, originalItems[0])
              rate1.name = '完成计划'
              const rate2 = Object.assign({}, originalItems[1])
              rate2.name = '完成成交'
              let num11 = 0
              let num12 = 0
              let num21 = 0
              let num22 = 0
              for (let i = 0; i < cnt; i++) {
                if (originalItems[i].data.type === 'plan') {
                  num11 = originalItems[i].data.kWh
                }
                if (originalItems[i].data.type === 'real') {
                  num12 = originalItems[i].data.kWh
                }
                if (originalItems[i].data.type === 'deal') {
                  num21 = originalItems[i].data.kWh
                }
                if (originalItems[i].data.type === 'up') {
                  num22 = originalItems[i].data.kWh
                }
              }
              if (num11 > 0) {
                rate1.value = (num12 / num11 * 100).toPrecision(4) + '%'
              } else {
                rate1.value = '0.0%'
              }
              if (num21 > 0) {
                rate2.value = (num22 / num21 * 100).toPrecision(4) + '%'
              } else {
                rate2.value = '0.0%'
              }
              originalItems.push(rate1)
              originalItems.push(rate2)
            }

            return originalItems
          }
        }
      })

      this.quartersChart.render()
    },

    updateQuartersChart () {
      const dv = new DataSet.View().source(this.quartersData)
      dv.transform({
        type: 'fold',
        fields: ['plan', 'deal', 'real', 'up'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.quartersChart.changeData(chartData)
    }
  }
}
</script>
