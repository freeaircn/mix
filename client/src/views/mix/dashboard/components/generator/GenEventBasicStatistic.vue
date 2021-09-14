<template>
  <div>
    <a-row :gutter="16">
      <a-col :sm="24" :md="12" :xl="12" :style="{ marginBottom: '8px' }">
        <a-card :loading="loading" title="运行时长（小时）" :bordered="false">
          <span slot="extra" >
            <a-tooltip slot="action">
              <template slot="title">
                截至：
                <p v-for="(item) in last_at" :key="item.gid">{{ item.gid }}：{{ item.last_at }}</p>
              </template>
              <a-icon type="info-circle-o" />
            </a-tooltip>
          </span>
          <div id="running-time-chart" :style="{height: '150px'}"></div>
        </a-card>
      </a-col>
      <a-col :sm="24" :md="12" :xl="12" :style="{ marginBottom: '8px' }">
        <a-card :loading="loading" title="开机次数" :bordered="false">
          <div id="start-num-chart" :style="{height: '150px'}"></div>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script>
import { Bar } from '@antv/g2plot'

export default {
  name: 'GenEventBasicStatistic',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    changed: {
      type: Boolean,
      default: false
    },
    statisticData: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      mountedDone: false,
      //
      runningTimeChart: null,
      startNumChart: null,
      last_at: []
    }
  },
  mounted () {
    // 运行时长
    this.initRunningTimeChart()
    // 开机次数
    this.initStartNumChart()
    //
    this.mountedDone = true
  },
  watch: {
    changed: {
      handler: function (val) {
        if (this.mountedDone) {
          this.updateRunningTimeChart()
          this.updateStartNumChart()
          this.updateLastAt()
        }
      },
      immediate: true
    }
  },
  methods: {

    initRunningTimeChart () {
      this.runningTimeChart = new Bar('running-time-chart', {
        data: this.statisticData,
        xField: 'running_time',
        yField: 'gid',
        seriesField: 'gid',
        legend: {
          position: 'top-left'
        },
        animation: false,
        maxBarWidth: 15,
        meta: {
          gid: {
            alias: '机组'
          },
          running_time: {
            alias: '时长'
          }
        }
      })
      this.runningTimeChart.render()
    },

    updateRunningTimeChart () {
      this.runningTimeChart.changeData(this.statisticData)
    },

    initStartNumChart () {
      this.startNumChart = new Bar('start-num-chart', {
        data: this.statisticData,
        xField: 'start_num',
        yField: 'gid',
        seriesField: 'gid',
        legend: {
          position: 'top-left'
        },
        animation: false,
        maxBarWidth: 15,
        meta: {
          gid: {
            alias: '机组'
          },
          start_num: {
            alias: '次数'
          }
        }
      })
      this.startNumChart.render()
    },

    updateStartNumChart () {
      this.startNumChart.changeData(this.statisticData)
    },

    updateLastAt () {
      this.last_at.splice(0, this.last_at.length)
      const len = this.statisticData.length
      for (let i = 0; i < len; i++) {
        let temp = {
          gid: this.statisticData[i].gid,
          last_at: this.statisticData[i].last_at
        }
        this.last_at.push(temp)
        temp = {}
      }
    }
  }
}
</script>
