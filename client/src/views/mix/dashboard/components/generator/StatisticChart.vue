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
        <a-row :gutter="[64, 16]">
          <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
            <div :style="{ marginBottom: '16px', fontWeight: 'bold', color: '#F4664A' }">累计运行时长 / 小时</div>
            <div id="running-time-chart" :style="{height: '400px'}"></div>
          </a-col>
          <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24">
            <div :style="{ marginBottom: '16px', fontWeight: 'bold', color: '#F4664A' }">累计开机次数 / 次</div>
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
      chartData: [],
      last_at: [],
      //
      runningTimeChart: null,
      startNumChart: null
    }
  },
  created () {
    const year = moment().year()
    for (let i = 2018; i <= year; i++) {
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
          this.chartData = res
          if (this.isMounted) {
            this.updateRunningTimeChart()
            this.updateStartNumChart()
            this.updateLastAt()
          }
        })
        .catch((err) => {
          // this.chartData.splice(0, this.chartData.length)
          if (err.response) {
          }
        })
    },

    initRunningTimeChart () {
      this.runningTimeChart = new Column('running-time-chart', {
        data: this.chartData,
        xField: 'gid',
        yField: 'running_time',
        seriesField: 'gid',
        color: ['#025DF4', '#2391FF', '#78D3F8'],
        legend: {
          position: 'bottom'
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
        animation: false,
        maxColumnWidth: 60,
        // meta: {
        //   gid: {
        //     alias: '机组'
        //   },
        //   running_time: {
        //     alias: '时长'
        //   }
        // },
        tooltip: {
          customItems: (originalItems) => {
            const cnt = originalItems.length
            if (cnt === 1) {
              const dateInfo = Object.assign({}, originalItems[0])
              dateInfo.name = '截至'
              const cnt2 = this.last_at.length
              for (let i = 0; i < cnt2; i++) {
                if (originalItems[0].name === this.last_at[i].gid) {
                  dateInfo.value = this.last_at[i].date
                }
              }
              originalItems.push(dateInfo)
            }
            return originalItems
          }
        }
      })
      this.runningTimeChart.render()
    },

    updateRunningTimeChart () {
      this.runningTimeChart.changeData(this.chartData)
    },

    initStartNumChart () {
      this.startNumChart = new Column('start-num-chart', {
        data: this.chartData,
        xField: 'gid',
        yField: 'start_num',
        seriesField: 'gid',
        color: ['#FF6B3B', '#E19348', '#FFC100'],
        legend: {
          position: 'bottom'
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
        animation: false,
        maxColumnWidth: 60,
        // meta: {
        //   gid: {
        //     alias: '机组'
        //   },
        //   start_num: {
        //     alias: '次数'
        //   }
        // },
        tooltip: {
          customItems: (originalItems) => {
            const cnt = originalItems.length
            if (cnt === 1) {
              const dateInfo = Object.assign({}, originalItems[0])
              dateInfo.name = '截至'
              const cnt2 = this.last_at.length
              for (let i = 0; i < cnt2; i++) {
                if (originalItems[0].name === this.last_at[i].gid) {
                  dateInfo.value = this.last_at[i].date
                }
              }
              originalItems.push(dateInfo)
            }
            return originalItems
          }
        }
      })
      this.startNumChart.render()
    },

    updateStartNumChart () {
      this.startNumChart.changeData(this.chartData)
    },

    updateLastAt () {
      this.last_at.splice(0, this.last_at.length)
      const len = this.chartData.length
      for (let i = 0; i < len; i++) {
        let temp = {
          gid: this.chartData[i].gid,
          date: this.chartData[i].last_at.substr(0, 10)
        }
        this.last_at.push(temp)
        temp = {}
      }
    }
  }
}
</script>
