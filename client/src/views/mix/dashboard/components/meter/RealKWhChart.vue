<template>
  <div>
    <a-card :title="year + '年'" :loading="loading" :bordered="false" :body-style="{marginBottom: '8px'}">
      <div class="extra-wrapper" slot="extra">
        <div class="extra-item">
          <a-button type="link" @click="handleChangeContent('month')">月度</a-button>
          <a-button type="link" @click="handleChangeContent('quarter')">季度</a-button>
        </div>
      </div>
      <div class="kwh-year-card-content">
        <a-row :gutter="16" type="flex">
          <a-col :xl="16" :lg="24" :md="24" :sm="24" :xs="24" >
            <div v-show="contentName == 'month'">
              <div id="month-line" :style="{height: '300px'}"></div>
            </div>

            <div v-show="contentName == 'quarter'">
              <div id="quarter-bar" :style="{height: '300px'}"></div>
            </div>
          </a-col>

          <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24" >
            <a-table
              ref="table"
              rowKey="id"
              :columns="columns"
              :data-source="completionRate"
              :loading="loading"
              :pagination="false"
            >
              <span slot="serial" slot-scope="text, record, index">
                {{ index + 1 }}
              </span>
            </a-table>
          </a-col>
        </a-row>
      </div>
    </a-card>
  </div>
</template>

<script>
// import moment from 'moment'
import { Line, Column } from '@antv/g2plot'

const DataSet = require('@antv/data-set')

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '周期',
    dataIndex: 'period'
  },
  {
    title: '完成率',
    dataIndex: 'value'
  }
]

const monthDataTemp = [
  { month: '1月', plan: 7.0, real: 3.9 },
  { month: '2月', plan: 6.9, real: 4.2 },
  { month: '3月', plan: 9.5, real: 5.7 },
  { month: '4月', plan: 14.5, real: 8.5 },
  { month: '5月', plan: 18.4, real: 11.9 },
  { month: '6月', plan: 21.5, real: 15.2 },
  { month: '7月', plan: 25.2, real: 17.0 },
  { month: '8月', plan: 26.5, real: 16.6 },
  { month: '9月', plan: 23.3, real: 0 },
  { month: '10月', plan: 18.3, real: 0 },
  { month: '11月', plan: 13.9, real: 0 },
  { month: '12月', plan: 9.6, real: 0 }
]

const quarterDataTemp = [
  { quarter: '1季度', plan: 7.0, real: 3.9 },
  { quarter: '2季度', plan: 6.9, real: 4.2 },
  { quarter: '3季度', plan: 9.5, real: 5.7 },
  { quarter: '4季度', plan: 14.5, real: 0 }
]

export default {
  name: 'RealKWhChart',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    year: {
      type: String,
      default: ''
    },
    monthData: {
      type: Object,
      default: () => {}
    },
    quarterData: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    this.columns = columns
    return {
      contentName: 'month',
      completionRate: [
        { id: 1, period: '本月', value: '10%' },
        { id: 2, period: '本季度', value: '10%' },
        { id: 3, period: '全年', value: '10%' }
      ],
      //
      monthLineData: null,
      //
      quarterBarData: null
    }
  },
  mounted () {
    let dv = new DataSet.View().source(monthDataTemp)
    dv.transform({
      type: 'fold',
      fields: ['plan', 'real'],
      key: 'type',
      value: 'kWh'
    })
    this.monthLineData = dv.rows

    const monthLine = new Line('month-line', {
      data: this.monthLineData,
      xField: 'month',
      yField: 'kWh',
      seriesField: 'type',
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
      // xAxis: {
      //   type: 'time',
      // },
      // yAxis: {
      //   label: {
      //     // 数值格式化为千分位
      //     formatter: (v) => `${v}`.replace(/\d{1,3}(?=(\d{3})+$)/g, (s) => `${s},`),
      //   },
      // },
    })
    monthLine.render()

    //
    dv = new DataSet.View().source(quarterDataTemp)
    dv.transform({
      type: 'fold',
      fields: ['plan', 'real'],
      key: 'type',
      value: 'kWh'
    })
    this.quarterBarData = dv.rows

    const quarterBar = new Column('quarter-bar', {
      data: this.quarterBarData,
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
    quarterBar.render()
  },
  // watch: {
  //   current: {
  //     handler: function (val) {
  //       this.pagination.current = val
  //     },
  //     immediate: true
  //   }
  // },
  methods: {

    handleChangeContent (type) {
      // this.$emit('reqData', type)
      this.contentName = type
    }

    // handleTableChange (value) {
    //   this.pagination.current = value.current

    //   // 向父组件发消息，更新数据区
    //   const queryParam = {
    //     limit: this.pagination.pageSize,
    //     offset: this.pagination.current
    //   }
    //   this.$emit('update:current', value.current)
    //   this.$emit('reqData', queryParam)
    // },

    // handleEdit (record) {
    //   this.curRecord = { ...record }
    //   this.editModalVisible = true
    // },

    // handleEditOk () {
    //   this.editModalVisible = false
    //   const param = { ...this.curRecord }
    //   this.$emit('reqEdit', param)
    // },

    // // 删除请求
    // handleDel (record) {
    //   const param = {
    //     id: record.id,
    //     station_id: record.station_id,
    //     generator_id: record.generator_id,
    //     event: record.event
    //   }
    //   this.$confirm({
    //     title: '确定删除吗?',
    //     // content: '删除 ' + record.username,
    //     onOk: () => {
    //       this.$emit('reqDelete', param)
    //     }
    //   })
    // }
  }
}
</script>
