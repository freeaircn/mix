<template>
  <div>
    <a-card :bordered="false" :body-style="{marginBottom: '8px'}">
      <div :style="{marginBottom: '16px'}">
        <a-form-model ref="queryForm" layout="inline" :model="query">
          <a-form-model-item label="全景">
            <a-select v-model="query.station_id" placeholder="站点" style="width: 160px">
              <a-select-option v-for="d in userInfo.readDept" :key="d.id" :value="d.id">
                {{ d.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item>
            <a-button type="primary" @click="onClickSearch">查询</a-button>
          </a-form-model-item>
        </a-form-model>
      </div>
    </a-card>

    <a-row :gutter="[8, 8]">
      <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24" >
        <a-card :bordered="false" >
          <div>
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
          <div id="kwh-overall-year-chart" :style="{height: '370px'}"></div>
        </a-card>
      </a-col>

      <a-col :xl="16" :lg="24" :md="24" :sm="24" :xs="24" >
        <a-card :bordered="false" >
          <div style="margin-bottom: 16px">上网电量 / 月（万kWh）</div>
          <div id="kwh-overall-month-chart" :style="{height: '430px'}"></div>
        </a-card>
      </a-col>
    </a-row>

  </div>
</template>

<script>
import { Column } from '@antv/g2plot'
import { mapGetters } from 'vuex'
import { apiQueryMeter } from '@/api/mix/meter'

export default {
  name: 'Overall',
  data () {
    return {
      query: { station_id: '0' },
      total: {},
      yearData: [],
      monthData: [],
      //
      mountedDone: false,
      onGridEnergyTotal: 0,
      genEnergyTotal: 0,
      yearChart: null,
      monthChart: null
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  mounted () {
    this.query.station_id = this.userInfo.allowDefaultDeptId
    this.initYearChart()
    this.initMonthChart()
    this.mountedDone = true
    //
    this.onClickSearch()
  },
  methods: {

    onClickSearch () {
      const params = { resource: 'statistic_overall', ...this.query }
      apiQueryMeter(params)
        .then((res) => {
          this.total = { ...res.total }
          this.yearData = res.yearData
          this.monthData = res.monthData
          //
          if (this.mountedDone) {
            this.updateTotal()
            this.updateYearChart()
            this.updateMonthChart()
          }
        })
        .catch(() => {
        })
    },

    //
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
