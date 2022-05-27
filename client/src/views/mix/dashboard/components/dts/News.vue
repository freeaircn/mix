<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" :body-style="{marginBottom: '8px'}">
      <a-button type="primary" @click="onClickRefresh" style="margin-right: 16px">刷新</a-button>
      <router-link to="/dashboard/dts/new" ><a-button type="default" style="margin-right: 8px">新建</a-button></router-link>
      <router-link to="/dashboard/dts/list"><a-button type="default">检索</a-button></router-link>
    </a-card>
    <a-card :bordered="false" title="更新动态" :body-style="{marginBottom: '8px'}">
      <a-table
        ref="table"
        rowKey="id"
        :columns="columns"
        :data-source="listData"
        :pagination="false"
        :loading="loading"
      >
        <span slot="type" slot-scope="text">
          {{ text | typeFilter }}
        </span>
        <span slot="title_" slot-scope="text, record">
          <template>
            <a @click="onQueryDetails(record)">{{ text }}</a>
          </template>
        </span>
        <span slot="dts_id" slot-scope="text, record">
          <template>
            <a @click="onQueryDetails(record)">{{ text }}</a>
          </template>
        </span>
      </a-table>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { queryDts } from '@/api/mix/dts'

const typeMap = {
  '1': { text: '隐患' },
  '2': { text: '缺陷' }
}

export default {
  name: 'DtsNews',
  mixins: [baseMixin],
  data () {
    return {
      columns: [
        {
          title: '类型',
          dataIndex: 'type',
          scopedSlots: { customRender: 'type' }
        },
        {
          title: '标题',
          dataIndex: 'title_',
          scopedSlots: { customRender: 'title_' }
        },
        {
          title: '站点',
          dataIndex: 'station'
        },
        {
          title: '进度',
          dataIndex: 'place_at'
        },
        {
          title: '单号',
          dataIndex: 'dts_id',
          scopedSlots: { customRender: 'dts_id' }
        },
        {
          title: '更新日期',
          dataIndex: 'updated_at'
        }
      ],

      pagination: {
        current: 1,
        pageSize: 8,
        total: 0
      },

      loading: false,
      listData: [ ]
    }
  },
  filters: {
    typeFilter (id) {
      return typeMap[id].text
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  mounted () {
    this.sendSearchReq()
  },
  methods: {
    onClickRefresh () {
      this.sendSearchReq()
    },

    sendSearchReq () {
      const params = {
        resource: 'news',
        station_id: this.userInfo.allowDefaultDeptId
      }
      this.loading = true
      queryDts(params)
        .then(data => {
          this.listData = data
          //
          this.loading = false
        })
        .catch(() => {
          this.loading = false
          this.listData.splice(0, this.listData.length)
        })
    },

    onQueryDetails (record) {
      if (record.dts_id) {
        const id = record.dts_id
        this.$router.push({ path: `/dashboard/dts/details/${id}` })
      }
    }

  }
}
</script>
