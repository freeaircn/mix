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

    <a-row :gutter="[8, 8]">
      <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24" >
        <info-chart-card title="新增" label="本月新增" :info="curMonthCreateNum" :loading="false">
          <div id="create-trend-chart" :style="{height: '56px'}"></div>
          <template slot="footer">全年 {{ totalCreateNum }}</template>
        </info-chart-card>
      </a-col>
      <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24" >
        <info-chart-card title="解决" label="本月解决" :info="curMonthResolveNum" :loading="false">
          <div id="resolve-trend-chart" :style="{height: '56px'}"></div>
          <template slot="footer">全年 {{ totalResolveNum }}</template>
        </info-chart-card>
      </a-col>
      <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24" >
        <info-chart-card title="超长问题" label="当前比率" :info="longTermRate" :loading="false">
          <a-tooltip title="超过90天没有解决的问题" slot="action">
            <a-icon type="info-circle-o" />
          </a-tooltip>
          <div id="long-term-chart" :style="{height: '56px'}"></div>
          <template slot="footer">"处理中"超长问题 {{ longTermWorkingRate }}%，"挂起"超长问题 {{ longTermSuspendRate }}%</template>
        </info-chart-card>
      </a-col>
    </a-row>

    <a-card :loading="false" :bordered="false" :body-style="{padding: '0', marginTop: '8px', marginBottom: '8px'}">
      <a-row :gutter="[8, 8]">
        <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24" >
          <chart-card title="全部">
            <div id="all-types-chart" :style="{height: '320px'}"></div>
          </chart-card>
        </a-col>
        <a-col :xl="16" :lg="16" :md="16" :sm="24" :xs="24" >
          <chart-card title="分布">
            <a-tooltip :title="extraMsg" slot="action">
              <a-icon type="info-circle-o" />
            </a-tooltip>
            <div id="distribution-chart" :style="{height: '320px'}"></div>
          </chart-card>
        </a-col>
      </a-row>
    </a-card>

    <a-row :gutter="[8, 8]" :style="{marginBottom: '8px'}">
      <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24" >
        <a-card title="缺陷" :headStyle="{fontSize: '15px', color: '000000bf'}" :bordered="false" >
          <a-tooltip :title="extraMsg" slot="extra">
            <a-icon type="info-circle-o" />
          </a-tooltip>
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
        <a-card title="隐患" :headStyle="{fontSize: '15px', color: '000000bf'}" :bordered="false" >
          <a-tooltip :title="extraMsg" slot="extra">
            <a-icon type="info-circle-o" />
          </a-tooltip>
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

    <a-card :loading="false" :bordered="false" :body-style="{padding: '0', marginTop: '8px', marginBottom: '8px'}">
      <chart-card title="原因统计">
        <a-tooltip title="已解决问题的原因统计" slot="action">
          <a-icon type="info-circle-o" />
        </a-tooltip>
        <div id="cause-chart" :style="{height: '320px'}"></div>
      </chart-card>
    </a-card>

  </page-header-wrapper>
</template>

<script>
/* eslint-disable camelcase */
import moment from 'moment'
import { mapGetters } from 'vuex'
import { Pie, Column, Bullet, Bar } from '@antv/g2plot'
import InfoChartCard from './modules/InfoChartCard'
import ChartCard from './modules/ChartCard'
import { queryDts } from '@/api/mix/dts'

export default {
  name: 'StatisticChart',
  components: {
    InfoChartCard,
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
      extraMsg: "'挂起' 表示影响较小的隐患或缺陷，暂不处理的状态",
      //
      curMonthCreateNum: 0,
      totalCreateNum: 0,
      curMonthResolveNum: 0,
      totalResolveNum: 0,
      //
      createTrendChart: null,
      createTrendChartData: [],
      resolveTrendChart: null,
      resolveTrendChartData: [],
      longTermChart: null,
      longTermChartData: [],
      longTermRate: '0%',
      longTermWorkingRate: 0,
      longTermSuspendRate: 0,
      //
      allTypesChart: null,
      allTypesChartData: [],
      distributionChart: null,
      distributionChartData: [],
      //
      defectLevelChart: null,
      defectLevelChartData: [],
      //
      defectWfChart: null,
      defectWfChartData: [],
      //
      dangerLevelChart: null,
      dangerLevelChartData: [],
      //
      dangerWfChart: null,
      dangerWfChartData: [],
      //
      causeChart: null,
      causeChartData: []
      //   { cause: '机械故障', type: '全部', value: 5 },
      //   { cause: '电路故障', type: '全部', value: 3 },
      //   { cause: '软件异常', type: '全部', value: 2 },
      //   { cause: '老化失效', type: '全部', value: 10 },
      //   { cause: '人为原因', type: '全部', value: 0 },
      //   { cause: '不可抗力', type: '全部', value: 1 },
      //   { cause: '机械故障', type: '今年', value: 2 },
      //   { cause: '电路故障', type: '今年', value: 2 },
      //   { cause: '软件异常', type: '今年', value: 2 },
      //   { cause: '老化失效', type: '今年', value: 6 },
      //   { cause: '人为原因', type: '今年', value: 0 },
      //   { cause: '不可抗力', type: '今年', value: 0 }
      // ]
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  mounted () {
    this.query.station_id = this.userInfo.allowDefaultDeptId
    this.initCreateTrendChart()
    this.initResolveTrendChart()
    this.initLongTermChart()
    this.initAllTypesChart()
    this.initDistributionChart()
    this.initDefectLevelChart()
    this.initDefectWfChart()
    this.initDangerLevelChart()
    this.initDangerWfChart()
    this.initCauseChart()
    //
    this.isMounted = true
    //
    this.onClickSearch()
  },
  methods: {
    onClickRecordIn () {
      this.$router.push({ path: '/dashboard/dts/list' })
    },

    onClickSearch () {
      const params = { resource: 'statistic_chart', station_id: this.query.station_id }
      queryDts(params)
        .then(data => {
          this.createTrendChartData = data.createList
          this.resolveTrendChartData = data.resolveList
          this.longTermChartData = data.longTerm.chart
          this.longTermRate = data.longTerm.chart[0].measures[0] + '%'
          this.longTermWorkingRate = data.longTerm.workingRate
          this.longTermSuspendRate = data.longTerm.chart[0].measures[0] - data.longTerm.workingRate
          //
          this.allTypesChartData = data.allTypes
          this.distributionChartData = data.distribution
          //
          this.defectLevelChartData = data.defectLevel
          this.defectWfChartData = data.defectWf
          this.dangerLevelChartData = data.hiddenDangerLevel
          this.dangerWfChartData = data.hiddenDangerWf
          //
          this.causeChartData = data.cause
          //
          var curMonth = moment().format('YYYY-MM')
          var temp = 0
          this.createTrendChartData.forEach((item) => {
            if (item.date === curMonth) {
              if (item.value < 1) {
                this.curMonthCreateNum = 0
              } else {
                this.curMonthCreateNum = item.value
              }
            }
            if (item.value < 1) {
              temp = temp + 0
            } else {
              temp = temp + item.value
            }
          })
          this.totalCreateNum = temp
          temp = 0
          this.resolveTrendChartData.forEach((item) => {
            if (item.date === curMonth) {
              if (item.value < 1) {
                this.curMonthResolveNum = 0
              } else {
                this.curMonthResolveNum = item.value
              }
            }
            if (item.value < 1) {
              temp = temp + 0
            } else {
              temp = temp + item.value
            }
          })
          this.totalResolveNum = temp
          //
          if (this.isMounted) {
            this.updateCreateTrendChart()
            this.updateResolveTrendChart()
            this.updateLongTermChart()
            this.updateAllTypesChart()
            this.updateDistributionChart()
            this.updateDefectLevelChart()
            this.updateDefectWfChart()
            this.updateDangerLevelChart()
            this.updateDangerWfChart()
            this.updateCauseChart()
          }
        })
        .catch(() => {
        })
    },

    initCreateTrendChart () {
      this.createTrendChart = new Column('create-trend-chart', {
        data: this.createTrendChartData,
        xField: 'date',
        yField: 'value',
        maxColumnWidth: 64,
        legend: false,
        label: false,
        xAxis: {
          title: null,
          label: null,
          grid: null,
          line: null
        },
        yAxis: {
          title: null,
          label: null,
          grid: null,
          line: null
        },
        tooltip: {
          formatter: (tip) => {
            if (tip.value === 0.1) {
              return { name: '新增', value: 0 }
            } else {
              return { name: '新增', value: tip.value }
            }
          }
        }
      })
      this.createTrendChart.render()
    },

    updateCreateTrendChart () {
      const chartData = this.createTrendChartData
      this.createTrendChart.changeData(chartData)
    },

    initResolveTrendChart () {
      this.resolveTrendChart = new Column('resolve-trend-chart', {
        data: this.resolveTrendChartData,
        xField: 'date',
        yField: 'value',
        maxColumnWidth: 64,
        legend: false,
        label: false,
        xAxis: {
          title: null,
          label: null,
          grid: null,
          line: null
        },
        yAxis: {
          title: null,
          label: null,
          grid: null,
          line: null
        },
        tooltip: {
          formatter: (tip) => {
            if (tip.value === 0.1) {
              return { name: '解决', value: 0 }
            } else {
              return { name: '解决', value: tip.value }
            }
          }
        }
      })
      this.resolveTrendChart.render()
    },

    updateResolveTrendChart () {
      const chartData = this.resolveTrendChartData
      this.resolveTrendChart.changeData(chartData)
    },

    initLongTermChart () {
      this.longTermChart = new Bullet('long-term-chart', {
        data: this.longTermChartData,
        measureField: 'measures',
        rangeField: 'ranges',
        targetField: 'target',
        xField: 'title',
        color: {
          range: ['#bfeec8', '#FFe0b0', '#FFbcb8'],
          measure: '#5B8FF9',
          target: '#39a3f4'
        },
        xAxis: {
          line: null
        },
        yAxis: false,
        label: {
          target: false
        },
        size: {
          range: 32,
          measure: 16,
          target: 40
        },
        tooltip: {
          formatter: (tip) => {
            if (tip.mKey && tip.mKey === 'measures') {
              return { name: '当前', value: tip.measures + '%' }
            }
            if (tip.tKey && tip.tKey === 'target') {
              return { name: '目标', value: tip.target + '%' }
            }
          }
        }
      })
      this.longTermChart.render()
    },

    updateLongTermChart () {
      const chartData = this.longTermChartData
      this.longTermChart.changeData(chartData)
    },

    initAllTypesChart () {
      this.allTypesChart = new Pie('all-types-chart', {
        appendPadding: 10,
        data: this.allTypesChartData,
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
      this.allTypesChart.render()
    },

    updateAllTypesChart () {
      const chartData = this.allTypesChartData
      this.allTypesChart.changeData(chartData)
    },

    initDistributionChart () {
      this.distributionChart = new Column('distribution-chart', {
        data: this.distributionChartData,
        isStack: true,
        xField: 'unit',
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
        // xAxis: {
        //   // title: null,
        //   // label: null,
        //   // grid: null,
        //   // line: null
        // },
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
      const chartData = this.distributionChartData
      this.distributionChart.changeData(chartData)
    },

    //
    initDefectLevelChart () {
      this.defectLevelChart = new Pie('defect-level-chart', {
        appendPadding: 10,
        data: this.defectLevelChartData,
        angleField: 'value',
        legend: { position: 'bottom' },
        colorField: 'level',
        color: ({ level }) => {
          if (level === '紧急') {
            return '#FF6B3B'
          }
          if (level === '严重') {
            return '#F6BD16'
          }
          if (level === '一般') {
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
      const chartData = this.defectLevelChartData
      this.defectLevelChart.changeData(chartData)
    },

    initDefectWfChart () {
      this.defectWfChart = new Pie('defect-wf-chart', {
        appendPadding: 10,
        data: this.defectWfChartData,
        angleField: 'value',
        legend: { position: 'bottom' },
        colorField: 'place_at',
        color: ({ place_at }) => {
          if (place_at === '处理中') {
            return '#61DDAA'
          }
          if (place_at === '挂起') {
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
      const chartData = this.defectWfChartData
      this.defectWfChart.changeData(chartData)
    },

    //
    initDangerLevelChart () {
      this.dangerLevelChart = new Pie('danger-level-chart', {
        appendPadding: 10,
        data: this.dangerLevelChartData,
        angleField: 'value',
        legend: { position: 'bottom' },
        colorField: 'level',
        color: ({ level }) => {
          if (level === '紧急') {
            return '#FF6B3B'
          }
          if (level === '严重') {
            return '#F6BD16'
          }
          if (level === '一般') {
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
      const chartData = this.dangerLevelChartData
      this.dangerLevelChart.changeData(chartData)
    },

    initDangerWfChart () {
      this.dangerWfChart = new Pie('danger-wf-chart', {
        appendPadding: 10,
        data: this.dangerWfChartData,
        angleField: 'value',
        legend: { position: 'bottom' },
        colorField: 'place_at',
        color: ({ place_at }) => {
          if (place_at === '处理中') {
            return '#61DDAA'
          }
          if (place_at === '挂起') {
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
      const chartData = this.dangerWfChartData
      this.dangerWfChart.changeData(chartData)
    },

    initCauseChart () {
      this.causeChart = new Bar('cause-chart', {
        data: this.causeChartData,
        isGroup: true,
        xField: 'value',
        yField: 'cause',
        seriesField: 'type',
        legend: {
          position: 'bottom'
        },
        barBackground: {
          style: {
            fill: 'rgba(0,0,0,0.1)'
          }
        },
        interactions: [{ type: 'active-region', enable: false }]
      })
      this.causeChart.render()
    },

    updateCauseChart () {
      const chartData = this.causeChartData
      this.causeChart.changeData(chartData)
    }

  }
}
</script>
