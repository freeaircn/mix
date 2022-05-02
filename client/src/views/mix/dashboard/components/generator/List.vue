<template>
  <page-header-wrapper :title="false">

    <a-card :bordered="false" :bodyStyle="{marginBottom: '8px'}">
      <span>
        录入事件：
        <a-button type="primary" @click="onClickNewRecord('1')" style="margin-right: 16px">1G</a-button>
        <a-button type="primary" @click="onClickNewRecord('2')" style="margin-right: 16px">2G</a-button>
        <a-button type="primary" @click="onClickNewRecord('3')" >3G</a-button>
      </span>
    </a-card>

    <a-modal :title="recordDiagTitle" v-model="recordDiagVisible" :footer="null" :destroyOnClose="true">
      <RecordForm
        :stationId="userInfo.allowDefaultDeptId"
        :stationItems="userInfo.readDept"
        :genId="genId"
        :creator="this.userInfo.username"
        :update="recordUpdate"
        :recordInfo="recordInfo"
        @submitSuccess="onRecordFormSuccess"
      >
      </RecordForm>
    </a-modal>

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

          <a-form-model-item label="机组">
            <a-select v-model="query.generator_id" placeholder="机组" allowClear style="width: 75px">
              <a-select-option value="0">全部</a-select-option>
              <a-select-option value="1">1G</a-select-option>
              <a-select-option value="2">2G</a-select-option>
              <a-select-option value="3">3G</a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item label="事件">
            <a-select v-model="query.event" placeholder="事件" allowClear style="width: 90px">
              <a-select-option value="0">全部</a-select-option>
              <a-select-option value="1">停机</a-select-option>
              <a-select-option value="2">开机</a-select-option>
              <a-select-option value="3">检修开始</a-select-option>
              <a-select-option value="4">检修结束</a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item label="说明">
            <a-select v-model="query.description" placeholder="说明" allowClear style="width: 90px">
              <a-select-option value="0">全部</a-select-option>
              <a-select-option value="1">非“无”</a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item>
            <a-button type="primary" @click="onClickSearch">查询</a-button>
          </a-form-model-item>

          <a-form-model-item>
            <a-button @click="onClickExportExcel">导出</a-button>
          </a-form-model-item>

          <a-form-model-item>
            <a-button :loading="syncBtnLoading" icon="cloud-upload" @click="onClickSyncKKX">同步可靠性</a-button>
          </a-form-model-item>
        </a-form-model>
      </div>

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
        <span slot="generator_id" slot-scope="text">
          {{ text | generatorIdFilter }}
        </span>
        <span slot="event" slot-scope="text">
          {{ text | eventFilter }}
        </span>
        <span slot="cause" slot-scope="text">
          {{ text | causeFilter }}
        </span>
        <span slot="action" slot-scope="text, record">
          <template>
            <a @click="onClickUpdate(record)">修改</a>
            <a-divider type="vertical" />
            <a @click="onClickDel(record)">删除</a>
          </template>
        </span>
      </a-table>
    </a-card>

  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
import { RecordForm } from './modules'
import { mapGetters } from 'vuex'
import { apiQueryEvent, apiDelEvent, apiExportExcel, apiSyncToKKX } from '@/api/mix/generator'
import { baseMixin } from '@/store/app-mixin'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '机组',
    dataIndex: 'generator_id',
    scopedSlots: { customRender: 'generator_id' }
  },
  {
    title: '事件',
    dataIndex: 'event',
    scopedSlots: { customRender: 'event' }
  },
  {
    title: '分类',
    dataIndex: 'cause',
    scopedSlots: { customRender: 'cause' }
  },
  {
    title: '时间',
    dataIndex: 'event_at'
  },
  {
    title: '记录人',
    dataIndex: 'creator'
  },
  {
    title: '说明',
    dataIndex: 'description'
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '150px',
    scopedSlots: { customRender: 'action' }
  }
]

const generatorIdMap = {
  1: { text: '1G' },
  2: { text: '2G' },
  3: { text: '3G' },
  4: { text: '4G' }
}

const eventMap = {
  1: { text: '停机' },
  2: { text: '开机' },
  3: { text: '检修开始' },
  4: { text: '检修结束' }
}

const causeMap = {
  0: { text: ' - ' },
  1: { text: '调度许可' },
  2: { text: '正常' },
  11: { text: '试验' },
  12: { text: '空转' },
  21: { text: '设备故障' },
  22: { text: '保护动作' },
  23: { text: '稳控动作' },
  31: { text: 'A级检修' },
  32: { text: 'B级检修' },
  33: { text: 'C级检修' },
  34: { text: 'D级检修' },
  35: { text: '其他' }
}

export default {
  name: 'List',
  mixins: [baseMixin],
  components: {
    RecordForm
  },
  data () {
    this.columns = columns
    return {
      recordDiagTitle: '录入机组事件',
      recordDiagVisible: false,
      recordUpdate: false,
      recordInfo: {},
      genId: '',

      // 记录 显示
      // stationItems: [],
      query: {
        station_id: '0',
        date: '',
        generator_id: '0',
        event: '0',
        description: '0'
      },
      loading: false,
      records: [],

      pagination: {
        current: 1,
        pageSize: 10,
        total: 0
      },

      syncBtnLoading: false
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  // beforeMount () {
  //   this.prepareSearchFunc()
  // },
  mounted () {
    this.query.station_id = this.userInfo.allowDefaultDeptId
    this.onClickSearch()
  },
  filters: {
    generatorIdFilter (type) {
      return generatorIdMap[type].text
    },
    eventFilter (type) {
      return eventMap[type].text
    },
    causeFilter (type) {
      return causeMap[type].text
    }
  },
  methods: {

    onClickNewRecord (genId) {
      this.genId = genId
      this.recordDiagTitle = '录入机组事件 ' + genId + 'G'
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

    // 2022-4-29
    // prepareSearchFunc () {
    //   const params = { resource: 'search_params' }
    //   apiQueryEvent(params)
    //     .then(res => {
    //       res.station.forEach(element => {
    //         this.stationItems.push(element)
    //       })
    //     })
    //     .catch(() => {
    //     })
    // },

    sendSearchReq (searchParams) {
      const params = Object.assign(searchParams, {
        resource: 'list',
        limit: this.pagination.pageSize,
        offset: this.pagination.current
      })
      this.loading = true
      apiQueryEvent(params)
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

    // 点击查询
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

    // 导出excel
    onClickExportExcel () {
      this.$confirm({
        title: '导出全年记录？',
        content: '日期 ' + this.query.date.split('-')[0] + '年',
        onOk: () => {
          const params = {
            resource: 'export_excel',
            station_id: this.query.station_id,
            date: this.query.date
          }
          apiExportExcel(params)
            .then(res => {
              const { data, headers } = res
              const blob = new Blob([data], { type: headers['content-type'] })
              const dom = document.createElement('a')
              const url = window.URL.createObjectURL(blob)
              const filename = '开停机记录_' + moment().format('YYYY-MM-DD') + '.xlsx'
              dom.href = url
              dom.download = filename
              dom.style.display = 'none'
              document.body.appendChild(dom)
              dom.click()
              dom.parentNode.removeChild(dom)
              window.URL.revokeObjectURL(url)

              this.$message.info('已导出文件')
            })
            .catch(() => {
              this.$message.info('导出文件失败')
            })
        }
      })
    },

    onClickSyncKKX () {
      this.$confirm({
        title: '确认记录同步至发电可靠性系统吗',
        content: '日期 ' + this.query.date.substr(0, 7),
        onOk: () => {
          const params = {
            resource: 'sync_kkx',
            station_id: this.query.station_id,
            date: this.query.date
          }
          this.syncBtnLoading = true
          apiSyncToKKX(params)
            .then(() => {
              this.syncBtnLoading = false
            })
            .catch(() => {
              this.syncBtnLoading = false
            })
        }
      })
    },

    onClickUpdate (record) {
      this.recordInfo = record
      this.recordDiagTitle = '修改记录 ' + record.generator_id + 'G'
      this.recordDiagVisible = true
      this.recordUpdate = true
      setTimeout(() => { this.recordUpdate = false }, 500)
    },

    onClickDel (record) {
      const param = {
        id: record.id,
        station_id: record.station_id,
        generator_id: record.generator_id,
        event: record.event
      }

      let text = ''
      if (record.event === '1') {
        text = record.event_at + ' ' + record.generator_id + 'G ' + ' 停机'
      }
      if (record.event === '2') {
        text = record.event_at + ' ' + record.generator_id + 'G ' + ' 开机'
      }
      if (record.event === '3') {
        text = record.event_at + '  ' + record.generator_id + 'G 检修开始'
      }
      if (record.event === '4') {
        text = record.event_at + '  ' + record.generator_id + 'G 检修结束'
      }
      this.$confirm({
        title: '确认删除吗',
        content: '记录: ' + text,
        onOk: () => {
          apiDelEvent(param)
            .then(() => {
                this.sendSearchReq(this.query)
              })
              .catch(() => {
              })
        }
      })
    }

  }
}
</script>

<style lang="less" scoped>
</style>
