<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" title="详情" :loading="loading" :body-style="{marginBottom: '8px'}">
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="站点">{{ details.station }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="图号">{{ details.dwg_num }}</a-descriptions-item>
        <a-descriptions-item label="类别">{{ details.category }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="图名">{{ details.dwg_name }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="关键词">{{ details.keywords }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="文件">{{ details.file_org_name }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="创建时间">{{ details.created_at }}</a-descriptions-item>
        <a-descriptions-item label="更新时间">{{ details.updated_at }}</a-descriptions-item>
      </a-descriptions>

      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="最后修改">{{ details.username }}</a-descriptions-item>
      </a-descriptions>

      <div style="margin-bottom: 8px">补充信息:</div>
      <a-row :gutter="16">
        <a-col :md="12" :sm="24">
          <div style="width: 100%; margin-bottom: 8px">
            <a-textarea id="textarea_id" v-model="details.info" :defaultValue="details.description" :rows="6" readOnly/>
          </div>
        </a-col>
      </a-row>

      <div style="margin-bottom: 8px">
        <a-button type="primary" @click="handleDownloadFile" style="margin-right: 16px">下载文件</a-button>
        <a-button type="default" @click="handleExit" style="margin-right: 16px">返回</a-button>
      </div>
    </a-card>

  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { apiQuery, apiDownloadFile } from '@/api/mix/drawing'

export default {
  name: 'Details',
  mixins: [baseMixin],
  data () {
    return {
      id: '0',
      loading: false,
      details: {
        id: '',
        station: '',
        category: '',
        dwg_name: '',
        dwg_num: '',
        keywords: '',
        file_org_name: '',
        info: '',
        username: '',
        created_at: '',
        updated_at: ''
      }
      // -- 2023-2-22
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {
    this.id = (this.$route.params.id) ? this.$route.params.id : '0'
  },
  mounted () {
    this.handleQueryDetails()
  },
  methods: {
    // 查询
    handleQueryDetails () {
      if (this.id === '0') {
        return
      }
      this.loading = true
      const params = { resource: 'details', id: this.id }
      apiQuery(params)
        .then(data => {
          Object.assign(this.details, data)
          this.loading = false
        })
        .catch(() => {
          this.loading = false
        })
    },

    handleDownloadFile () {
      const param = {
        id: this.details.id,
        file_org_name: this.details.file_org_name
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

    handleExit () {
      this.$router.back()
    }
  }
}
</script>
