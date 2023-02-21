<template>
  <page-header-wrapper :title="false">
    <!-- <a-card :bordered="false" :body-style="{marginBottom: '8px'}">
      <router-link to="/dashboard/dts/new" ><a-button type="primary" style="margin-right: 8px">新建</a-button></router-link>
    </a-card> -->
    <a-card :bordered="false" title="" :body-style="{marginBottom: '8px'}">
      <div class="table-page-search-wrapper">
        <a-form layout="inline" :label-col="labelCol" :wrapper-col="wrapperCol">
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
              <a-form-item label="类别">
                <a-select v-model="searchParams.category_id" placeholder="请选择">
                  <a-select-option v-for="d in categoryItems" :key="d.id" :value="d.id">
                    {{ d.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <a-form-item label="图名">
                <a-input v-model="searchParams.dwg_name" placeholder=""/>
              </a-form-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <a-form-item label="图号">
                <a-input v-model="searchParams.dwg_num" placeholder=""/>
              </a-form-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <a-form-item label="关键词">
                <a-input v-model="searchParams.keywords" placeholder=""/>
              </a-form-item>
            </a-col>
            <a-col :md="4" :sm="24">
              <span>
                <a-button shape="round" icon="search" @click="handleSearch(searchParams)"></a-button>
                <a-button style="margin-left: 8px" shape="round" icon="close" @click="resetSearchParams"></a-button>
                <router-link slot="extra" to="/dashboard/drawing/new"><a-button type="primary" shape="round" icon="plus" style="margin-left: 8px;"></a-button></router-link>
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
        <!-- <span slot="dts_id" slot-scope="text, record">
          <template>
            <a @click="handleQueryDetails(record)">{{ text }}</a>
          </template>
        </span> -->
        <span slot="action" slot-scope="text, record">
          <template>
            <a @click="handleQueryDetails(record)">详情</a>
            <a-divider type="vertical" />
            <a @click="handleEdit(record)">编辑</a>
            <a-divider type="vertical" />
            <a @click="handleDelete(record)">删除</a>
          </template>
        </span>

      </a-table>
    </a-card>
  </page-header-wrapper>
</template>

<script>
// import moment from 'moment'
import store from '@/store'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { apiQuery, apiDelete } from '@/api/mix/drawing'

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
          title: '附件名',
          dataIndex: 'file_org_name'
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
    if (to.name === 'DtsDetails' || to.name === 'DtsEdit') {
      var temp1 = { pageId: this.pagination.current, params: this.searchParams }
      store.dispatch('setDtsListSearchParam', temp1)
    } else {
      store.dispatch('setDtsListSearchParam', null)
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
      this.pagination.current = 1
      this.sendSearchReq(this.searchParams)
    },

    handleTableChange (pagination) {
      const pager = { ...this.pagination }
      pager.current = pagination.current
      this.pagination = pager

      this.sendSearchReq(this.searchParams)
    },

    handleQueryDetails (record) {
      if (record.id) {
        // const id = record.id
        // this.$router.push({ path: `/dashboard/dts/details/${id}` })
      }
    },

    handleEdit (record) {
      if (record.id) {
        // const id = record.id
        // this.$router.push({ path: `/dashboard/dts/edit/${id}` })
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
