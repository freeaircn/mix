<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" :body-style="{marginBottom: '8px'}">
      <a-form-model ref="queryForm" layout="inline" :model="query" @submit.native.prevent>
        <a-form-model-item >
          <a-select v-model="query.station_id" placeholder="站点" style="width: 160px">
            <a-select-option v-for="d in userInfo.readDept" :key="d.id" :value="d.id">
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

    <a-row :gutter="[16, 16]" :style="{marginBottom: '0px'}">
      <a-col :xl="12" :lg="12" :md="24" :sm="24" :xs="24" >
        <!-- <chart-card title="新增 / 月" label="本月新增" :value="'3'" :loading="false"></chart-card> -->
        <chart-card title="新增 / 月" label="本月新增" :value="'3'" :loading="false"></chart-card>
      </a-col>
      <!-- <a-col :xl="8" :lg="8" :md="24" :sm="24" :xs="24" >
        <chart-card title="解决 / 月" label="本月解决" :value="'3'" :loading="false"></chart-card>
      </a-col> -->
    </a-row>

    <a-card :bordered="false" :body-style="{marginBottom: '8px'}">
      <a-row :gutter="[24, 8]">
        <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24" >
          <div id="top-chart" :style="{height: '320px'}"></div>
        </a-col>
        <a-col :xl="16" :lg="16" :md="16" :sm="24" :xs="24" >
          <div id="distribution-chart" :style="{height: '320px'}"></div>
        </a-col>
      </a-row>
    </a-card>

    <a-row :gutter="[8, 8]" :style="{marginBottom: '8px'}">
      <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
        <a-card title="缺陷" :bordered="false" >
          <a-row :gutter="[8, 8]">
            <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
              <div id="defect-level-chart" :style="{height: '240px'}"></div>
            </a-col>
            <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
              <div id="defect-wf-chart" :style="{height: '240px'}"></div>
            </a-col>
          </a-row>
        </a-card>
      </a-col>
      <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
        <a-card title="隐患" :bordered="false" >
          <a-row :gutter="[8, 8]">
            <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
              <div id="danger-level-chart" :style="{height: '240px'}"></div>
            </a-col>
            <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
              <div id="danger-wf-chart" :style="{height: '240px'}"></div>
            </a-col>
          </a-row>
        </a-card>
      </a-col>
    </a-row>

  </page-header-wrapper>
</template>

<script>
/* eslint-disable camelcase */
import { mapGetters } from 'vuex'
import { Pie, Column } from '@antv/g2plot'
import ChartCard from './modules/ChartCard'
// import { apiQueryEvent } from '@/api/mix/generator'
// const DataSet = require('@antv/data-set')

export default {
  name: 'StatisticChart',
  components: {
    ChartCard
  },
  props: {
    stationId: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      query: {
        station_id: 0
      },
      isMounted: false,
      //
      topChart: null,
      dataDangerAndDefect: [
        { type: '隐患', value: 2 },
        { type: '缺陷', value: 25 }
      ],
      //
      defectLevelChart: null,
      dataDefectLevel: [
        { type: '紧急', value: 1 },
        { type: '严重', value: 3 },
        { type: '一般', value: 21 }
      ],
      //
      defectWfChart: null,
      dataDefectWf: [
        { type: '处理中', value: 8 },
        { type: '挂起', value: 17 }
      ],
      //
      dangerLevelChart: null,
      dataDangerLevel: [
        { type: '紧急', value: 1 },
        { type: '严重', value: 1 },
        { type: '一般', value: 0 }
      ],
      //
      dangerWfChart: null,
      dataDangerWf: [
        { type: '处理中', value: 2 },
        { type: '挂起', value: 0 }
      ],
      //
      distributionChart: null,
      dataDistribution: [
        { type: '1#机组', place_at: '处理中', value: 2 },
        { type: '2#机组', place_at: '处理中', value: 2 },
        { type: '3#机组', place_at: '处理中', value: 2 },
        { type: '输电线路', place_at: '处理中', value: 2 },
        { type: 'GIS', place_at: '处理中', value: 2 },
        { type: '保护设备', place_at: '处理中', value: 6 },
        { type: '电力监控设备', place_at: '处理中', value: 6 },
        { type: '通信设备', place_at: '处理中', value: 7 },
        { type: '公用系统', place_at: '处理中', value: 4 },
        { type: '大坝', place_at: '处理中', value: 7 },

        { type: '1#机组', place_at: '挂起', value: 0 },
        { type: '2#机组', place_at: '挂起', value: 2 },
        { type: '3#机组', place_at: '挂起', value: 2 },
        { type: '输电线路', place_at: '挂起', value: 2 },
        { type: 'GIS', place_at: '挂起', value: 2 },
        { type: '保护设备', place_at: '挂起', value: 6 },
        { type: '电力监控设备', place_at: '挂起', value: 6 },
        { type: '通信设备', place_at: '挂起', value: 7 },
        { type: '公用系统', place_at: '挂起', value: 0 },
        { type: '大坝', place_at: '挂起', value: 7 }
      ]
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  mounted () {
    this.query.station_id = this.userInfo.allowDefaultDeptId
    this.initTopChart()
    this.initDefectLevelChart()
    this.initDefectWfChart()
    this.initDangerLevelChart()
    this.initDangerWfChart()
    this.initDistributionChart()
    //
    this.isMounted = true
    //
    this.onClickSearch()
  },
  methods: {
    onClickRecordIn () {
      this.$router.push({ path: '/dashboard/generator_event/list' })
    },

    onClickSearch () {
      // const params = { resource: 'statistic', station_id: this.query.station_id, date: this.query.date }
      // apiQueryEvent(params)
      //   .then(res => {
      //     this.run_time = res.run_time
      //     this.start_num = res.start_num
      //     //
      //     this.totalHours = res.total_run.total
      //     this.g1TotalRunTime = res.total_run.G1_run
      //     this.g2TotalRunTime = res.total_run.G2_run
      //     this.g3TotalRunTime = res.total_run.G3_run
      //     this.g1TotalStart = res.total_start.G1_start
      //     this.g2TotalStart = res.total_start.G2_start
      //     this.g3TotalStart = res.total_start.G3_start
      //     if (this.isMounted) {
      //       this.updateTopChart()
      //       this.updateDefectLevelChart()
      //       this.updateDefectWfChart()
      //       this.updateDangerLevelChart()
      //       this.updateDangerWfChart()
      //     }
      //   })
      //   .catch(() => {
      //   })
    },

    initTopChart () {
      this.topChart = new Pie('top-chart', {
        appendPadding: 10,
        data: this.dataDangerAndDefect,
        angleField: 'value',
        colorField: 'type',
        color: ({ type }) => {
          if (type === '缺陷') {
            return '#FF4500'
          }
          if (type === '隐患') {
            return '#B40F0F'
          }
        },
        legend: { position: 'bottom' },
        radius: 0.8,
        label: {
          type: 'outer',
          content: '{name} {percentage}'
        },
        interactions: [{ type: 'pie-legend-active' }, { type: 'element-active' }]
      })
      this.topChart.render()
    },

    updateTopChart () {
      const chartData = this.dataDangerAndDefect
      this.topChart.changeData(chartData)
    },

    initDistributionChart () {
      this.distributionChart = new Column('distribution-chart', {
        data: this.dataDistribution,
        isStack: true,
        xField: 'type',
        yField: 'value',
        seriesField: 'place_at',
        colorField: 'place_at', // 部分图表使用 seriesField
        color: ({ place_at }) => {
          if (place_at === '挂起') {
            return '#65789B'
          }
          if (place_at === '处理中') {
            return '#61DDAA'
          }
        },
        maxColumnWidth: 64,
        legend: { position: 'bottom' },
        label: {
          // 可手动配置 label 数据标签位置
          position: 'middle', // 'top', 'bottom', 'middle'
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
        xAxis: {
          // title: null,
          // label: null,
          // grid: null,
          // line: null
        },
        yAxis: {
          // title: null,
          // label: null
          // grid: null
          line: null
        }
      })
      this.distributionChart.render()
    },

    updateDistributionChart () {
      const chartData = this.dataDangerWf
      this.distributionChart.changeData(chartData)
    },

    //
    initDefectLevelChart () {
      this.defectLevelChart = new Pie('defect-level-chart', {
        appendPadding: 10,
        data: this.dataDefectLevel,
        angleField: 'value',
        legend: { position: 'bottom' },
        colorField: 'type',
        color: ({ type }) => {
          if (type === '紧急') {
            return '#FF6B3B'
          }
          if (type === '严重') {
            return '#F6BD16'
          }
          if (type === '一般') {
            return '#5B8FF9'
          }
        },
        radius: 0.8,
        label: {
          type: 'outer',
          content: '{name} {percentage}'
        },
        interactions: [{ type: 'pie-legend-active' }, { type: 'element-active' }]
      })
      this.defectLevelChart.render()
    },

    updateDefectLevelChart () {
      const chartData = this.dataDefectLevel
      this.defectLevelChart.changeData(chartData)
    },

    initDefectWfChart () {
      this.defectWfChart = new Pie('defect-wf-chart', {
        appendPadding: 10,
        data: this.dataDefectWf,
        angleField: 'value',
        legend: { position: 'bottom' },
        colorField: 'type',
        color: ({ type }) => {
          if (type === '处理中') {
            return '#61DDAA'
          }
          if (type === '挂起') {
            return '#65789B'
          }
        },
        radius: 0.8,
        label: {
          type: 'outer',
          content: '{name} {percentage}'
        },
        interactions: [{ type: 'pie-legend-active' }, { type: 'element-active' }]
      })
      this.defectWfChart.render()
    },

    updateDefectWfChart () {
      const chartData = this.dataDefectWf
      this.defectWfChart.changeData(chartData)
    },

    //
    initDangerLevelChart () {
      this.dangerLevelChart = new Pie('danger-level-chart', {
        appendPadding: 10,
        data: this.dataDangerLevel,
        angleField: 'value',
        legend: { position: 'bottom' },
        colorField: 'type',
        color: ({ type }) => {
          if (type === '紧急') {
            return '#FF6B3B'
          }
          if (type === '严重') {
            return '#F6BD16'
          }
          if (type === '一般') {
            return '#5B8FF9'
          }
        },
        radius: 0.8,
        label: {
          type: 'outer',
          content: '{name} {percentage}'
        },
        interactions: [{ type: 'pie-legend-active' }, { type: 'element-active' }]
      })
      this.dangerLevelChart.render()
    },

    updateDangerLevelChart () {
      const chartData = this.dataDangerLevel
      this.dangerLevelChart.changeData(chartData)
    },

    initDangerWfChart () {
      this.dangerWfChart = new Pie('danger-wf-chart', {
        appendPadding: 10,
        data: this.dataDangerWf,
        angleField: 'value',
        legend: { position: 'bottom' },
        colorField: 'type',
        color: ({ type }) => {
          if (type === '处理中') {
            return '#61DDAA'
          }
          if (type === '挂起') {
            return '#65789B'
          }
        },
        radius: 0.8,
        label: {
          type: 'outer',
          content: '{name} {percentage}'
        },
        interactions: [{ type: 'pie-legend-active' }, { type: 'element-active' }]
      })
      this.dangerWfChart.render()
    },

    updateDangerWfChart () {
      const chartData = this.dataDangerWf
      this.dangerWfChart.changeData(chartData)
    }

  }
}
</script>
