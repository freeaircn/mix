<template>
  <div>
    <div class="kwh-year-card-content">
      <div style="margin-bottom: 8px">
        <a-row :gutter="8" >
          <a-col :span="12">
            <a-statistic :title="year + '年计划 / 万kWh'" :value="totalPlan" :value-style="{ color: '#FF4500' }">
              <!-- <template #suffix>
                <span> / 万kWh</span>
              </template> -->
            </a-statistic>
          </a-col>
          <a-col :span="12">
            <a-statistic title="累计成交 / 万kWh" :value="totalDeal" :value-style="{ color: '#FF4500' }">
              <!-- <template #suffix>
                <span> / 万kWh</span>
              </template> -->
            </a-statistic>
          </a-col>
        </a-row>
      </div>

      <a-table
        ref="table"
        rowKey="id"
        :columns="columns"
        :data-source="listData"
        :loading="loading"
        :pagination="false"
      >
        <template slot="footer">
          <span style="">{{ '全机组单日无功（万kWh）正向 ' + dayFrkAllGens + '，反向 ' + dayBrkAllGens }}</span>
        </template>
      </a-table>

    </div>
  </div>
</template>

<script>

export default {
  name: 'MetersDailyStatisticList',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    date: {
      type: String,
      default: ''
    },
    totalPlan: {
      type: String,
      default: '0'
    },
    totalDeal: {
      type: String,
      default: '0'
    },
    dayFrkAllGens: {
      type: String,
      default: '0'
    },
    dayBrkAllGens: {
      type: String,
      default: '0'
    },
    listData: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      columns: [
        {
          title: '#',
          dataIndex: 'rowHeader'
        },
        {
          title: '发电量',
          dataIndex: 'generator'
        },
        {
          title: '完成计划',
          dataIndex: 'completePlanRate'
        },
        {
          title: '上网电量',
          dataIndex: 'line'
        },
        {
          title: '完成成交',
          dataIndex: 'completeDealRate'
        }
      ],
      year: '',
      mountedDone: false
    }
  },
  mounted () {
    //
    this.mountedDone = true
  },
  watch: {
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
  }
}
</script>
