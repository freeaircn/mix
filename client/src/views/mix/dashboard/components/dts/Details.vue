<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" title="进度" :loading="loading" :body-style="{marginBottom: '8px', marginRight: '100px'}">
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
      <a-descriptions v-if="details.cause_name != ''" title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="原因">{{ details.cause_name }}</a-descriptions-item>
        <a-descriptions-item label="原因分析">{{ details.cause_analysis }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions v-if="details.score != '0'" title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="评分">{{ details.score }}</a-descriptions-item>
        <a-descriptions-item label="评分人">{{ details.scored_by }}</a-descriptions-item>
        <a-descriptions-item label="评分说明">{{ details.score_desc }}</a-descriptions-item>
      </a-descriptions>

      <div style="margin-bottom: 8px">描述:</div>
      <div style="width: 100%">
        <a-textarea id="textarea_id" v-model="details.description" :defaultValue="details.description" :rows="9" readOnly/>
      </div>

      <div v-if="(details.progress.length !== 0)" style="margin-top: 8px; margin-bottom: 8px">进展:</div>
      <div v-if="(details.progress.length !== 0)" style="width: 100%" >
        <a-textarea
          id="textarea_id"
          v-model="details.progress"
          :rows="8"
          readOnly/>
      </div>

      <div style="margin-top: 8px; margin-bottom: 8px">附件:</div>
      <div style="width: 380px">
        <a-upload
          :accept="acceptFileTypes"
          action="/api/dts/attachment"
          :data="{dts_id: dts_id, station_id: details.station_id}"
          :before-upload="beforeUploadAttachment"
          :showUploadList="{ showDownloadIcon: true, showRemoveIcon: showRmvAttachmentIcon }"
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
      <!-- <router-link slot="extra" to="/dashboard/dts/list">返回</router-link> -->
      <a-button v-if="operation.allowUpdateProgress" type="primary" @click="reqUpdateProgress" style="margin-right: 16px">更新进展</a-button>
      <a-button v-if="operation.allowResolve" type="primary" @click="reqToResolve" style="margin-top: 8px; margin-right: 16px">提交解决</a-button>
      <a-button v-if="operation.allowClose" type="primary" @click="reqToClose" style="margin-top: 8px; margin-right: 16px">提交关闭</a-button>
      <a-button v-if="operation.allowBackWork" type="primary" @click="reqBackWork" style="margin-top: 8px; margin-right: 16px">返回处理</a-button>
      <a-button v-if="operation.allowSuspend" type="default" @click="reqToSuspend" style="margin-top: 8px; margin-right: 16px">挂起</a-button>
      <a-button v-if="operation.allowCancel" type="default" @click="reqToCancel" style="margin-top: 8px; margin-right: 16px">取消</a-button>
      <a-button v-if="operation.allowScore" type="default" @click="reqUpdateScore" style="margin-top: 8px; margin-right: 16px">提交评分</a-button>
      <div style="margin-top: 8px">
        <a-button type="default" @click="handleExit" style="margin-right: 16px">退出</a-button>
      </div>
    </a-card>

    <my-form :visible.sync="visibleNewProgressDiag" title="新进展" :record="newProgress" @confirm="handleUpdateProgress">
    </my-form>
    <my-form :visible.sync="visibleSuspendDiag" title="挂起" :record="suspendProgress" @confirm="handleToSuspend">
    </my-form>
    <my-form :visible.sync="visibleCancelDiag" title="取消" :record="cancelProgress" @confirm="handleToCancel">
    </my-form>
    <resolve-form :visible.sync="visibleResolveDiag" title="解决" :causes="causes" :record="resolveInfo" @confirm="handleToResolve">
    </resolve-form>
    <my-form :visible.sync="visibleBackWorkDiag" title="返回处理" :record="backWorkProgress" @confirm="handleBackWork">
    </my-form>
    <my-form :visible.sync="visibleCloseDiag" title="关闭" :record="closeProgress" @confirm="handleToClose">
    </my-form>

    <score-form :visible.sync="visibleScoreDiag" title="评分" :record="scoreRecord" @confirm="handleUpdateScore">
    </score-form>

  </page-header-wrapper>
</template>

<script>
import myConfig from '@/config/myConfig'
import MyForm from './Form'
import ScoreForm from './ScoreForm'
import ResolveForm from './ResolveForm'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { queryDts, delAttachment, downloadAttachment, updateDts } from '@/api/mix/dts'

export default {
  name: 'DtsTicketDetails',
  components: {
    MyForm,
    ScoreForm,
    ResolveForm
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
        progress: '',
        score: '',
        score_desc: '',
        scored_by: '',
        cause: '',
        cause_name: '',
        cause_analysis: ''
      },
      progressTemplates: {},
      causes: [],
      //
      operation: {
        allowUpdateProgress: false,
        allowSuspend: false,
        allowCancel: false,
        allowScore: false,
        allowResolve: false,
        allowClose: false,
        allowBackWork: false,
        showRmvAttachmentIcon: false
      },
      visibleNewProgressDiag: false,
      newProgress: { text: '' },
      visibleSuspendDiag: false,
      suspendProgress: { text: '' },
      visibleCancelDiag: false,
      cancelProgress: { text: '' },
      //
      visibleResolveDiag: false,
      resolveInfo: {
        text: '',
        cause: '',
        cause_analysis: ''
      },
      //
      visibleBackWorkDiag: false,
      backWorkProgress: { text: '' },
      visibleCloseDiag: false,
      closeProgress: { text: '' },
      //
      acceptFileTypes: '',
      fileNumber: 0,
      fileList: [],
      //
      visibleScoreDiag: false,
      scoreRecord: { score: '0', score_desc: '' }
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ]),
    disableUploadBtn: function () {
      return this.fileNumber >= myConfig.dtsAttachmentMaxNumber
    },
    showRmvAttachmentIcon: function () {
      return this.operation.showRmvAttachmentIcon
    }
  },
  created () {
    this.dts_id = (this.$route.params.id) ? this.$route.params.id : '0'
    myConfig.dtsAttachmentFileTypes.forEach((item) => {
      this.acceptFileTypes = this.acceptFileTypes + item + ', '
    })
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
          this.progressTemplates = data.progressTemplates
          this.operation = { ...data.operation }
          this.fileList = data.attachments
          this.fileNumber = data.attachments.length
          //
          this.causes = data.causes
          this.causes.forEach((item) => {
            if (item.id === this.details.cause) {
              this.details.cause_name = item.name
            }
          })
          //
          this.loading = false
        })
        .catch(() => {
          this.details.cause_name = ''
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

        if (myConfig.dtsAttachmentFileTypes.indexOf(file.type) === -1) {
          this.$message.error('允许文件类型: jpg, png, txt, pdf, doc, docx, xls, xlsx, ppt, pptx, zip')
          // eslint-disable-next-line prefer-promise-reject-errors
          return reject(false)
        }

        if (file.size === 0) {
          this.$message.error('上传空文件')
          // eslint-disable-next-line prefer-promise-reject-errors
          return reject(false)
        }

        if (file.size > myConfig.dtsAttachmentMaxSize) {
          this.$message.error('附件大小超限')
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
      if (file.status !== 'done') {
        this.fileNumber = this.fileNumber - 1
        return true
      }

      if (file.status === 'done') {
        this.$confirm({
          title: '确定删除附件?',
          content: file.name,
          onOk: () => {
            const param = { id: file.response.id, dts_id: this.dts_id }
            return delAttachment(param)
              .then(() => {
                const index = this.fileList.indexOf(file)
                const newFileList = this.fileList.slice()
                newFileList.splice(index, 1)
                this.fileList = newFileList
                this.fileNumber = this.fileNumber - 1
                return true
              })
              .catch(() => {
                return false
              })
          }
        })
        return false
      } else {
        return true
      }
    },

    onClickDownloadAttachment (file) {
      const param = { id: file.response.id, dts_id: this.dts_id }
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

    reqUpdateProgress () {
      this.newProgress.text = this.progressTemplates.update_progress
      this.visibleNewProgressDiag = true
    },

    handleUpdateProgress (record) {
      const data = {
        resource: 'progress',
        dts_id: this.dts_id,
        progress: record.text
      }
      updateDts(data)
        .then(() => {
          // window.scroll(0, 0)
          this.onQueryDetails()
        })
        .catch(() => {
        })
    },

    reqUpdateScore () {
      this.scoreRecord.score = this.details.score
      this.scoreRecord.score_desc = this.details.score_desc
      this.visibleScoreDiag = true
    },

    handleUpdateScore (record) {
      const data = {
        resource: 'score',
        dts_id: this.dts_id,
        score: record.score,
        score_desc: record.score_desc
      }
      updateDts(data)
        .then(() => {
          this.onQueryDetails()
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    reqToResolve () {
      this.resolveInfo.text = this.progressTemplates.to_resolve
      if (this.details.cause !== '0') {
        this.resolveInfo.cause = this.details.cause
      }
      this.resolveInfo.cause_analysis = this.details.cause_analysis
      this.visibleResolveDiag = true
    },

    handleToResolve (record) {
      const data = {
        resource: 'to_resolve',
        dts_id: this.dts_id,
        progress: record.text,
        cause: record.cause,
        cause_analysis: record.cause_analysis
      }
      updateDts(data)
        .then(() => {
          this.onQueryDetails()
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    reqToSuspend () {
      this.suspendProgress.text = this.progressTemplates.to_suspend
      this.visibleSuspendDiag = true
    },

    handleToSuspend (record) {
      const data = {
        resource: 'to_suspend',
        dts_id: this.dts_id,
        progress: record.text
      }
      updateDts(data)
        .then(() => {
          this.onQueryDetails()
        })
        .catch(() => {
        })
    },

    reqToCancel () {
      this.cancelProgress.text = this.progressTemplates.to_cancel
      this.visibleCancelDiag = true
    },

    handleToCancel (record) {
      const data = {
        resource: 'to_cancel',
        dts_id: this.dts_id,
        progress: record.text
      }
      updateDts(data)
        .then(() => {
          this.onQueryDetails()
        })
        .catch(() => {
        })
    },

    reqBackWork () {
      this.backWorkProgress.text = this.progressTemplates.back_work
      this.visibleBackWorkDiag = true
    },

    handleBackWork (record) {
      const data = {
        resource: 'back_work',
        dts_id: this.dts_id,
        progress: record.text
      }
      updateDts(data)
        .then(() => {
          this.onQueryDetails()
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    reqToClose () {
      this.closeProgress.text = this.progressTemplates.to_close
      this.visibleCloseDiag = true
    },

    handleToClose (record) {
      const data = {
        resource: 'to_close',
        dts_id: this.dts_id,
        progress: record.text
      }
      updateDts(data)
        .then(() => {
          this.onQueryDetails()
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    handleExit () {
      this.$router.back()
    }
  }
}
</script>
