<template>
  <div>
    <a-card title="统计" :bordered="false" :body-style="{marginBottom: '8px'}">
      <!-- <a-button slot="extra" type="link" @click="switchChart('30Days')">30天</a-button>
      <a-button slot="extra" type="link" @click="switchChart('month')">月度</a-button>
      <a-button slot="extra" type="link" @click="switchChart('quarter')">季度</a-button>
      <a-button slot="extra" type="link" @click="onGetChartData">刷新</a-button> -->

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

      <div :style="{marginBottom: '16px'}">截至：2021-12-13</div>

      <div>
        <a-row :gutter="[16,16]" >
          <a-col :xl="4" :lg="4" :md="24" :sm="24" :xs="24">
            <a-statistic title="全年计划 / 万kWh" :value="123445.1234" :value-style="{ color: '#FF4500' }">
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="24" :sm="24" :xs="24">
            <a-statistic title="完成计划 / %" :value="99.12" :value-style="{ color: '#1AAF8B' }">
              <template #suffix>
                <span>(全年)</span>
              </template>
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="24" :sm="24" :xs="24">
            <a-statistic title="完成计划 / %" :value="99.12" :value-style="{ color: '#1AAF8B' }">
              <template #suffix>
                <span>(本月)</span>
              </template>
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="24" :sm="24" :xs="24">
            <a-statistic title="累计成交 / 万kWh" :value="100000.4567" :value-style="{ color: '#FF4500' }">
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="24" :sm="24" :xs="24">
            <a-statistic title="完成成交 / %" :value="99.12" :value-style="{ color: '#1AAF8B' }">
              <template #suffix>
                <span>(全年)</span>
              </template>
            </a-statistic>
          </a-col>
          <a-col :xl="4" :lg="4" :md="24" :sm="24" :xs="24">
            <a-statistic title="完成成交 / %" :value="99.12" :value-style="{ color: '#1AAF8B' }">
              <template #suffix>
                <span>(本月)</span>
              </template>
            </a-statistic>
          </a-col>
        </a-row>
      </div>

      <div v-show="false">
        <a-row :gutter="16" type="flex">
          <a-col :xl="12" :lg="12" :md="24" :sm="24" :xs="24" >
            <div style="height: 53px; padding: 16px 0px;" >
              <span style="color: #303133">发电量 (万kWh)</span>
            <!-- <span style="float:right; margin-right:16px">
              <a type="link" @click="switchGeneratorChart('30Days')">30天</a>
              <a-divider type="vertical" />
              <a type="link" @click="switchGeneratorChart('month')">月度</a>
              <a-divider type="vertical" />
              <a type="link" @click="switchGeneratorChart('quarter')">季度</a>
            </span> -->
            </div>

            <div v-show="generatorChartType == 'month'">
              <!-- <div id="generator-month-chart" :style="{height: '430px'}"></div> -->
            </div>
            <div v-show="generatorChartType == '30Days'">
              <!-- <div id="generator-days-chart" :style="{height: '430px'}"></div> -->
            </div>

            <div v-show="generatorChartType == 'quarter'">
              <!-- <div id="generator-quarter-chart" :style="{height: '430px'}"></div> -->
            </div>
          </a-col>

          <a-col :xl="12" :lg="12" :md="24" :sm="24" :xs="24" >
            <div style="height: 53px; padding: 16px 0px;" >
              <span style="color: #303133;">上网电量 (万kWh)</span>
            <!-- <span style="float:right; margin-right:16px">
              <a type="link" @click="switchLineChart('30Days')">30天</a>
              <a-divider type="vertical" />
              <a type="link" @click="switchLineChart('month')">月度</a>
              <a-divider type="vertical" />
              <a type="link" @click="switchLineChart('quarter')">季度</a>
            </span> -->
            </div>

            <div v-show="lineChartType == 'month'">
              <div id="line-month-chart" :style="{height: '430px'}"></div>
            </div>
            <div v-show="lineChartType == '30Days'">
              <div id="line-days-chart" :style="{height: '430px'}"></div>
            </div>

            <div v-show="lineChartType == 'quarter'">
              <div id="line-quarter-chart" :style="{height: '430px'}"></div>
            </div>
          </a-col>
        </a-row>
      </div>

      <div>
        <a-tabs type="card" default-active-key="2">
          <a-tab-pane key="1" tab="30天" force-render>
            <div id="generator-days-chart" :style="{height: '430px'}"></div>
          </a-tab-pane>
          <a-tab-pane key="2" tab="月度" force-render>
            <div id="generator-month-chart" :style="{height: '430px'}"></div>
          </a-tab-pane>
          <a-tab-pane key="3" tab="季度" force-render>
            <div id="generator-quarter-chart" :style="{height: '430px'}"></div>
          </a-tab-pane>
        </a-tabs>
      </div>
    </a-card>
  </div>
</template>

<script>
import moment from 'moment'
import { Area, Column } from '@antv/g2plot'
import { getStatisticChartData } from '@/api/mix/meter'

const DataSet = require('@antv/data-set')

// const yearRange = []

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
      //
      generatorChartType: 'month',
      lineChartType: 'month',
      //
      generator30DaysData: [],
      generator12MonthsData: [],
      generator4QuartersData: [],
      //
      generator30DaysChart: null,
      generator12MonthsChart: null,
      generator4QuartersChart: null,
      //
      line30DaysData: [],
      line12MonthsData: [],
      line4QuartersData: [],
      //
      line30DaysChart: null,
      line12MonthsChart: null,
      line4QuartersChart: null
    }
  },
  created () {
    const year = moment().year()
    for (let i = 2011; i <= year; i++) {
      this.yearRange.push({
        name: i + '年',
        value: i
      })
    }
    this.query.date = year
  },
  mounted () {
    // 发电机
    this.initGenerator30DaysChart()
    this.initGenerator12MonthChart()
    this.initGenerator4QuartersChart()
    //
    this.initLine30DaysChart()
    this.initLine12MonthChart()
    this.initLine4QuartersChart()
    //
    this.isMounted = true
    //
    this.onGetChartData()
  },
  methods: {

    onGetChartData () {
      const query = {
        station_id: this.stationId
      }
      getStatisticChartData(query)
        .then(res => {
          this.date = res.date.substring(0, 4)
          //
          this.generator30DaysData = res.generator30Days
          this.generator12MonthsData = res.generator12Months
          this.generator4QuartersData = res.generator4Quarters
          //
          this.line30DaysData = res.line30Days
          this.line12MonthsData = res.line12Months
          this.line4QuartersData = res.line4Quarters
          //
          if (this.isMounted) {
            this.updateGenerator30DaysChart()
            this.updateGenerator12MonthsChart()
            this.updateGenerator4QuartersChart()
            //
            this.updateLine30DaysChart()
            this.updateLine12MonthsChart()
            this.updateLine4QuartersChart()
          }
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    //
    switchChart (type) {
      this.generatorChartType = type
      this.lineChartType = type
    },

    switchGeneratorChart (type) {
      this.generatorChartType = type
    },

    switchLineChart (type) {
      this.lineChartType = type
    },

    initGenerator30DaysChart () {
      this.generator30DaysChart = new Column('generator-days-chart', {
        data: this.generator30DaysData,
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
      this.generator30DaysChart.render()
    },

    updateGenerator30DaysChart () {
      this.generator30DaysChart.changeData(this.generator30DaysData)
    },

    initGenerator12MonthChart () {
      const dv = new DataSet.View().source(this.generator12MonthsData)
      dv.transform({
        type: 'fold',
        fields: ['plan', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.generator12MonthsChart = new Area('generator-month-chart', {
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
              if (text === 'plan') alias = '计划'
              if (text === 'real') alias = '实际'
              return alias
            }
          }
        },
        tooltip: {
          formatter: (item) => {
            let alias = ''
            if (item.type === 'plan') alias = '计划'
            if (item.type === 'real') alias = '实际'
            return { name: alias, value: item.kWh }
          },
          customItems: (originalItems) => {
            if (originalItems.length === 2) {
              const rate = Object.assign({}, originalItems[1])
              rate.name = '完成率'
              if (originalItems[0].data.type === 'plan') {
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

      this.generator12MonthsChart.render()
    },

    updateGenerator12MonthsChart () {
      const dv = new DataSet.View().source(this.generator12MonthsData)
      dv.transform({
        type: 'fold',
        fields: ['plan', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.generator12MonthsChart.changeData(chartData)
    },

    initGenerator4QuartersChart () {
      const dv = new DataSet.View().source(this.generator4QuartersData)
      dv.transform({
        type: 'fold',
        fields: ['plan', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.generator4QuartersChart = new Column('generator-quarter-chart', {
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
              if (text === 'plan') alias = '计划'
              if (text === 'real') alias = '实际'
              return alias
            }
          }
        },
        tooltip: {
          formatter: (item) => {
            let alias = ''
            if (item.type === 'plan') alias = '计划'
            if (item.type === 'real') alias = '实际'
            return { name: alias, value: item.kWh }
          },
          customItems: (originalItems) => {
            if (originalItems.length === 2) {
              const rate = Object.assign({}, originalItems[1])
              rate.name = '完成率'
              if (originalItems[0].data.type === 'plan') {
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
      this.generator4QuartersChart.render()
    },

    updateGenerator4QuartersChart () {
      const dv = new DataSet.View().source(this.generator4QuartersData)
      dv.transform({
        type: 'fold',
        fields: ['plan', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.generator4QuartersChart.changeData(chartData)
    },

    // 上网
    initLine30DaysChart () {
      this.line30DaysChart = new Column('line-days-chart', {
        data: this.line30DaysData,
        xField: 'date',
        yField: 'real',
        xAxis: {
          tickCount: 5
        },
        color: '#6DC8EC',
        meta: {
          date: {
            alias: '日期'
          },
          real: {
            alias: '上网电量'
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
      this.line30DaysChart.render()
    },

    updateLine30DaysChart () {
      this.line30DaysChart.changeData(this.line30DaysData)
    },

    initLine12MonthChart () {
      const dv = new DataSet.View().source(this.line12MonthsData)
      dv.transform({
        type: 'fold',
        fields: ['deal', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.line12MonthsChart = new Area('line-month-chart', {
        data: chartData,
        xField: 'month',
        yField: 'kWh',
        seriesField: 'type',
        isStack: false,
        point: {},
        color: ['#F6903D', '#F6BD16'],
        legend: {
          position: 'bottom',
          itemName: {
            formatter: (text) => {
              let alias = ''
              if (text === 'deal') alias = '成交'
              if (text === 'real') alias = '实际'
              return alias
            }
          }
        },
        tooltip: {
          formatter: (item) => {
            let alias = ''
            if (item.type === 'deal') alias = '成交'
            if (item.type === 'real') alias = '实际'
            return { name: alias, value: item.kWh }
          },
          customItems: (originalItems) => {
            if (originalItems.length === 2) {
              const rate = Object.assign({}, originalItems[1])
              rate.name = '完成率'
              if (originalItems[0].data.type === 'deal') {
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

      this.line12MonthsChart.render()
    },

    updateLine12MonthsChart () {
      const dv = new DataSet.View().source(this.line12MonthsData)
      dv.transform({
        type: 'fold',
        fields: ['deal', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.line12MonthsChart.changeData(chartData)
    },

    initLine4QuartersChart () {
      const dv = new DataSet.View().source(this.line4QuartersData)
      dv.transform({
        type: 'fold',
        fields: ['deal', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.line4QuartersChart = new Column('line-quarter-chart', {
        data: chartData,
        isGroup: true,
        xField: 'quarter',
        yField: 'kWh',
        seriesField: 'type',
        color: ['#F6903D', '#F6BD16'],
        legend: {
          position: 'bottom',
          itemName: {
            formatter: (text) => {
              let alias = ''
              if (text === 'deal') alias = '成交'
              if (text === 'real') alias = '实际'
              return alias
            }
          }
        },
        tooltip: {
          formatter: (item) => {
            let alias = ''
            if (item.type === 'deal') alias = '成交'
            if (item.type === 'real') alias = '实际'
            return { name: alias, value: item.kWh }
          },
          customItems: (originalItems) => {
            if (originalItems.length === 2) {
              const rate = Object.assign({}, originalItems[1])
              rate.name = '完成率'
              if (originalItems[0].data.type === 'deal') {
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
      this.line4QuartersChart.render()
    },

    updateLine4QuartersChart () {
      const dv = new DataSet.View().source(this.line4QuartersData)
      dv.transform({
        type: 'fold',
        fields: ['deal', 'real'],
        key: 'type',
        value: 'kWh'
      })
      const chartData = dv.rows

      this.line4QuartersChart.changeData(chartData)
    }
  }
}
</script>
