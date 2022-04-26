<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" title="进度" :loading="loading" :body-style="{marginBottom: '8px', marginRight: '100px'}">
      <router-link slot="extra" to="/dashboard/dts/list">返回</router-link>

      <a-steps size="small" :current="steps.current">
        <a-step v-for="(item, j) in steps.step" :key="j" :title="item.title" >
          <a-icon slot="icon" v-if="item.icon" type="loading" />
          <template v-slot:description>
            <div v-for="(d, i) in item.description" :key="i">{{ d }}</div>
          </template>
        </a-step>
      </a-steps>
    </a-card>

    <a-card :bordered="false" title="详情" :loading="loading" :body-style="{marginBottom: '8px'}">
      <a slot="extra" @click="onQueryDetails">刷新</a>

      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="单号">{{ details.dts_id }}</a-descriptions-item>
        <a-descriptions-item label="类别">{{ details.type }}</a-descriptions-item>
        <a-descriptions-item label="影响程度">{{ details.level }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="标题">{{ details.title }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="站点">{{ details.station }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="设备">{{ details.device }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="创建时间">{{ details.created_at }}</a-descriptions-item>
        <a-descriptions-item label="更新时间">{{ details.updated_at }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="创建">{{ details.creator }}</a-descriptions-item>
        <a-descriptions-item label="审核" v-if="details.reviewer.length !== 0">{{ details.reviewer }}</a-descriptions-item>
      </a-descriptions>

      <div style="margin-bottom: 8px">描述:</div>
      <div style="width: 100%">
        <a-textarea id="textarea_id" v-model="details.description" :defaultValue="details.description" :rows="9" readOnly/>
      </div>

      <div v-if="(details.progress != null)" style="margin-top: 8px; margin-bottom: 8px">进展:</div>
      <div v-if="(details.progress != null)" style="width: 100%" >
        <a-textarea
          id="textarea_id"
          v-model="details.progress"
          :rows="8"
          readOnly/>
      </div>

      <div style="margin-top: 8px; margin-bottom: 8px">附件:</div>
      <div style="width: 380px">
        <a-upload
          accept="text/plain, image/jpeg, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-powerpoint, application/vnd.openxmlformats-officedocument.presentationml.presentation, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, application/pdf, application/zip"
          action="/api/dts/attachment"
          :data="{dts_id: dts_id}"
          :before-upload="beforeUploadAttachment"
          :showUploadList="{ showDownloadIcon: true, showRemoveIcon: true }"
          :fileList="fileList"
          :remove="onClickDelAttachment"
          @change="afterUploadAttachment"
          @download="onClickDownloadAttachment"
        >
          <a-button :disabled="disableUploadBtn"> <a-icon type="upload" /> 点击上传 </a-button>
        </a-upload>
      </div>
    </a-card>

    <a-card :bordered="false" title="操作" :loading="loading" :body-style="{marginBottom: '8px'}">
      <router-link slot="extra" to="/dashboard/dts/list">返回</router-link>
      <a-button v-if="operation.allowUpdateProgress" type="primary" @click="reqUpdateProgress" style="margin-top: 8px; margin-right: 16px">更新进展</a-button>
    </a-card>

    <my-form :visible.sync="visibleNewProgressDiag" title="新进展" :record="newProgress" @confirm="handleUpdateProgress">
    </my-form>

  </page-header-wrapper>
</template>

<script>
import myConfig from '@/config/mix_config'
import MyForm from './Form'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { queryDts, delAttachment, putProgress } from '@/api/mix/dts'

export default {
  name: 'DtsTicketDetails',
  components: {
    MyForm
  },
  mixins: [baseMixin],
  data () {
    return {
      dts_id: '0',
      loading: false,
      steps: {
        current: 0
      },
      details: {
        dts_id: '',
        type: '',
        level: '',
        place_at: '',
        title: '',
        device: '',
        creator: '',
        reviewer: '',
        created_at: '',
        updated_at: '',
        description: '',
        progress: ''
      },
      newProgressTpl: '',
      //
      operation: {
        allowUpdateProgress: false
      },
      // activeKey: ['1'],
      visibleNewProgressDiag: false,
      newProgress: { content: '' },
      //
      fileNumber: 0,
      fileList: []
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ]),
    disableUploadBtn: function () {
      return this.fileNumber >= myConfig.DtsAttachmentMaxNumber
    }
  },
  created () {
    this.dts_id = (this.$route.params.id) ? this.$route.params.id : '0'
  },
  mounted () {
    this.onQueryDetails()
  },
  methods: {
    // 查询
    onQueryDetails () {
      if (this.dts_id === '0') {
        return
      }
      this.loading = true
      const params = { resource: 'details', dts_id: this.dts_id }
      queryDts(params)
        .then(data => {
          Object.assign(this.details, data.details)
          this.steps = data.steps
          this.newProgressTpl = data.newProgressTpl
          this.operation = { ...data.operation }
          this.fileList = data.attachments
          this.fileNumber = data.attachments.length
          this.loading = false
        })
        .catch(() => {
          this.loading = false
        })
    },

    // 附件
    beforeUploadAttachment (file) {
      return new Promise((resolve, reject) => {
        let conflict = false
        this.fileList.forEach(f => {
          if (file.name === f.name) {
            conflict = true
          }
        })
        if (conflict) {
          this.$message.error('已上传相同文件名的附件')
          // eslint-disable-next-line prefer-promise-reject-errors
          return reject(false)
        }

        let fileType = file.type === 'text/plain' || file.type === 'image/jpeg' || file.type === 'image/png'
        fileType = fileType || file.type === 'application/msword' || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        fileType = fileType || file.type === 'application/vnd.ms-powerpoint' || file.type === 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        fileType = fileType || file.type === 'application/vnd.ms-excel' || file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        fileType = fileType || file.type === 'application/pdf' || file.type === 'application/zip'
        if (!fileType) {
          this.$message.error('允许文件类型: jpg, png, txt, pdf, doc, docx, xls, xlsx, ppt, pptx, zip')
          // eslint-disable-next-line prefer-promise-reject-errors
          return reject(false)
        }

        if (file.size > myConfig.DtsAttachmentMaxSize) {
          this.$message.error('文件大小超限')
          // eslint-disable-next-line prefer-promise-reject-errors
          return reject(false)
        }

        return resolve(true)
      })
    },

    afterUploadAttachment (info) {
      const fileList = [...info.fileList]
      this.fileList = fileList
      if (info.file.status === 'error' || info.file.status === 'done') {
        this.fileNumber = this.fileNumber + 1
      }
      if (info.file.status === 'done') {
        this.$message.success('附件上传成功')
      } else if (info.file.status === 'error') {
        if (info.file.response.hasOwnProperty('messages')) {
          this.$message.error(info.file.response.messages.error)
        } else {
          this.$message.error('附件上传失败')
        }
      }
    },

    onClickDelAttachment (file) {
      const index = this.fileList.indexOf(file)
      const newFileList = this.fileList.slice()
      newFileList.splice(index, 1)
      this.fileList = newFileList
      this.fileNumber = this.fileNumber - 1
      if (file.status === 'done') {
        const param = { id: file.response.id, dts_id: this.dts_id }
        return delAttachment(param)
          .then(() => {
            return true
          })
          .catch(() => {
            return true
          })
      } else {
        return true
      }
    },

    onClickDownloadAttachment () {

    },

    reqUpdateProgress () {
      this.newProgress.content = this.newProgressTpl
      this.visibleNewProgressDiag = true
    },

    handleUpdateProgress (record) {
      const progress = {
            dts_id: this.dts_id,
            progress: record.content
          }
          putProgress(progress)
            .then(() => {
              // this.operation.newProgress = this.newProgressTpl
              // window.scroll(0, 0)
              this.onQueryDetails()
            })
            .catch((err) => {
              if (err.response) {
              }
            })
    },

    handleToResolve () {
      this.$confirm({
        title: '问题解决了吗？',
        onOk: () => {
          console.log('OK')
        }
      })
    },

    handleToClose () {
      this.$confirm({
        title: '你想要关闭问题单吗？',
        onOk: () => {
          console.log('OK')
        }
      })
    }

  }
}
</script>
