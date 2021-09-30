<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-09-30 20:50:48
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-30 23:51:11
-->
<template>
  <page-header-wrapper :title="false">

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <a-card :bordered="false" title="进度" :loading="loading" :body-style="{marginBottom: '8px'}">
      <router-link slot="extra" to="/dashboard/dts/list">返回</router-link>

      <a-steps size="small" :current="1">
        <a-step title="提交" description="This is a description." />
        <a-step title="检查" description="This is a description." ><a-icon slot="icon" type="loading" /></a-step>
        <a-step title="审核" description="This is a description." />
        <a-step title="解决" description="This is a description." />
        <a-step title="关闭" description="This is a description." />
      </a-steps>
      <a-divider />
    </a-card>

    <a-card :bordered="false" title="详情" :loading="loading" :body-style="{marginBottom: '8px'}">
      <a slot="extra" @click="onQueryDetails">刷新</a>

      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="单号">{{ details.ticket_id }}</a-descriptions-item>
        <a-descriptions-item label="类别">{{ details.type }}</a-descriptions-item>
        <a-descriptions-item label="影响程度">{{ details.level }}</a-descriptions-item>
        <a-descriptions-item label="进度">{{ details.place_at }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="标题">{{ details.title }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="所属单元">{{ details.equipment_unit }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="创建人">{{ details.creator }}</a-descriptions-item>
        <a-descriptions-item label="创建时间">{{ details.created_at }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="处理人">{{ details.handler }}</a-descriptions-item>
        <a-descriptions-item label="更新时间">{{ details.updated_at }}</a-descriptions-item>
      </a-descriptions>

      <div style="margin-bottom: 8px">进展:</div>
      <div style="width: 100%"><a-textarea :defaultValue="details.progress" :rows="10" /></div>

      <a-divider style="margin-bottom: 32px"/>
    </a-card>

  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { getDtsTicketDetails } from '@/api/service'

export default {
  name: 'DtsTicketDetails',
  mixins: [baseMixin],
  data () {
    return {
      ticketId: '',
      steps: [],
      loading: false,
      details: {
        ticket_id: '20210930001',
        type: '故障',
        level: '严重',
        place_at: '检查',
        title: 'xxG的XX故障',
        equipment_unit: 'xxGxx',
        creator: '小强',
        created_at: '2021-09-30 20:24:25',
        handler: '小强',
        updated_at: '2021-09-30 20:24:25',
        progress: '【问题描述】\n\n【发生时间】\n\n【问题影响】\n\n【已采取措施】\n\n'
      }
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {
    this.ticketId = (this.$route.params.ticketId) ? this.$route.params.ticketId : '0'
  },
  mounted () {
    this.onQueryDetails()
  },
  methods: {

    // 查询
    onQueryDetails () {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        ticket_id: this.ticketId
      }
      this.loading = true
      getDtsTicketDetails(query)
        .then(data => {
          this.loading = false
          //
        })
        .catch((err) => {
          this.loading = false
          if (err.response) {
          }
        })
    }

  }
}
</script>
