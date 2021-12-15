<template>
  <page-header-wrapper :title="false">

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :bodyStyle="{marginBottom: '8px'}">
      <a-button style="margin-right: 16px" @click="onClickPlanAndDealDiag">计划&成交</a-button>
      <a-button type="primary" @click="onClickNewRecord">录入电度</a-button>
    </a-card>

    <a-modal title="计划&成交" v-model="planAndDealDiagVisible">
      <template slot="footer">
        <a-button key="submit" type="primary" @click="() => {this.planAndDealDiagVisible = false}">
          关闭
        </a-button>
      </template>
      <PlanAndDeal
        :loading="planAndDealListLoading"
        :date="queryDateOfPlanAndDeal"
        :listData="planAndDealListData"
        :totalPlan="planAndDealTotalPlan"
        :totalDeal="planAndDealTotalDeal"
        @query="onQueryPlanAndDeal"
        @update="onUpdatePlanAndDeal"
      >
      </PlanAndDeal>
    </a-modal>

    <div class="antd-pro-pages-dashboard-analysis-twoColLayout" :style="{ marginBottom: '8px' }">
      <a-card :bordered="false" title="电表记录" :style="{ height: '100%' }">
        <div>
          <RecordList
            :loading="recordListLoading"
            :date="queryRecordDate"
            :listData="recordListData"
            :current.sync="recordListPageIndex"
            :total="totalRecords"
            @paginationChange="onRecordListPageChange"
            @query="onQueryRecord"
            @update="onUpdateRecord"
            @delete="onDeleteRecord"
            @report="onReqDailyReport"
          >
          </RecordList>
        </div>
      </a-card>
    </div>

    <a-modal :title="recordDiagTitle" v-model="recordDiagVisible" :footer="null" :destroyOnClose="true">
      <RecordForm
        :stationId="userInfo.belongToDeptId"
        :creator="this.userInfo.username"
        :update="recordUpdate"
        :recordInfo="recordInfo"
        @submitSuccess="onRecordFormSuccess"
        @failure="onRecordFormFailure"
      >
      </RecordForm>
    </a-modal>

    <a-modal v-model="reportDiagVisible" title="简报" >
      <template slot="footer">
        <a-button key="submit" type="primary" @click="() => {this.reportDiagVisible = false}">关闭</a-button>
      </template>
      <a-card size="small" title="其他" style="margin-bottom: 8px">
        <p>{{ reportContent.extra1 }}</p>
        <p>{{ reportContent.extra2 }}</p>
      </a-card>
      <a-card size="small" title="今日1" style="margin-bottom: 8px">
        <a-button slot="extra" type="link" @click="handleCopyReportContent('daily1')">复制</a-button>
        <p>{{ reportContent.daily1 }}</p>
      </a-card>
      <a-card size="small" title="今日2" style="margin-bottom: 8px">
        <a-button slot="extra" type="link" @click="handleCopyReportContent('daily2')">复制</a-button>
        <p>{{ reportContent.daily2 }}</p>
      </a-card>
      <a-card size="small" title="一周">
        <a-button slot="extra" type="link" @click="handleCopyReportContent('weekly')">复制</a-button>
        <p>{{ reportContent.weekly }}</p>
      </a-card>
    </a-modal>

    <a-modal v-model="reportOf20ClockDiagVisible" title="简报" >
      <template slot="footer">
        <a-button key="submit" type="primary" @click="() => {this.reportOf20ClockDiagVisible = false}">关闭</a-button>
      </template>
      <a-statistic title="20:00发电量" :value="dailyOf20Clock">
        <template #suffix>
          <span> / 万kWh</span>
        </template>
      </a-statistic>
    </a-modal>

    <StatisticChart
      :stationId="userInfo.belongToDeptId"
    >
    </StatisticChart>

    <div v-if="!isMobile">
      <a-card :loading="false" title="全景" :bordered="false" :body-style="{marginBottom: '8px'}">
        <div class="extra-wrapper" slot="extra">
          <div class="extra-item">
            <a-button type="link" @click="onQueryOverallStatistic">刷新</a-button>
          </div>
        </div>

        <OverallStatistic
          :loading="overallStatLoading"
          :changed="overallStatChanged"
          :total="overallStatTotal"
          :yearData="overallStatYearData"
          :monthData="overallStatMonthData"
        >
        </OverallStatistic>

      </a-card>
    </div>

  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
import { BigNumber } from 'bignumber.js'
// import { deepMerge } from '@/utils/util'
import { RecordForm, StatisticChart, OverallStatistic, RecordList, PlanAndDeal } from './components/meter'
import { mapGetters } from 'vuex'
import { getRecord, delRecord, getPlanAndDeal, updatePlanAndDealRecord, getReportDaily, getStatisticOverall } from '@/api/mix/meter'
import { baseMixin } from '@/store/app-mixin'

export default {
  name: 'Meters',
  mixins: [baseMixin],
  components: {
    RecordForm,
    StatisticChart,
    PlanAndDeal,
    RecordList,
    OverallStatistic
  },
  data () {
    return {
      // 计划&成交数据 2021-11-08
      planAndDealDiagVisible: false,
      planAndDealListLoading: false,
      queryDateOfPlanAndDeal: '',
      planAndDealListData: [],
      planAndDealTotalPlan: '0',
      planAndDealTotalDeal: '0',

      // 录入新电度表单对话框 2021-11-20
      recordDiagTitle: '录入电表读数',
      recordDiagVisible: false,
      recordUpdate: false,
      recordInfo: {},

      // 记录显示
      recordListLoading: false,
      recordListPageIndex: 1,
      pageSize: 6,
      totalRecords: 0,
      queryRecordDate: '',
      recordListData: [],
      reportDiagVisible: false,
      reportContent: {
        extra1: '',
        extra2: '',
        daily1: '',
        daily2: '',
        weekly: ''
      },

      //
      reportOf20ClockDiagVisible: false,
      dailyOf20Clock: '0',

      // 全景统计显示
      overallStatLoading: false,
      overallStatChanged: false,
      overallStatTotal: {
        onGridEnergy: 0,
        genEnergy: 0
      },
      overallStatYearData: [],
      overallStatMonthData: []
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {
    // 初值
    this.queryDateOfPlanAndDeal = moment().format('YYYY-MM-DD')
  },
  mounted () {
    // 2021-11-08
    this.onQueryRecord()

    if (!this.isMobile) {
      // 全景统计
      this.onQueryOverallStatistic()
    }
  },
  methods: {
    // 2021-11-08
    onClickPlanAndDealDiag () {
      this.queryDateOfPlanAndDeal = moment().format('YYYY-MM-DD')
      this.onQueryPlanAndDeal(this.queryDateOfPlanAndDeal)
      this.planAndDealDiagVisible = true
    },

    // 记录-增 start
    onClickNewRecord () {
      this.recordDiagVisible = true
    },

    onRecordFormSuccess (method) {
      this.recordDiagVisible = false
      if (method === 'post') {
        this.onQueryRecord()
      }
      if (method === 'put') {
        this.onQueryRecordAfterUpdate(this.queryRecordDate)
      }
    },

    onRecordFormFailure () {
      this.recordDiagVisible = false
    },
    // 记录-增 end

    // 记录-删
    onDeleteRecord (param) {
      delRecord(param)
        .then(() => {
            this.onQueryRecord()
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) {
              this.recordListData.splice(0, this.recordListData.length)
            }
          })
    },

    // 记录-查 start
    onQueryRecord (date = '') {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: date,
        limit: this.pageSize,
        offset: 1
      }
      this.recordListPageIndex = 1
      this.recordListLoading = true
      getRecord(query)
        .then(res => {
          this.recordListLoading = false
          //
          this.totalRecords = res.total
          this.queryRecordDate = res.date
          this.recordListData = res.data
        })
        .catch((err) => {
          this.recordListLoading = false
          this.recordListData.splice(0, this.recordListData.length)
          if (err.response) {
          }
        })
    },

    onQueryRecordAfterUpdate (date = '') {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: date,
        limit: this.pageSize,
        offset: this.recordListPageIndex
      }
      // this.recordListPageIndex = 1
      this.recordListLoading = true
      getRecord(query)
        .then(res => {
          this.recordListLoading = false
          //
          this.totalRecords = res.total
          // this.queryRecordDate = res.date
          this.recordListData = res.data
        })
        .catch((err) => {
          this.recordListLoading = false
          this.recordListData.splice(0, this.recordListData.length)
          if (err.response) {
          }
        })
    },

    onRecordListPageChange (param) {
      const query = { ...param }
      query.station_id = this.userInfo.belongToDeptId
      query.date = this.queryRecordDate

      this.recordListLoading = true
      getRecord(query)
        .then(res => {
          this.recordListLoading = false
          //
          this.totalRecords = res.total
          this.recordListData = res.data
        })
        .catch((err) => {
          this.recordListLoading = false
          this.recordListData.splice(0, this.recordListData.length)
          if (err.response) {
          }
        })
    },
    // 记录-查 end

    // 记录-改 start
    onUpdateRecord (record) {
      this.recordInfo = record
      this.recordDiagVisible = true
      this.recordUpdate = true
      setTimeout(() => { this.recordUpdate = false }, 500)
    },
    // 记录-改 end

    // 查看电量单日简报
    onReqDailyReport (param) {
      const query = {
        station_id: param.station_id,
        log_date: param.log_date,
        log_time: param.log_time
      }
      this.reportDiagVisible = false
      this.reportContent.extra1 = ''
      this.reportContent.extra2 = ''
      this.reportContent.daily1 = ''
      this.reportContent.daily2 = ''
      this.reportContent.weekly = ''
      //
      this.reportOf20ClockDiagVisible = false
      this.dailyOf20Clock = '0'
      getReportDaily(query)
        .then(res => {
          const data = res.data
          const type = res.type
          //
          if (type === '23') {
            this.reportDiagVisible = true
            this.reportContent.extra1 = data.extra1
            this.reportContent.extra2 = data.extra2
            this.reportContent.daily1 = data.daily1
            this.reportContent.daily2 = data.daily2
            this.reportContent.weekly = data.weekly
          }
          //
          if (type === '20') {
            this.reportOf20ClockDiagVisible = true
            this.dailyOf20Clock = data
          }
        })
        .catch((err) => {
          this.reportDiagVisible = false
          this.reportContent.extra1 = ''
          this.reportContent.extra2 = ''
          this.reportContent.daily1 = ''
          this.reportContent.daily2 = ''
          this.reportContent.weekly = ''
          //
          this.reportOf20ClockDiagVisible = false
          this.dailyOf20Clock = '0'
          if (err.response) {
          }
        })
    },

    handleCopyReportContent (type) {
      let text = ''
      if (type === 'daily1') {
        text = this.reportContent.daily1
      } else if (type === 'daily2') {
        text = this.reportContent.daily2
      } else if (type === 'weekly') {
        text = this.reportContent.weekly
      }
      this.$copyText(text).then(
        () => {
          this.$message.info('复制成功')
        },
        () => {
          this.$message.info('复制失败')
        })
    },

    // 查询计划 2021-11-08
    onQueryPlanAndDeal (date) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: date
      }
      this.planAndDealListLoading = true
      getPlanAndDeal(query)
        .then(res => {
          this.planAndDealListLoading = false
          //
          this.queryDateOfPlanAndDeal = date
          const temp = this.adaptPlanAndDealDisplay(res.data, 4)

          this.planAndDealListData = temp.listData
          this.planAndDealTotalPlan = temp.totalPlan
          this.planAndDealTotalDeal = temp.totalDeal
        })
        .catch((err) => {
          this.planAndDealListLoading = false
          this.planAndDealListData.splice(0, this.planAndDealListData.length)
          if (err.response) {
          }
        })
    },

    // 修改计划 2021-11-08
    onUpdatePlanAndDeal (record) {
      const data = { ...record }
      data.station_id = this.userInfo.belongToDeptId
      // 万kwh -> kwh
      data.planning = this.fractionToInteger(data.planning, 4)
      data.deal = this.fractionToInteger(data.deal, 4)
      updatePlanAndDealRecord(data)
        .then(() => {
          this.onQueryPlanAndDeal(this.queryDateOfPlanAndDeal)
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    // 全景
    onQueryOverallStatistic () {
      const query = {
        station_id: this.userInfo.belongToDeptId
      }
      this.overallStatLoading = true
      getStatisticOverall(query)
        .then((res) => {
          this.overallStatTotal = { ...res.total }
          this.overallStatYearData = res.yearData
          this.overallStatMonthData = res.monthData
          //
          this.overallStatLoading = false
          this.overallStatChanged = !this.overallStatChanged
          })
          .catch((err) => {
            this.overallStatLoading = false
            if (err.response) {
            }
          })
    },

    // 2021-11-08 DB: kwh -> Web: 万kwh
    adaptPlanAndDealDisplay (data, bits) {
      const temp = data
      let totalPlan = 0
      let totalDeal = 0
      for (let i = 0; i < temp.length; i++) {
        temp[i].month = data[i].month + '月'
        //
        totalPlan = totalPlan + parseInt(data[i].planning)
        totalDeal = totalDeal + parseInt(data[i].deal)
        //
        temp[i].planning = this.integerToFraction(data[i].planning, bits)
        temp[i].deal = this.integerToFraction(data[i].deal, bits)
      }

      const res = {
        totalPlan: this.integerToFraction(totalPlan, bits),
        totalDeal: this.integerToFraction(totalDeal, bits),
        listData: temp
      }

      return res
    },

    // 2021-11-08
    fractionToInteger (value, bits) {
      const str = new BigNumber(value).toFixed()
      // 截取小数部分 bits位
      const arr = str.split('.')
      let res = str
      if (arr.length === 2) {
        const frac = arr[1]
        res = arr[0] + '.' + frac.substr(0, bits)
      }
      // 乘以10的bits方
      const x = Math.pow(10, bits)
      const temp = new BigNumber(res).multipliedBy(x)

      return temp.toFixed()
    },

    // 2021-11-08
    integerToFraction (value, bits) {
      // 除以10的bits方
      const x = Math.pow(10, bits)
      const temp = new BigNumber(value).dividedBy(x)

      return temp.toFixed()
    }
  }
}
</script>

<style lang="less" scoped>
  .extra-wrapper {
    // line-height: 55px;
    padding-right: 8px;

    .extra-item {
      display: inline-block;
      margin-right: 8px;

      a {
        margin-left: 8px;
      }
    }
  }
</style>
