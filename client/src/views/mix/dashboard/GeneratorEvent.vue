<template>
  <page-header-wrapper :title="false">

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :bodyStyle="{marginBottom: '8px'}">
      <span>
        录入事件：
        <a-button type="primary" @click="onClickNewRecord('1')" style="margin-right: 16px">1G</a-button>
        <a-button type="primary" @click="onClickNewRecord('2')" style="margin-right: 16px">2G</a-button>
        <a-button type="primary" @click="onClickNewRecord('3')" >3G</a-button>
      </span>
    </a-card>

    <a-modal :title="recordDiagTitle" v-model="recordDiagVisible" :footer="null" :destroyOnClose="true">
      <RecordForm
        :stationId="userInfo.belongToDeptId"
        :genId="genId"
        :creator="this.userInfo.username"
        :update="recordUpdate"
        :recordInfo="recordInfo"
        @submitSuccess="onRecordFormSuccess"
      >
      </RecordForm>
    </a-modal>

    <div :style="{ marginBottom: '8px' }">
      <a-card :bordered="false" title="事件记录" :style="{ height: '100%' }">
        <RecordList
          :loading="listLoading"
          :date.sync="queryRecordDate"
          :genId.sync="queryRecordGenId"
          :listData="recordListData"
          :current.sync="recordListPageIndex"
          :total="totalRecords"
          @paginationChange="onRecordListPageChange"
          @query="onQueryRecord"
          @update="onUpdateRecord"
          @delete="onDeleteRecord"
          @export="onExportEventFile"
        >
        </RecordList>
      </a-card>
    </div>

    <div :style="{marginBottom: '8px'}">
      <StatisticChart
        :stationId="userInfo.belongToDeptId"
      >
      </StatisticChart>
    </div>

    <div :style="{marginBottom: '8px'}">
      <a-card :loading="false" title="全景" :bordered="false">
      </a-card>
    </div>

  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
import { RecordForm, RecordList, StatisticChart } from './components/generator'
import { mapGetters } from 'vuex'
import { apiGetEvent, apiDelEvent, apiGetExportEvent } from '@/api/mix/generator'
import { baseMixin } from '@/store/app-mixin'

export default {
  name: 'GeneratorEvent',
  mixins: [baseMixin],
  components: {
    RecordForm,
    RecordList,
    StatisticChart
  },
  data () {
    return {
      recordDiagTitle: '录入机组事件',
      recordDiagVisible: false,
      recordUpdate: false,
      recordInfo: {},
      genId: '',

      // 记录 显示
      listLoading: false,
      recordListPageIndex: 1,
      pageSize: 5,
      recordListData: [],
      totalRecords: 0,
      queryRecordDate: '',
      queryRecordGenId: ''
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  // created () {
  // },
  mounted () {
    this.onQueryRecord()
  },
  methods: {

    // 记录-增
    onClickNewRecord (genId) {
      this.genId = genId
      this.recordDiagTitle = '录入机组事件 ' + genId + 'G'
      this.recordDiagVisible = true
    },

    onRecordFormSuccess (method) {
      this.recordDiagVisible = false
      if (method === 'post') {
        this.onQueryRecordAfterUpdate(this.queryRecordDate, this.queryRecordGenId)
      }
      if (method === 'put') {
        this.onQueryRecordAfterUpdate(this.queryRecordDate, this.queryRecordGenId)
      }
    },

    // 记录-查
    onQueryRecord (date = '', gid = 0) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        generator_id: gid,
        date: date,
        limit: this.pageSize,
        offset: 1
      }
      this.recordListPageIndex = 1
      this.listLoading = true
      apiGetEvent(query)
        .then(res => {
          this.listLoading = false
          //
          this.totalRecords = res.total
          this.queryRecordDate = res.date
          this.recordListData = res.data
        })
        .catch((err) => {
          this.listLoading = false
          this.recordListData.splice(0, this.recordListData.length)
          if (err.response) {
          }
        })
    },

    onQueryRecordAfterUpdate (date = '', gid = 0) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        generator_id: gid,
        date: date,
        limit: this.pageSize,
        offset: this.recordListPageIndex
      }

      this.recordListLoading = true
      apiGetEvent(query)
        .then(res => {
          this.listLoading = false
          //
          this.totalRecords = res.total
          // this.queryRecordDate = res.date
          this.recordListData = res.data
        })
        .catch((err) => {
          this.listLoading = false
          this.recordListData.splice(0, this.recordListData.length)
          if (err.response) {
          }
        })
    },

    // 点击分页
    onRecordListPageChange (param) {
      const query = { ...param }
      query.station_id = this.userInfo.belongToDeptId
      query.date = this.queryRecordDate

      this.listLoading = true
      apiGetEvent(query)
        .then(res => {
          this.listLoading = false
          //
          this.totalRecords = res.total
          this.recordListData = res.data
        })
        .catch((err) => {
          this.listLoading = false
          this.recordListData.splice(0, this.recordListData.length)
          if (err.response) {
          }
        })
    },

    onDeleteRecord (param) {
      apiDelEvent(param)
        .then(() => {
            this.onQueryRecordAfterUpdate(this.queryRecordDate, this.queryRecordGenId)
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) { }
          })
    },

    onUpdateRecord (record) {
      this.recordInfo = record
      this.recordDiagTitle = '修改记录 ' + record.generator_id + 'G'
      this.recordDiagVisible = true
      this.recordUpdate = true
      setTimeout(() => { this.recordUpdate = false }, 500)
    },

    // 导出excel文件
    onExportEventFile (date) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: date
      }

      apiGetExportEvent(query)
        .then(res => {
          const { data, headers } = res

          const str = headers['content-type']
          if (str.indexOf('json') !== -1) {
            this.$message.warning('没有权限')
          } else {
            // 下载excel文件
            const blob = new Blob([data], { type: headers['content-type'] })
            const dom = document.createElement('a')
            const url = window.URL.createObjectURL(blob)
            const filename = this.userInfo.belongToDeptName + '_开停机记录_' + moment().format('YYYY-MM-DD') + '.xlsx'
            dom.href = url
            dom.download = decodeURI(filename)
            dom.style.display = 'none'
            document.body.appendChild(dom)
            dom.click()
            dom.parentNode.removeChild(dom)
            window.URL.revokeObjectURL(url)

            this.$message.info('已导出文件')
          }
        })
        .catch((err) => {
          this.$message.info('导出文件失败')
          if (err.response) {
          }
        })
    }
  }
}
</script>

<style lang="less" scoped>
</style>
