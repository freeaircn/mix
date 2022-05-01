<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" :body-style="{marginBottom: '8px'}">
      <a-form-model ref="queryForm" layout="inline" :model="query" @submit.native.prevent>
        <a-form-model-item >
          <a-select v-model="query.station_id" placeholder="站点" style="width: 160px">
            <a-select-option v-for="d in stationItems" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item>
          <a-select v-model="query.date" style="width: 100px" >
            <a-select-option v-for="d in yearRange" :key="d.value" :value="d.value" >
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item>
          <a-button type="primary" @click="onClickSearch">查询</a-button>
        </a-form-model-item>

        <a-form-model-item>
          <a-button type="primary" @click="onClickRecordIn">录入</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>

    <a-row :gutter="[8, 8]" :style="{marginBottom: '8px'}">
      <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
        <a-card title="累计运行时长 / 小时" :bordered="false" >
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
        </a-card>
      </a-col>
      <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
        <a-card title="累计开机次数 / 次" :bordered="false" >
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
        </a-card>
      </a-col>
    </a-row>
  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
import { mapGetters } from 'vuex'
import { Column } from '@antv/g2plot'
import { apiQueryEvent } from '@/api/mix/generator'

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
      stationItems: [],
      query: {
        station_id: 0,
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
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {
    const year = moment().year()
    for (let i = year; i >= 2017; i--) {
      this.yearRange.push({
        name: i + '年',
        value: i
      })
    }
    this.query.date = year
  },
  beforeMount () {
    this.prepareSearchFunc()
  },
  mounted () {
    this.query.station_id = this.userInfo.allowDefaultDeptId
    this.initRunningTimeChart()
    this.initStartNumChart()
    //
    this.isMounted = true
    //
    this.onClickSearch()
  },
  methods: {
    // 查询
    prepareSearchFunc () {
      const params = { resource: 'search_params' }
      apiQueryEvent(params)
        .then(res => {
          res.station.forEach(element => {
            this.stationItems.push(element)
          })
        })
        .catch(() => {
        })
    },

    onClickSearch () {
      const params = { resource: 'statistic', station_id: this.query.station_id, date: this.query.date }
      apiQueryEvent(params)
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
          if (this.isMounted) {
            this.updateRunningTimeChart()
            this.updateStartNumChart()
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
    },

    onClickRecordIn () {
      this.$router.push({ path: '/dashboard/generator_event/list' })
    }
  }
}
</script>
