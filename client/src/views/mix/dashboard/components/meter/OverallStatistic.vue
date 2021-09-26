<template>
  <div>
    <div>
      <a-row :gutter="16" type="flex">
        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24" >
          <div style="margin: 12px 0px">
            <a-row :gutter="16" >
              <a-col :span="12">
                <a-statistic title="投产发电" :value="genEnergyTotal" :value-style="{ color: '#cf1322' }">
                  <template #suffix>
                    <span> / 万kWh</span>
                  </template>
                </a-statistic>
              </a-col>
              <a-col :span="12">
                <a-statistic title="投产上网" :value="onGridEnergyTotal" :value-style="{ color: '#cf1322' }">
                  <template #suffix>
                    <span> / 万kWh</span>
                  </template>
                </a-statistic>
              </a-col>
            </a-row>
          </div>

          <div style="margin-bottom: 16px">上网电量 / 年（万kWh）</div>
          <div id="kwh-overall-year-chart" :style="{height: '346px'}"></div>
        </a-col>

        <a-col :xl="16" :lg="24" :md="24" :sm="24" :xs="24" >
          <div style="margin-bottom: 16px">上网电量 / 月（万kWh）</div>
          <div id="kwh-overall-month-chart" :style="{height: '430px'}"></div>
        </a-col>
      </a-row>
    </div>
  </div>
</template>

<script>
// import { Column, Area } from '@antv/g2plot'
import { Column } from '@antv/g2plot'

export default {
  name: 'MetersOverallStatistic',
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
    total: {
      type: Object,
      default: () => {}
    },
    yearData: {
      type: Array,
      default: () => []
    },
    monthData: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      year: '',
      mountedDone: false,
      onGridEnergyTotal: 0,
      genEnergyTotal: 0,
      yearChart: null,
      monthChart: null
    }
  },
  mounted () {
    // 年
    this.initYearChart()
    // 月
    this.initMonthChart()
    //
    this.mountedDone = true
  },
  watch: {
    changed: {
      handler: function (val) {
        if (this.mountedDone) {
          this.updateTotal()
          this.updateYearChart()
          this.updateMonthChart()
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
    updateTotal () {
      this.genEnergyTotal = this.total.genEnergy
      this.onGridEnergyTotal = this.total.onGridEnergy
    },

    initYearChart () {
      this.yearChart = new Column('kwh-overall-year-chart', {
        data: this.yearData,
        xField: 'date',
        yField: 'value',
        xAxis: {
          tickCount: 5
        },
        color: '#7262FD',
        meta: {
          date: {
            alias: '日期'
          },
          value: {
            alias: '上网电量'
          }
        },
        animation: false,
        slider: {
          start: 0,
          end: 1,
          trendCfg: {
            isArea: true
          }
        },
        minColumnWidth: 5,
        maxColumnWidth: 25
      })
      this.yearChart.render()
    },

    updateYearChart () {
      this.yearChart.changeData(this.yearData)
    },

    initMonthChart () {
      this.monthChart = new Column('kwh-overall-month-chart', {
        data: this.monthData,
        xField: 'date',
        yField: 'value',
        xAxis: {
          tickCount: 5
        },
        color: '#78D3F8',
        meta: {
          date: {
            alias: '日期'
          },
          value: {
            alias: '上网电量'
          }
        },
        animation: false,
        slider: {
          start: 0,
          end: 1,
          trendCfg: {
            isArea: true
          }
        },
        maxColumnWidth: 25
      })
      this.monthChart.render()
    },

    updateMonthChart () {
      this.monthChart.changeData(this.monthData)
    }
  }
}
</script>
