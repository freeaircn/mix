<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" :loading="loading" :body-style="{marginBottom: '8px'}">
      <!-- <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="站点">{{ details.station }}</a-descriptions-item>
      </a-descriptions> -->
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="类别">{{ details.category }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="文档标题">{{ details.title }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="文档编号">{{ details.doc_num }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="密级">{{ details.secret_level }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="关键词">{{ details.keywords }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="创建时间">{{ details.created_at }}</a-descriptions-item>
        <a-descriptions-item label="更新时间">{{ details.updated_at }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="保管期限">{{ details.retention_period }}</a-descriptions-item>
        <a-descriptions-item label="存放地点">{{ details.store_place }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="最后编辑">{{ details.username }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="概要">{{ details.summary }}</a-descriptions-item>
      </a-descriptions>
      <!-- <div style="margin-bottom: 8px">概要:</div>
      <a-row :gutter="16">
        <a-col :md="12" :sm="24">
          <div style="width: 100%; margin-bottom: 8px">
            <a-textarea id="textarea_id" v-model="details.summary" :defaultValue="details.description" :rows="6" readOnly/>
          </div>
        </a-col>
      </a-row> -->
      <div style="margin-bottom: 8px; font-weight: 700">文档：</div>
      <div style="margin-bottom: 8px">
        <a-row :gutter="16">
          <a-col :md="12" :sm="24">
            <div style="width: 100%; margin-bottom: 8px">
              <files-table :listData="fileList" @preview="handlePreviewFile" @download="handleDownloadFile" @delete="handleDeleteFile"></files-table>
            </div>
          </a-col>
        </a-row>
      </div>

      <div style="margin-bottom: 8px">
        <!-- <a-button type="primary" @click="handleDownloadFile" style="margin-right: 16px">下载文件</a-button>
        <a-button type="primary" @click="handlePreviewFile" style="margin-right: 16px">预览文件</a-button> -->
        <a-button type="default" @click="handleExit" style="margin-right: 16px">返回</a-button>
      </div>
    </a-card>

  </page-header-wrapper>
</template>

<script>
import { partyBranch as CONFIG } from '@/config/myConfig'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { apiQuery, apiDownloadFile } from '@/api/mix/party_branch'
import FilesTable from '@/views/mix/partybranch/components/FilesTable'
//
import pdf from 'vue-pdf'

export default {
  name: 'PartyBranchDetails',
  components: {
      FilesTable,
      pdf
  },
  mixins: [baseMixin],
  data () {
    return {
      uuid: '0',
      loading: false,
      details: {
        id: '',
        uuid: '',
        station: '',
        category: '',
        title: '',
        doc_num: '',
        keywords: '',
        secret_level: '',
        retention_period: '',
        store_place: '',
        summary: '',
        username: '',
        created_at: '',
        updated_at: ''
      },
      fileList: []
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {
    this.uuid = (this.$route.params.uuid) ? this.$route.params.uuid : '0'
  },
  mounted () {
    this.handleQueryDetails(this.uuid)
  },
  methods: {
    // 查询
    handleQueryDetails (uuid) {
      if (uuid === '0') {
        return
      }
      this.loading = true
      const params = { resource: 'details', uuid: this.uuid }
      apiQuery(params)
        .then(data => {
          this.details = data.details
          this.fileList = data.files
          this.loading = false
        })
        .catch(() => {
          this.loading = false
        })
    },

    // handleDownloadFile () {
    //   if (this.details.file_org_name === '') {
    //     this.$message.info('没有可以下载的文件')
    //     return true
    //   }
    //   const param = {
    //     id: this.details.id,
    //     file_org_name: this.details.file_org_name
    //   }
    //   apiDownloadFile(param)
    //     .then((res) => {
    //       const { data, headers } = res

    //       const str = headers['content-type']
    //       if (str.indexOf('json') !== -1) {
    //         this.$message.warning('没有权限')
    //       } else {
    //         // 下载文件
    //         const blob = new Blob([data], { type: headers['content-type'] })
    //         const dom = document.createElement('a')
    //         const url = window.URL.createObjectURL(blob)
    //         dom.href = url
    //         const filename = headers['content-disposition'].split(';')[1].split('=')[1]
    //         dom.download = decodeURI(filename)
    //         dom.style.display = 'none'
    //         document.body.appendChild(dom)
    //         dom.click()
    //         dom.parentNode.removeChild(dom)
    //         window.URL.revokeObjectURL(url)

    //         this.$message.info('文件已下载')
    //       }
    //     })
    //     .catch(() => {
    //       this.$message.info('文件下载失败')
    //     })
    // },

    handleExit () {
      this.$router.back()
    },

    handlePreviewFile (record) {
      console.log('handlePreviewFile: ', record)
      if (record.file_org_name === '') {
        this.$message.info('没有可预览的文件')
        return true
      }
      if (CONFIG.allowedPreviewFileTypes.includes(record.file_ext) === false) {
        this.$message.info('不支持预览文件类型：' + record.file_ext)
        return true
      }
      //
      const id = record.id
      const file = record.file_org_name
      this.$router.push({ path: `/party_branch/file_preview/${id}/${file}` })
    },

    handleDownloadFile (record) {
      console.log('handleDownloadFile: ', record)
      if (record.file_org_name === '') {
        this.$message.info('没有可以下载的文件')
        return true
      }
      const params = {
        id: record.id,
        file_org_name: record.file_org_name
      }
      apiDownloadFile(params)
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

    handleDeleteFile (record) {
      console.log('handleDeleteFile: ', record)
      this.$emit('delete', record)
    }
  }
}
</script>

<style lang="less" scoped>
    /deep/ .ant-descriptions-item-label {
      // color: #1890ff;
      // color: rgba(0, 0, 0, 0.85);
      font-weight: 700;
    }

</style>
