<template>
  <page-header-wrapper :title="false">
    <!-- <a-card :bordered="false" :body-style="{marginBottom: '8px'}">
      <router-link to="/dashboard/dts/new" ><a-button type="primary" style="margin-right: 8px">新建</a-button></router-link>
    </a-card> -->
    <a-card :bordered="false" title="" :body-style="{marginBottom: '8px'}">
      <div class="table-page-search-wrapper">
        <a-form-model
          layout="inline"
          ref="form"
          :model="searchParams"
          :rules="rules">
          <a-row :gutter="16">
            <a-col :md="3" :sm="24">
              <a-form-model-item label="站点" >
                <a-select v-model="searchParams.station_id" placeholder="请选择" >
                  <a-select-option v-for="d in stationItems" :key="d.id" :value="d.id">
                    {{ d.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>
            <a-col :md="3" :sm="24">
              <a-form-model-item label="类别">
                <a-select v-model="searchParams.category_id" placeholder="请选择">
                  <a-select-option v-for="d in categoryItems" :key="d.id" :value="d.id">
                    {{ d.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <a-form-model-item label="图名" prop="dwg_name">
                <a-input v-model="searchParams.dwg_name" placeholder=""/>
              </a-form-model-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <a-form-model-item label="图号" prop="dwg_num">
                <a-input v-model="searchParams.dwg_num" placeholder=""/>
              </a-form-model-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <a-form-model-item label="关键词" prop="keywords">
                <a-input v-model="searchParams.keywords" placeholder=""/>
              </a-form-model-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <span>
                <a-button shape="round" icon="search" @click="handleSearch(searchParams)"></a-button>
                <a-button style="margin-left: 8px" shape="round" icon="close" @click="resetSearchParams"></a-button>
                <router-link slot="extra" to="/dashboard/drawing/new"><a-button type="primary" shape="round" icon="plus" style="margin-left: 8px;"></a-button></router-link>
              </span>
            </a-col>
          </a-row>
        </a-form-model>
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
        <span slot="file_org_name" slot-scope="text, record">
          <template>
            <a @click="handleDownloadFile(record)">{{ text }}</a>
          </template>
        </span>
        <span slot="action" slot-scope="text, record">
          <template>
            <a @click="handleQueryDetails(record)">详情</a>
            <a-divider type="vertical" />
            <a @click="handleEdit(record)">修改</a>
            <a-divider type="vertical" />
            <a @click="handleDelete(record)">删除</a>
          </template>
        </span>

      </a-table>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import * as pattern from '@/utils/validateRegex'
import store from '@/store'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { apiQuery, apiDelete, apiDownloadFile } from '@/api/mix/drawing'

export default {
  name: 'Drawing',
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
        category_id: '0',
        dwg_name: '',
        dwg_num: '',
        keywords: ''
      },
      rules: {
        dwg_name: [
          { pattern: pattern.englishChineseNum__.regex, message: pattern.englishChineseNum__.msg, trigger: ['change'] }
        ],
        dwg_num: [
          { pattern: pattern.englishNum__.regex, message: pattern.englishNum__.msg, trigger: ['change'] }
        ],
        keywords: [
          { pattern: pattern.englishChineseNumComma.regex, message: pattern.englishChineseNumComma.msg, trigger: ['change'] }
        ]
      },
      //
      stationItems: [],
      categoryItems: [],
      //
      columns: [
        {
          title: '图名',
          dataIndex: 'dwg_name'
        },
        {
          title: '图号',
          dataIndex: 'dwg_num'
        },
        {
          title: '类别',
          dataIndex: 'category'
        },
        {
          title: '关键词',
          dataIndex: 'keywords'
        },
        {
          title: '文件',
          dataIndex: 'file_org_name',
          scopedSlots: { customRender: 'file_org_name' }
        },
        {
          title: '上传者',
          dataIndex: 'username'
        },
        {
          title: '创建日期',
          dataIndex: 'created_at'
        },
        {
          title: '更新日期',
          dataIndex: 'updated_at'
        },
        {
          title: '操作',
          dataIndex: 'action',
          scopedSlots: { customRender: 'action' }
        }
      ],

      pagination: {
        current: 1,
        pageSize: 10,
        total: 0,
        showTotal: (total) => { return '结果' + total }
      },

      loading: false,
      listData: []
    }
  },
  computed: {
    ...mapGetters([
      'userInfo', 'drawingListSearch'
    ])
  },
  beforeMount () {
    this.prepareSearchOptions()
  },
  mounted () {
    this.searchParams.station_id = this.userInfo.allowDefaultDeptId
    if (this.drawingListSearch) {
      this.pagination.current = this.drawingListSearch.pageId || 1
      this.searchParams = { ...this.searchParams, ...this.drawingListSearch.params }
      //
      // this.sendSearchReq(this.searchParams)
    }
    this.sendSearchReq(this.searchParams)
  },
  beforeRouteLeave (to, from, next) {
    if (to.name === 'DrawingDetails' || to.name === 'DrawingEdit') {
      var temp1 = { pageId: this.pagination.current, params: this.searchParams }
      store.dispatch('setDrawingListSearchParam', temp1)
    } else {
      store.dispatch('setDrawingListSearchParam', null)
    }
    next()
  },
  methods: {
    // 查询
    prepareSearchOptions () {
      const params = { resource: 'search_options' }
      apiQuery(params)
        .then(res => {
          this.stationItems = res.stationItems
          this.categoryItems = res.categoryItems
        })
        .catch(() => {
        })
    },

    resetSearchParams () {
      this.searchParams.station_id = this.userInfo.allowDefaultDeptId
      this.searchParams.category_id = '0'
      this.searchParams.dwg_name = ''
      this.searchParams.dwg_num = ''
      this.searchParams.keywords = ''
    },

    sendSearchReq (params) {
      var data = Object.assign(params, {
        resource: 'list',
        limit: this.pagination.pageSize,
        offset: this.pagination.current
      })
      data.dwg_name = params.dwg_name.trim()
      data.dwg_num = params.dwg_num.trim()
      data.keywords = params.keywords.trim()
      this.loading = true
      apiQuery(data)
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
      this.$refs.form.validate(valid => {
        if (valid) {
          this.pagination.current = 1
          this.sendSearchReq(this.searchParams)
        } else {
          return false
        }
      })
    },

    handleTableChange (pagination) {
      const pager = { ...this.pagination }
      pager.current = pagination.current
      this.pagination = pager

      this.sendSearchReq(this.searchParams)
    },

    handleDownloadFile (record) {
      const param = {
        id: record.id,
        file_org_name: record.file_org_name
      }
      apiDownloadFile(param)
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

            this.$message.info('文件已下载')
          }
        })
        .catch(() => {
          this.$message.info('文件下载失败')
        })
    },

    handleQueryDetails (record) {
      if (record.id) {
        const id = record.id
        this.$router.push({ path: `/dashboard/drawing/details/${id}` })
      }
    },

    handleEdit (record) {
      if (record.id) {
        const id = record.id
        this.$router.push({ path: `/dashboard/drawing/edit/${id}` })
      }
    },

    handleDelete (record) {
      if (record.id) {
        this.$confirm({
          title: '确定删除吗?',
          content: record.dwg_name,
          onOk: () => {
            const param = { id: record.id }
            apiDelete(param)
              .then(() => {
                this.handleSearch()
              })
              .catch(() => {
              })
          }
        })
      }
    }

  }
}
</script>
