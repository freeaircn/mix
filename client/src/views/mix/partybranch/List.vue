<template>
  <page-header-wrapper :title="false">
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
                <a-cascader
                  :options="categoryItems"
                  v-model="searchParams.category"
                  :allowClear="true"
                  expand-trigger="hover"
                  change-on-select
                  :displayRender="cascaderDisplayRender"
                  :fieldNames="{ label: 'name', value: 'id', children: 'children' }"
                  placeholder="请选择"
                />
              </a-form-model-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <a-form-model-item label="标题" prop="title">
                <a-input v-model="searchParams.title" :allowClear="true" placeholder=""/>
              </a-form-model-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <a-form-model-item label="关键词" prop="keywords">
                <a-input v-model="searchParams.keywords" :allowClear="true" placeholder=""/>
              </a-form-model-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <span>
                <a-button shape="round" icon="search" @click="handleSearch(searchParams)"></a-button>
                <a-button style="margin-left: 8px" shape="round" icon="close" @click="resetSearchParams"></a-button>
                <a-button type="primary" shape="round" icon="plus" style="margin-left: 8px;" @click="handleNew"></a-button>
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
        <span slot="title_" slot-scope="text, record">
          <template>
            <a @click="handleQueryDetails(record)">{{ text }}</a>
          </template>
        </span>
        <span slot="file_org_name" slot-scope="text, record">
          <template>
            <a @click="handleQueryDetails(record)">{{ text }}</a>
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
// import store from '@/store'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { apiQuery, apiDelete, apiDownloadFile } from '@/api/mix/party_branch'

export default {
  name: 'List',
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
        category: [],
        title: '',
        keywords: ''
      },
      rules: {
        title: [
          { pattern: pattern.TITLE.regex, message: pattern.TITLE.msg, trigger: ['change'] }
        ],
        keywords: [
          { pattern: pattern.KEY_WORDS.regex, message: pattern.KEY_WORDS.msg, trigger: ['change'] }
        ]
      },
      //
      stationItems: [],
      categoryItems: [],
      //
      columns: [
        {
          title: '标题',
          dataIndex: 'title',
          scopedSlots: { customRender: 'title_' }
        },
        {
          title: '文件号',
          dataIndex: 'doc_num'
        },
        {
          title: '类别',
          dataIndex: 'category'
        },
        {
          title: '密级',
          dataIndex: 'secret_level'
        },
        {
          title: '保管期',
          dataIndex: 'retention_period'
        },
        {
          title: '存放放点',
          dataIndex: 'store_place'
        },
        // {
        //   title: '最后编辑',
        //   dataIndex: 'username'
        // },
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
      'userInfo'
      // 'drawingListSearch'
    ])
  },
  beforeMount () {
    this.prepareSearchOptions()
  },
  mounted () {
    this.searchParams.station_id = this.userInfo.allowDefaultDeptId
    // if (this.drawingListSearch) {
    //   this.pagination.current = this.drawingListSearch.pageId || 1
    //   this.searchParams = { ...this.searchParams, ...this.drawingListSearch.params }
    //   //
    //   // this.sendSearchReq(this.searchParams)
    // }
    this.sendSearchReq(this.searchParams)
  },
  // beforeRouteLeave (to, from, next) {
  //   if (to.name === 'DrawingDetails' || to.name === 'DrawingEdit') {
  //     var temp1 = { pageId: this.pagination.current, params: this.searchParams }
  //     store.dispatch('setDrawingListSearchParam', temp1)
  //   } else {
  //     store.dispatch('setDrawingListSearchParam', null)
  //   }
  //   next()
  // },
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
      this.searchParams.category = []
      this.searchParams.title = ''
      this.searchParams.keywords = ''
    },

    sendSearchReq (params) {
      var data = { ...params }
      data.title = params.title.trim()
      data.keywords = params.keywords.trim()
      //
      if (params.category.length === 0) {
        data.category_id = '0'
      } else {
        data.category_id = params.category[params.category.length - 1]
      }
      data.resource = 'list'
      data.limit = this.pagination.pageSize
      data.offset = this.pagination.current
      //
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
        this.$router.push({ path: `/party_branch/details/${id}` })
      }
    },

    handleEdit (record) {
      if (record.id) {
        const id = record.id
        this.$router.push({ path: `/party_branch/edit/${id}` })
      }
    },

    handleNew () {
      this.$router.push({ path: `/party_branch/new` })
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
    },

    cascaderDisplayRender ({ labels, selectedOptions }) {
      // if (labels.length > 0) {
      //   return labels[labels.length - 1]
      // } else {
      //   return ''
      // }
      return labels[labels.length - 1]
    }
  }
}
</script>
