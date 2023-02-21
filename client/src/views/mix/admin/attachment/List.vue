<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" title="" :body-style="{marginBottom: '8px'}">

      <div class="table-page-search-wrapper">
        <a-form layout="inline" :label-col="labelCol" :wrapper-col="wrapperCol">
          <a-row :gutter="24">
            <a-col :md="6" :sm="24">
              <a-form-model-item label="类别" >
                <a-select v-model="searchParams.category_id" placeholder="请选择" >
                  <a-select-option v-for="d in categoryItems" :key="d.id" :value="d.id">
                    {{ d.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>

            <a-col :md="6" :sm="24">
              <a-form-model-item label="站点" >
                <a-select v-model="searchParams.station_id" placeholder="请选择" >
                  <a-select-option v-for="d in stationItems" :key="d.id" :value="d.id">
                    {{ d.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>

            <a-col :md="6" :sm="24">
              <a-form-item label="文件名">
                <a-input v-model="searchParams.filename" placeholder=""/>
              </a-form-item>
            </a-col>
            <a-col :md="6" :sm="24">
              <span>
                <a-button type="primary" @click="handleSearch(searchParams)">查询</a-button>
                <a-button style="margin-left: 8px" @click="resetSearchParams">重置</a-button>
              </span>
            </a-col>
          </a-row>
        </a-form>
      </div>

      <a-table
        ref="table"
        rowKey="id"
        :columns="columns"
        :data-source="listData"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
      >
        <span slot="org_name" slot-scope="text, record">
          <template>
            <a @click="onClickDownload(record)">{{ text }}</a>
          </template>
        </span>
        <span slot="action" slot-scope="text, record">
          <template>
            <a @click="onClickDownload(record)">下载</a>
            <a-divider type="vertical" />
            <a @click="onClickDel(record)">删除</a>
          </template>
        </span>

      </a-table>
    </a-card>
  </page-header-wrapper>
</template>

<script>
// import moment from 'moment'
// import store from '@/store'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { queryDts, downloadAttachment, delAttachment } from '@/api/mix/dts'

export default {
  name: 'AttachmentList',
  mixins: [baseMixin],
  data () {
    return {
      labelCol: {
        lg: { span: 7 }, sm: { span: 7 }
      },
      wrapperCol: {
        lg: { span: 10 }, sm: { span: 17 }
      },
      //
      searchParams: {
        station_id: '0',
        filename: ''
      },
      //
      categoryItems: [],
      stationItems: [],
      //
      columns: [
        {
          title: '类别',
          dataIndex: 'station'
        },
        {
          title: '站点',
          dataIndex: 'station'
        },
        {
          title: '附件',
          dataIndex: 'org_name',
          scopedSlots: { customRender: 'org_name' }
        },
        {
          title: '绑定',
          dataIndex: 'bind_id'
        },
        {
          title: '主体',
          dataIndex: 'title'
        },
        {
          title: '上传',
          dataIndex: 'username'
        },
        {
          title: '上传日期',
          dataIndex: 'created_at'
        },
        {
          title: '操作',
          dataIndex: 'action',
          scopedSlots: { customRender: 'action' }
        }
      ],

      pagination: {
        current: 1,
        pageSize: 8,
        total: 0
      },

      loading: false,
      listData: []
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  beforeMount () {
    this.prepareSearchFunc()
  },
  mounted () {
    this.searchParams.station_id = this.userInfo.allowDefaultDeptId
    // this.sendSearchReq(this.searchParams)
  },
  methods: {

    // 查询
    prepareSearchFunc () {
      const params = { resource: 'search_params' }
      queryDts(params)
        .then(res => {
          this.stationItems = res.stationItems
        })
        .catch(() => {
        })
    },

    resetSearchParams () {
      this.searchParams.station_id = this.userInfo.allowDefaultDeptId
      this.searchParams.filename = ''
    },

    sendSearchReq (searchParams) {
      var params = Object.assign(searchParams, {
        resource: 'attachments_list',
        limit: this.pagination.pageSize,
        offset: this.pagination.current
      })
      params.filename = searchParams.filename.trim()
      this.loading = true
      queryDts(params)
        .then(res => {
          const pagination = { ...this.pagination }
          pagination.total = res.total
          this.pagination = pagination
          this.listData = res.data
          //
          this.loading = false
        })
        .catch((err) => {
          this.loading = false
          this.listData.splice(0, this.listData.length)
          if (err.response) {
          }
        })
    },

    handleSearch () {
      this.pagination.current = 1
      this.sendSearchReq(this.searchParams)
    },

    handleTableChange (pagination) {
      const pager = { ...this.pagination }
      pager.current = pagination.current
      this.pagination = pager

      this.sendSearchReq(this.searchParams)
    },

    onClickDownload (record) {
      this.$confirm({
        title: '确认下载附件吗？',
        content: record.org_name,
        onOk: () => {
          const param = { id: record.id, dts_id: record.dts_id }
          downloadAttachment(param)
            .then((res) => {
              const { data, headers } = res

              const str = headers['content-type']
              if (str.indexOf('json') !== -1) {
                this.$message.warning('没有权限')
              } else {
                // 下载文件
                const blob = new Blob([data], { type: headers['content-type'] })
                const dom = document.createElement('a')
                const url = window.URL.createObjectURL(blob)
                dom.href = url
                const filename = headers['content-disposition'].split(';')[1].split('=')[1]
                dom.download = decodeURI(filename)
                dom.style.display = 'none'
                document.body.appendChild(dom)
                dom.click()
                dom.parentNode.removeChild(dom)
                window.URL.revokeObjectURL(url)

                this.$message.info('已下载附件')
              }
            })
            .catch(() => {
              this.$message.info('下载附件失败')
            })
        },
        onCancel () {
        }
      })
    },

    onClickDel (record) {
      this.$confirm({
        title: '确定删除附件?',
        content: record.org_name,
        onOk: () => {
          const param = { id: record.id, dts_id: record.dts_id }
          return delAttachment(param)
            .then(() => {
              this.$message.info('已删除附件')
              this.handleSearch()
            })
            .catch(() => {
              this.$message.warning('删除附件失败')
            })
        }
      })
    }

  }
}
</script>
