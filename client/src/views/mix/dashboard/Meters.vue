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

    <div class="antd-pro-pages-dashboard-analysis-twoColLayout" :class="!isMobile && 'desktop'">
      <a-row :gutter="[8,8]" type="flex" :style="{ marginBottom: '8px' }">
        <a-col :xl="12" :lg="12" :md="24" :sm="24" :xs="24" >
          <a-card :bordered="false" :title="dailyListTitle" :style="{height: '100%'}">
            <a-button slot="extra" type="link" @click="onShowDailyStatisticList">刷新</a-button>
            <!-- <div class="extra-wrapper" slot="extra">
              <div class="extra-item">
                <a-button type="link" @click="onShowDailyStatisticList">刷新</a-button>
              </div>
            </div> -->
            <DailyStatisticList
              :loading="dailyListLoading"
              :date="dailyListDate"
              :totalPlan="dailyListTotalPlan"
              :totalDeal="dailyListTotalDeal"
              :dayFrkAllGens="dailyListDayFrk"
              :dayBrkAllGens="dailyListDayBrk"
              :listData="dailyListData"
            >
            </DailyStatisticList>
          </a-card>
        </a-col>
        <a-col :xl="12" :lg="12" :md="24" :sm="24" :xs="24">
          <a-card :bordered="false" title="历史记录" :style="{ height: '100%' }">
            <div>
              <!-- style="width: calc(100% - 240px);" -->
              <LogList
                :loading="logListLoading"
                :date="logListDate"
                :listData="logListData"
                :current.sync="logListPageIndex"
                :total="totalLogs"
                @paginationChange="onLogListPageChange"
                @query="onQueryMeterLog"
                @queryDetail="onQueryMetersLogDetail"
                @report="onReqDailyReport"
                @delete="onDeleteMeterLog"
              >
              </LogList>
            </div>
          </a-card>
        </a-col>
      </a-row>
    </div>

    <a-modal :title="newRecordDiagTitle" v-model="newRecordDiagVisible" :footer="null" :destroyOnClose="true">
      <RecordForm
        :stationId="userInfo.belongToDeptId"
        :creator="this.userInfo.username"
        @submitSuccess="onNewRecordSuccess"
      >
      </RecordForm>
    </a-modal>

    <a-modal :title="logDetailDiagTitle" v-model="logDetailDiagVisible">
      <template slot="footer">
        <a-button key="submit" type="primary" @click="() => {this.logDetailDiagVisible = false}">关闭</a-button>
      </template>
      <LogDetail
        :tab1Data="logDetailTab1Data"
        :tab2Data="logDetailTab2Data"
        :tab3Data="logDetailTab3Data"
        :tab4Data="logDetailTab4Data"
      >
      </LogDetail>
    </a-modal>

    <a-modal v-model="reportDiagVisible" title="简报" >
      <template slot="footer">
        <a-button key="submit" type="primary" @click="() => {this.reportDiagVisible = false}">关闭</a-button>
      </template>
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
      <!-- <a-card :title="'年统计图 （万kWh）'" :bordered="false" :body-style="{marginBottom: '8px'}">
        <a-button slot="extra" type="link" @click="onQueryBasicStatistic()">刷新</a-button>
        <BasicStatistic
          :loading="basicStatLoading"
          :changed="basicStatChanged"
          :date="basicStatDate"
          :statisticListData="basicStatListData"
          :thirtyDaysData="basicStat30DaysData"
          :monthlyData="basicStatMonthlyData"
          :quarterlyData="basicStatQuarterlyData"
        >
        </BasicStatistic>
      </a-card> -->

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
import { RecordForm, StatisticChart, OverallStatistic, LogList, BasicStatistic, DailyStatisticList, PlanAndDeal, LogDetail } from './components/meter'
import { mapGetters } from 'vuex'
import { getMeterLogs, getMetersLogDetail, getMetersDailyReport, getMetersBasicStatistic, delMeterLogs, getMetersOverallStatistic } from '@/api/service'
import { getDailyStatistic, getPlanAndDeal, updatePlanAndDealRecord } from '@/api/mix/meter'
import { baseMixin } from '@/store/app-mixin'

export default {
  name: 'Meters',
  mixins: [baseMixin],
  components: {
    RecordForm,
    StatisticChart,
    DailyStatisticList,
    PlanAndDeal,
    LogList,
    BasicStatistic,
    OverallStatistic,
    LogDetail
  },
  data () {
    return {
      // 单日数据统计显示 2021-11-08
      dailyListTitle: '截至',
      dailyListLoading: false,
      dailyListDate: '',
      dailyListTotalPlan: '0',
      dailyListTotalDeal: '0',
      dailyListDayFrk: '0',
      dailyListDayBrk: '0',
      dailyListData: [],

      // 计划&成交数据 2021-11-08
      planAndDealDiagVisible: false,
      planAndDealListLoading: false,
      queryDateOfPlanAndDeal: '',
      planAndDealListData: [],
      planAndDealTotalPlan: '0',
      planAndDealTotalDeal: '0',

      // 录入新电度表单对话框 2021-11-20
      newRecordDiagTitle: '录入电表读数',
      newRecordDiagVisible: false,

      // 记录显示
      logListLoading: false,
      logListPageIndex: 1,
      pageSize: 6,
      totalLogs: 0,
      logListDate: '',
      logListData: [],
      reportDiagVisible: false,
      reportContent: {
        daily1: '',
        daily2: '',
        weekly: ''
      },

      reportOf20ClockDiagVisible: false,
      dailyOf20Clock: '0',

      logDetailDiagTitle: '',
      logDetailDiagVisible: false,
      logDetailTab1Data: [],
      logDetailTab2Data: [],
      logDetailTab3Data: [],
      logDetailTab4Data: [],

      // Basic统计显示
      basicStatLoading: false,
      basicStatChanged: false,
      basicStatDate: '',
      basicStatListData: [],
      basicStat30DaysData: [],
      basicStatMonthlyData: [],
      basicStatQuarterlyData: [],

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
    this.logListDate = moment().format('YYYY-MM-DD')
    this.queryDateOfPlanAndDeal = moment().format('YYYY-MM-DD')
  },
  mounted () {
    // 2021-11-08
    this.onShowDailyStatisticList()
    //
    this.onQueryMeterLog(this.logListDate)

    if (!this.isMobile) {
      // 统计图表
      this.onQueryBasicStatistic()

      // 全景统计
      this.onQueryOverallStatistic()
    }
  },
  methods: {
    // 单日数据统计 2021-11-08
    onShowDailyStatisticList () {
      const query = {
        station_id: this.userInfo.belongToDeptId
      }
      this.dailyListLoading = true
      getDailyStatistic(query)
        .then(response => {
          this.dailyListDate = response.date
          this.dailyListTotalPlan = response.totalPlan
          this.dailyListTotalDeal = response.totalDeal
          this.dailyListDayFrk = response.dayFrk
          this.dailyListDayBrk = response.dayBrk
          this.dailyListData = response.listData
          //
          this.dailyListLoading = false
          this.dailyListTitle = '截至 ' + this.dailyListDate
        })
        .catch((err) => {
          this.dailyListLoading = false
          if (err.response) {
          }
        })
    },

    // 2021-11-08
    onClickPlanAndDealDiag () {
      this.queryDateOfPlanAndDeal = moment().format('YYYY-MM-DD')
      this.onQueryPlanAndDeal(this.queryDateOfPlanAndDeal)
      this.planAndDealDiagVisible = true
    },

    // 录入表单 2021-11-20
    onClickNewRecord () {
      this.newRecordDiagVisible = true
    },

    onNewRecordSuccess () {
      this.newRecordDiagVisible = false
      this.onQueryMeterLog(this.logListDate)
      this.onShowDailyStatisticList()
    },

    // 记录列表显示
    onQueryMeterLog (date) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: date,
        type: 'month',
        limit: this.pageSize,
        offset: 1
      }
      this.logListPageIndex = 1
      this.logListLoading = true
      getMeterLogs(query)
        .then(res => {
          this.logListLoading = false
          //
          this.logListDate = date
          this.totalLogs = res.total
          this.logListData = res.data
        })
        .catch((err) => {
          this.logListLoading = false
          this.logListData.splice(0, this.logListData.length)
          if (err.response) {
          }
        })
    },

    onLogListPageChange (param) {
      const query = { ...param }
      query.station_id = this.userInfo.belongToDeptId
      query.date = this.logListDate
      query.type = 'month'

      this.logListLoading = true
      getMeterLogs(query)
        .then(res => {
          this.logListLoading = false
          //
          this.totalLogs = res.total
          this.logListData = res.data
        })
        .catch((err) => {
          this.logListLoading = false
          this.logListData.splice(0, this.logListData.length)
          if (err.response) {
          }
        })
    },

    onQueryMetersLogDetail (param) {
      this.logDetailTab1Data.splice(0, this.logDetailTab1Data.length)
      this.logDetailTab2Data.splice(0, this.logDetailTab2Data.length)
      this.logDetailTab3Data.splice(0, this.logDetailTab3Data.length)
      this.logDetailTab4Data.splice(0, this.logDetailTab4Data.length)
      //
      this.logDetailDiagTitle = param.log_date + ' ' + param.log_time
      this.logDetailDiagVisible = false
      getMetersLogDetail(param)
        .then((data) => {
          this.logDetailTab1Data = data.tab1Data
          this.logDetailTab2Data = data.tab2Data
          this.logDetailTab3Data = data.tab3Data
          this.logDetailTab4Data = data.tab4Data
          //
          this.logDetailDiagVisible = true
        })
        .catch((err) => {
          this.logDetailTab1Data.splice(0, this.logDetailTab1Data.length)
          this.logDetailTab2Data.splice(0, this.logDetailTab2Data.length)
          this.logDetailTab3Data.splice(0, this.logDetailTab3Data.length)
          this.logDetailTab4Data.splice(0, this.logDetailTab4Data.length)
          //
          this.logDetailDiagVisible = false
          if (err.response) {
          }
        })
    },

    // 查看电量单日简报
    onReqDailyReport (param) {
      const query = {
        station_id: param.station_id,
        log_date: param.log_date,
        log_time: param.log_time
      }
      this.reportDiagVisible = false
      this.reportContent.daily1 = ''
      this.reportContent.daily2 = ''
      this.reportContent.weekly = ''
      //
      this.reportOf20ClockDiagVisible = false
      this.dailyOf20Clock = '0'
      getMetersDailyReport(query)
        .then(res => {
          const data = res.data
          const type = res.type
          //
          if (type === '23') {
            this.reportDiagVisible = true
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

    onDeleteMeterLog (param) {
      delMeterLogs(param)
        .then(() => {
            this.onQueryMeterLog(this.logListDate)
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) {
              this.logListData.splice(0, this.logListData.length)
            }
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

    // 基本统计
    onQueryBasicStatistic () {
      const query = {
        station_id: this.userInfo.belongToDeptId
      }
      this.basicStatLoading = true
      getMetersBasicStatistic(query)
        .then(res => {
          this.basicStatDate = res.date
          this.basicStatListData = res.statisticList
          this.basicStat30DaysData = res.thirtyDaysData
          this.basicStatMonthlyData = res.monthData
          this.basicStatQuarterlyData = res.quarterData
          //
          this.basicStatLoading = false
          this.basicStatChanged = !this.basicStatChanged
        })
        .catch((err) => {
          this.basicStatLoading = false
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
      getMetersOverallStatistic(query)
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

  // .antd-pro-pages-dashboard-analysis-twoColLayout {
  //   position: relative;
  //   display: flex;
  //   display: block;
  //   flex-flow: row wrap;
  // }

  // .antd-pro-pages-dashboard-analysis-salesCard {
  //   height: calc(100% - 24px);
  //   /deep/ .ant-card-head {
  //     position: relative;
  //   }
  // }

</style>
