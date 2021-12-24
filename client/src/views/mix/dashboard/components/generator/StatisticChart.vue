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

      <div :style="{ marginBottom: '8px' }">
        <a-row :gutter="[32, 16]">
          <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
            <div :style="{ marginBottom: '16px', fontWeight: 'bold', color: '#F4664A' }">累计运行时长 / 小时</div>
            <div :style="{ marginBottom: '16px'}">
              <a-row :gutter="[16, 16]">
                <a-col :xl="8" :lg="8" :md="8" :sm="8" :xs="8" >
                  <a-statistic title="全年" :value="g1TotalRunTime" :value-style="{ color: '#FF745A' }">
                    <template #suffix>
                      <span> / G1</span>
                    </template>
                  </a-statistic>
                </a-col>
                <a-col :xl="8" :lg="8" :md="8" :sm="8" :xs="8" >
                  <a-statistic title="全年" :value="g2TotalRunTime" :value-style="{ color: '#FF745A' }">
                    <template #suffix>
                      <span> / G2</span>
                    </template>
                  </a-statistic>
                </a-col>
                <a-col :xl="8" :lg="8" :md="8" :sm="8" :xs="8" >
                  <a-statistic title="全年" :value="g3TotalRunTime" :value-style="{ color: '#FF745A' }">
                    <template #suffix>
                      <span> / G3</span>
                    </template>
                  </a-statistic>
                </a-col>
              </a-row>
            </div>
            <div id="running-time-chart" :style="{height: '400px'}"></div>
          </a-col>
          <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24">
            <div :style="{ marginBottom: '16px', fontWeight: 'bold', color: '#F4664A' }">累计开机次数 / 次</div>
            <div :style="{ marginBottom: '16px'}">
              <a-row :gutter="[16, 16]">
                <a-col :xl="8" :lg="8" :md="8" :sm="8" :xs="8" >
                  <a-statistic title="全年" :value="g1TotalStart" :value-style="{ color: '#21A97A' }">
                    <template #suffix>
                      <span> / G1</span>
                    </template>
                  </a-statistic>
                </a-col>
                <a-col :xl="8" :lg="8" :md="8" :sm="8" :xs="8" >
                  <a-statistic title="全年" :value="g2TotalStart" :value-style="{ color: '#21A97A' }">
                    <template #suffix>
                      <span> / G2</span>
                    </template>
                  </a-statistic>
                </a-col>
                <a-col :xl="8" :lg="8" :md="8" :sm="8" :xs="8" >
                  <a-statistic title="全年" :value="g3TotalStart" :value-style="{ color: '#21A97A' }">
                    <template #suffix>
                      <span> / G3</span>
                    </template>
                  </a-statistic>
                </a-col>
              </a-row>
            </div>
            <div id="start-num-chart" :style="{height: '400px'}"></div>
          </a-col>
        </a-row>
      </div>

    </a-card>
  </div>
</template>

<script>
import moment from 'moment'
import { Column } from '@antv/g2plot'
import { apiGetEventStatisticChartData } from '@/api/mix/generator'

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
        date: null
      },
      yearRange: [],
      isMounted: false,
      //
      run_time: [],
      start_num: [],
      // last_at: [],
      //
      runningTimeChart: null,
      startNumChart: null,
      //
      g1TotalRunTimeChart: null,
      g2TotalRunTimeChart: null,
      g3TotalRunTimeChart: null,

      totalHours: 0,
      g1TotalRunTime: 0,
      g2TotalRunTime: 0,
      g3TotalRunTime: 0,
      g1TotalStart: 0,
      g2TotalStart: 0,
      g3TotalStart: 0
      // g1TotalIdleTime: 0,
      // g2TotalIdleTime: 0,
      // g3TotalIdleTime: 0
    }
  },
  created () {
    const year = moment().year()
    for (let i = 2017; i <= year; i++) {
      this.yearRange.push({
        name: i + '年',
        value: i
      })
    }
    this.query.date = year
  },
  mounted () {
    this.initRunningTimeChart()
    this.initStartNumChart()
    // this.initG1TotalRunTimeChart()
    //
    this.isMounted = true
    //
    this.onGetChartData()
  },
  methods: {
    onGetChartData () {
      const query = {
        station_id: this.stationId,
        year: this.query.date
      }
      apiGetEventStatisticChartData(query)
        .then(res => {
          this.run_time = res.run_time
          this.start_num = res.start_num
          //
          this.totalHours = res.total_run.total
          this.g1TotalRunTime = res.total_run.G1_run
          this.g2TotalRunTime = res.total_run.G2_run
          this.g3TotalRunTime = res.total_run.G3_run
          this.g1TotalStart = res.total_start.G1_start
          this.g2TotalStart = res.total_start.G2_start
          this.g3TotalStart = res.total_start.G3_start
          // this.g1TotalIdleTime = res.total_run.G1_idle
          // this.g2TotalIdleTime = res.total_run.G2_idle
          // this.g3TotalIdleTime = res.total_run.G3_idle
          if (this.isMounted) {
            this.updateRunningTimeChart()
            this.updateStartNumChart()
            // this.updateLastAt()
            // this.updateG1TotalRunTimeChart()
          }
        })
        .catch((err) => {
          // this.chartData.splice(0, this.chartData.length)
          if (err.response) {
          }
        })
    },

    initRunningTimeChart () {
      const dv = new DataSet.View().source(this.run_time)
      dv.transform({
        type: 'fold',
        fields: ['G1', 'G2', 'G3'],
        key: 'type',
        value: 'time'
      })
      const chartData = dv.rows

      this.runningTimeChart = new Column('running-time-chart', {
        data: chartData,
        xField: 'month',
        yField: 'time',
        seriesField: 'type',
        isStack: false,
        isGroup: true,
        color: ['#E86452', '#FF9845', '#F6BD16'],
        legend: {
          position: 'bottom'
        },
        slider: {
          start: 0,
          end: 1
          // trendCfg: {
          //   isArea: true
          // }
        },
        animation: false,
        maxColumnWidth: 60
      })
      this.runningTimeChart.render()
    },

    updateRunningTimeChart () {
      const dv = new DataSet.View().source(this.run_time)
      dv.transform({
        type: 'fold',
        fields: ['G1', 'G2', 'G3'],
        key: 'type',
        value: 'time'
      })
      const chartData = dv.rows

      this.runningTimeChart.changeData(chartData)
    },

    initStartNumChart () {
      const dv = new DataSet.View().source(this.start_num)
      dv.transform({
        type: 'fold',
        fields: ['G1', 'G2', 'G3'],
        key: 'type',
        value: 'num'
      })
      const chartData = dv.rows

      this.startNumChart = new Column('start-num-chart', {
        data: chartData,
        xField: 'month',
        yField: 'num',
        seriesField: 'type',
        isStack: false,
        isGroup: true,
        color: ['#025DF4', '#2391FF', '#78D3F8'],
        legend: {
          position: 'bottom'
        },
        slider: {
          start: 0,
          end: 1
          // trendCfg: {
          //   isArea: true
          // }
        },
        animation: false,
        maxColumnWidth: 60
      })
      this.startNumChart.render()
    },

    updateStartNumChart () {
      const dv = new DataSet.View().source(this.start_num)
      dv.transform({
        type: 'fold',
        fields: ['G1', 'G2', 'G3'],
        key: 'type',
        value: 'num'
      })
      const chartData = dv.rows

      this.startNumChart.changeData(chartData)
    }

    // initG1TotalRunTimeChart () {
    //   this.g1TotalRunTimeChart = new Pie('G1-total-run-time-chart', {
    //     appendPadding: 10,
    //     data: [{ type: '运行', value: this.g1TotalRunTime }, { type: '停机', value: this.g1TotalIdleTime }],
    //     angleField: 'value',
    //     colorField: 'type',
    //     radius: 1,
    //     innerRadius: 0.64,
    //     meta: {
    //       value: {
    //         formatter: (v) => `${v}小时`
    //       }
    //     },
    //     label: {
    //       type: 'inner',
    //       offset: '-50%',
    //       autoRotate: false,
    //       style: { textAlign: 'center' },
    //       formatter: ({ percent }) => `${(percent * 100).toFixed(0)}%`
    //     },
    //     statistic: {
    //       title: {
    //         offsetY: -8
    //       },
    //       content: {
    //         offsetY: -4
    //       }
    //     },
    //     // 添加 中心统计文本 交互
    //     interactions: [
    //       { type: 'element-selected' },
    //       { type: 'element-active' },
    //       {
    //         type: 'pie-statistic-active',
    //         cfg: {
    //           start: [
    //             { trigger: 'element:mouseenter', action: 'pie-statistic:change' },
    //             { trigger: 'legend-item:mouseenter', action: 'pie-statistic:change' }
    //           ],
    //           end: [
    //             { trigger: 'element:mouseleave', action: 'pie-statistic:reset' },
    //             { trigger: 'legend-item:mouseleave', action: 'pie-statistic:reset' }
    //           ]
    //         }
    //       }
    //     ]
    //   })

    //   this.g1TotalRunTimeChart.render()
    // },

    // updateG1TotalRunTimeChart () {
    //   const chartData = [{ type: '运行', value: this.g1TotalRunTime }, { type: '停机', value: this.g1TotalIdleTime }]
    //   this.g1TotalRunTimeChart.changeData(chartData)
    // }

    // updateLastAt () {
    //   this.last_at.splice(0, this.last_at.length)
    //   const len = this.chartData.length
    //   for (let i = 0; i < len; i++) {
    //     let temp = {
    //       gid: this.chartData[i].gid,
    //       date: this.chartData[i].last_at.substr(0, 10)
    //     }
    //     this.last_at.push(temp)
    //     temp = {}
    //   }
    // }
  }
}
</script>
