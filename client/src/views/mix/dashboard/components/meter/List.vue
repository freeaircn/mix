<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" :body-style="{marginBottom: '8px'}">
      <div :style="{marginBottom: '8px'}">
        <a-form-model ref="queryForm" layout="inline" :model="query" @submit.native.prevent>
          <a-form-model-item >
            <a-select v-model="query.station_id" placeholder="站点" style="width: 160px">
              <a-select-option v-for="d in userInfo.readDept" :key="d.id" :value="d.id">
                {{ d.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item>
            <a-month-picker v-model="query.date" valueFormat="YYYY-MM-DD" />
          </a-form-model-item>

          <a-form-model-item>
            <a-button type="primary" @click="onClickSearch">查询</a-button>
          </a-form-model-item>

          <a-form-model-item>
            <a-button type="primary" @click="onClickNewRecord">录入电度</a-button>
          </a-form-model-item>
          <a-form-model-item>
            <a-button type="default" @click="onClickPlanAndDealDiag">计划&成交</a-button>
          </a-form-model-item>

        </a-form-model>
      </div>

      <a-modal :title="recordDiagTitle" v-model="recordDiagVisible" :footer="null" :destroyOnClose="true">
        <RecordForm
          :stationId="userInfo.allowDefaultDeptId"
          :stationItems="userInfo.readDept"
          :creator="this.userInfo.username"
          :update="recordUpdate"
          :recordInfo="recordInfo"
          @submitSuccess="onRecordFormSuccess"
          @failure="onRecordFormFailure"
        >
        </RecordForm>
      </a-modal>

      <a-table
        ref="table"
        rowKey="id"
        :columns="columns"
        :data-source="records"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
      >
        <span slot="serial" slot-scope="text, record, index">
          {{ index + 1 }}
        </span>
        <span slot="action" slot-scope="text, record">
          <template>
            <a @click="onClickReport(record)">简报</a>
            <a-divider type="vertical" />
            <a @click="onClickUpdate(record)">修改</a>
            <a-divider type="vertical" />
            <a @click="onClickDel(record)">删除</a>
          </template>
        </span>
      </a-table>

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
        <a @click="toggleAdvanced" style="margin-top: 8px; margin-bottom: 8px">
          {{ moreReport ? '收起' : '展开' }}
          <a-icon :type="moreReport ? 'up' : 'down'"/>
        </a>
        <template v-if="moreReport">
          <a-card size="small" title="一周" style="margin-top: 8px">
            <a-button slot="extra" type="link" @click="handleCopyReportContent('weekly')">复制</a-button>
            <p style="color: #FF745A">{{ reportContent.weekly }}</p>
          </a-card>
        </template>
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

      <a-modal title="计划&成交" v-model="planAndDealDiagVisible">
        <template slot="footer">
          <a-button key="submit" type="primary" @click="() => {this.planAndDealDiagVisible = false}">关闭</a-button>
        </template>
        <PlanAndDeal
          :loading="planAndDealListLoading"
          :date="queryDateOfPlanAndDeal"
          :stationId="userInfo.allowDefaultDeptId"
          :stationItems="userInfo.readDept"
          :listData="planAndDealListData"
          :totalPlan="planAndDealTotalPlan"
          :totalDeal="planAndDealTotalDeal"
          @query="onQueryPlanAndDeal"
          @update="onUpdatePlanAndDeal"
        >
        </PlanAndDeal>
      </a-modal>

    </a-card>
  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
import { BigNumber } from 'bignumber.js'
import { RecordForm, PlanAndDeal } from './modules'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { apiQueryMeter, delRecord, updatePlanAndDealRecord } from '@/api/mix/meter'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '日期',
    dataIndex: 'log_date'
  },
  {
    title: '时间',
    dataIndex: 'log_time'
  },
  {
    title: '记录人',
    dataIndex: 'creator'
  },
  {
    title: '操作',
    dataIndex: 'action',
    scopedSlots: { customRender: 'action' }
  }
]

export default {
  name: 'List',
  mixins: [baseMixin],
  components: {
    RecordForm,
    PlanAndDeal
  },
  data () {
    this.columns = columns
    return {
      query: {
        station_id: '0',
        date: ''
      },

      loading: false,
      records: [],

      pagination: {
        current: 1,
        pageSize: 10,
        total: 0
      },
      //
      recordDiagTitle: '录入电表读数',
      recordDiagVisible: false,
      recordUpdate: false,
      recordInfo: {},
      //
      reportDiagVisible: false,
      reportContent: {
        extra1: '',
        extra2: '',
        daily1: '',
        daily2: '',
        weekly: ''
      },
      moreReport: false,

      //
      reportOf20ClockDiagVisible: false,
      dailyOf20Clock: '0',
      //
      planAndDealDiagVisible: false,
      planAndDealListLoading: false,
      queryDateOfPlanAndDeal: '',
      planAndDealListData: [],
      planAndDealTotalPlan: '0',
      planAndDealTotalDeal: '0'
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  mounted () {
    this.query.station_id = this.userInfo.allowDefaultDeptId
    this.onClickSearch()
  },
  methods: {
    toggleAdvanced () {
      this.moreReport = !this.moreReport
    },

    onClickNewRecord () {
      this.recordDiagVisible = true
    },

    onRecordFormSuccess (method) {
      this.recordDiagVisible = false
      if (method === 'post') {
        this.onClickSearch()
      }
      if (method === 'put') {
        this.sendSearchReq(this.query)
      }
    },

    onRecordFormFailure () {
      this.recordDiagVisible = false
    },

    sendSearchReq (searchParams) {
      const params = Object.assign(searchParams, {
        resource: 'list',
        limit: this.pagination.pageSize,
        offset: this.pagination.current
      })
      this.loading = true
      apiQueryMeter(params)
        .then(res => {
          const pagination = { ...this.pagination }
          pagination.total = res.total
          this.pagination = pagination
          this.records = res.data
          this.query.date = res.date
          //
          this.loading = false
        })
        .catch(() => {
          this.loading = false
          this.query.date = moment().format('YYYY-MM-DD')
          this.records.splice(0, this.records.length)
        })
    },

    onClickSearch () {
      this.pagination.current = 1
      this.sendSearchReq(this.query)
    },

    // 分页
    handleTableChange (pagination) {
      const pager = { ...this.pagination }
      pager.current = pagination.current
      this.pagination = pager

      this.sendSearchReq(this.query)
    },

    onClickUpdate (record) {
      this.recordInfo = record
      this.recordDiagVisible = true
      this.recordUpdate = true
      setTimeout(() => { this.recordUpdate = false }, 500)
    },

    onClickDel (record) {
      const param = { ...record }
      this.$confirm({
        title: '确定删除吗?',
        onOk: () => {
          delRecord(param)
            .then(() => {
                this.onClickSearch()
              })
              .catch(() => {
                this.records.splice(0, this.records.length)
              })
        }
      })
    },

    // 查看简报，消息码report
    onClickReport (record) {
      const params = {
        resource: 'daily_report',
        station_id: record.station_id,
        log_date: record.log_date,
        log_time: record.log_time
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
      apiQueryMeter(params)
        .then(res => {
          const data = res.data
          const type = res.type
          //
          if (type === '23') {
            var weekDay = moment(record.log_date).format('E')
            if (weekDay === '4') {
              this.moreReport = true
            } else {
              this.moreReport = false
            }
            //
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
        .catch(() => {
          this.reportDiagVisible = false
          this.reportContent.extra1 = ''
          this.reportContent.extra2 = ''
          this.reportContent.daily1 = ''
          this.reportContent.daily2 = ''
          this.reportContent.weekly = ''
          //
          this.reportOf20ClockDiagVisible = false
          this.dailyOf20Clock = '0'
        })
    },

    onClickPlanAndDealDiag () {
      this.queryDateOfPlanAndDeal = moment().format('YYYY-MM-DD')
      this.onQueryPlanAndDeal(this.queryDateOfPlanAndDeal)
      this.planAndDealDiagVisible = true
    },

    onQueryPlanAndDeal (date) {
      const params = {
        resource: 'plan_and_deal',
        station_id: this.userInfo.allowDefaultDeptId,
        date: date
      }
      this.planAndDealListLoading = true
      apiQueryMeter(params)
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

    onUpdatePlanAndDeal (record) {
      const data = { ...record }
      data.station_id = this.userInfo.allowDefaultDeptId
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
    },

    handleCopyReportContent (type) {
      var copyData = ''
      switch (type) {
        case 'daily1':
          copyData = this.reportContent.daily1
          break
        case 'daily2':
          copyData = this.reportContent.daily2
          break
        case 'weekly':
          copyData = this.reportContent.weekly
          break

        default:
          return
      }
      this.$copyText(copyData).then(() => {
          this.$message.success('复制成功')
        }).catch(() => {
          this.$message.success('复制失败')
        })
    }
  }
}
</script>
