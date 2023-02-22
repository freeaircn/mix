<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-22 23:26:29
-->
<template>
  <page-header-wrapper :title="false">
    <a-card title="修改" :bordered="false" :body-style="{marginBottom: '8px'}" >
      <a-form-model
        ref="form"
        :model="record"
        :rules="rules"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="站点" prop="station_id">
          <a-select v-model="record.station_id" disabled >
            <a-select-option v-for="d in stationItems" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="图名" prop="dwg_name">
          <a-input v-model="record.dwg_name"></a-input>
        </a-form-model-item>
        <a-form-model-item label="类别" prop="category_id">
          <a-select v-model="record.category_id" placeholder="请选择" >
            <a-select-option v-for="d in categoryItems" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="关键词" prop="keywords">
          <a-input v-model="record.keywords" placeholder="多个词之间用逗号隔开"></a-input>
        </a-form-model-item>
        <a-form-model-item label="补充信息" prop="info">
          <a-textarea v-model="record.info" :rows="4" />
        </a-form-model-item>

        <a-form-model-item label="图纸" prop="file">
          <a-upload
            :accept="allowedFileTypes"
            :action="config.uploadUrl"
            :data="{key: 'saved'}"
            :before-upload="beforeUpload"
            :showUploadList="{ showDownloadIcon: true, showRemoveIcon: true }"
            :fileList="fileList"
            :remove="handleDeleteFile"
            @change="afterUpload"
            @download="handleDownloadFile"
          >
            <a-button :disabled="!ready || disableUploadBtn"> <a-icon type="upload" /> 点击上传 </a-button>
          </a-upload>
        </a-form-model-item>

        <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
          <a-button type="primary" @click="onSubmit" :disabled="!ready" >确认</a-button>
          <!-- <router-link slot="extra" to="/dashboard/dts"><a-button>取消</a-button></router-link> -->
          <a-button style="margin-left: 16px" @click="onCancel">取消</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import myConfig from '@/config/myConfig'
import * as pattern from '@/utils/validateRegex'
import { mapGetters } from 'vuex'
//
import { apiQuery, apiUpdate, apiDeleteFile, apiDownloadFile } from '@/api/mix/drawing'

export default {
  name: 'EditForm',
  data () {
    return {
      labelCol: {
        lg: { span: 7 }, sm: { span: 7 }
      },
      wrapperCol: {
        lg: { span: 10 }, sm: { span: 17 }
      },
      //
      ready: false,
      stationItems: [],
      categoryItems: [],
      //
      id: '0',
      record: {
        id: '',
        station_id: '',
        dwg_name: '',
        category_id: '',
        keywords: '',
        info: ''
      },
      //
      rules: {
        station_id: [{ required: true, message: '请选择', trigger: ['change'] }],
        category_id: [{ required: true, message: '请选择', trigger: ['change'] }],
        dwg_name: [
          { required: true, message: '请输入', trigger: ['change'] },
          { pattern: pattern.englishChineseNum__.regex, message: pattern.englishChineseNum__.msg, trigger: ['change'] }
        ],
        keywords: [
          { required: true, message: '请输入', trigger: ['change'] },
          { pattern: pattern.englishChineseNumComma.regex, message: pattern.englishChineseNumComma.msg, trigger: ['change'] }
        ],
        info: [
          { pattern: pattern.englishChineseNumPunctuation.regex, message: pattern.englishChineseNumPunctuation.msg, trigger: ['change'] }
        ]
      },
      //
      config: myConfig.drawing,
      allowedFileTypes: '',
      fileNumber: 0,
      fileList: []
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ]),
    disableUploadBtn: function () {
      return this.fileNumber >= this.config.maxFileNumber
    }
  },
  created () {
    this.id = (this.$route.params.id) ? this.$route.params.id : '0'
  },
  mounted () {
    this.handleQueryDetails()
  },
  methods: {

    handleQueryDetails () {
      if (this.id === '0') {
        return
      }
      const params = { resource: 'edit', id: this.id }
      apiQuery(params)
        .then((data) => {
          Object.assign(this.record, data.record)
          //
          this.stationItems = data.stationItems
          this.categoryItems = data.categoryItems
          //
          this.fileList = data.files
          this.fileNumber = data.files.length
          //
          this.ready = true
        })
        .catch(() => {
          this.record.station_id = ''
          this.record.dwg_name = ''
          this.record.category_id = ''
          this.record.keywords = ''
          this.record.info = ''
          //
          this.ready = false
        })
    },

    beforeUpload (file) {
      return new Promise((resolve, reject) => {
        let conflict = false
        this.fileList.forEach(f => {
          if (file.name === f.name) {
            conflict = true
          }
        })
        if (conflict) {
          this.$message.error('已上传同名文件')
          // eslint-disable-next-line prefer-promise-reject-errors
          return reject(false)
        }

        if (this.config.allowedFileTypes.indexOf(file.type) === -1) {
          this.$message.error('允许文件类型: pdf, zip')
          // eslint-disable-next-line prefer-promise-reject-errors
          return reject(false)
        }

        if (file.size === 0) {
          this.$message.error('空文件')
          // eslint-disable-next-line prefer-promise-reject-errors
          return reject(false)
        }

        if (file.size > this.config.maxFileSize) {
          this.$message.error('文件大小超过 100MB')
          // eslint-disable-next-line prefer-promise-reject-errors
          return reject(false)
        }

        return resolve(true)
      })
    },

    afterUpload (info) {
      if (info.file.status === 'error' || info.file.status === 'done') {
        this.fileNumber = this.fileNumber + 1
        this.fileList = info.fileList
      }
      if (info.file.status === 'done') {
        this.$message.success('文件上传成功')
      } else if (info.file.status === 'error') {
        if (info.file.response.hasOwnProperty('messages')) {
          this.$message.error(info.file.response.messages.error)
        } else {
          this.$message.error('文件上传失败')
        }
      }
    },

    handleDeleteFile (file) {
      const index = this.fileList.indexOf(file)
      const newFileList = this.fileList.slice()
      newFileList.splice(index, 1)
      this.fileList = newFileList
      this.fileNumber = this.fileNumber - 1
      if (file.status === 'done') {
        return apiDeleteFile({ key: 'unsaved', id: file.response.id })
          .then(() => {
            return true
          })
          .catch(() => {
            return false
          })
      } else {
        return true
      }
    },

    handleDownloadFile () {
      const param = {
        id: this.record.id,
        file_org_name: this.record.file_org_name
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

    onSubmit () {
      this.$refs.form.validate(valid => {
        if (valid) {
          var data = { ...this.record }
          apiUpdate(data)
            .then(() => {
              this.$router.back()
            })
            .catch(() => {
            })
        } else {
          return false
        }
      })
    },

    onCancel () {
      // this.$router.push({ path: `/dashboard/dts/list` })
      this.$router.back()
    }
  }
}
</script>
