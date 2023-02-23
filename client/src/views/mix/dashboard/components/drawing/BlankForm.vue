<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-23 14:46:33
-->
<template>
  <page-header-wrapper :title="false">
    <a-card title="新建" :bordered="false" :body-style="{marginBottom: '8px'}" >
      <a-form-model
        ref="form"
        :model="record"
        :rules="rules"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="站点" prop="station_id">
          <a-select v-model="record.station_id" placeholder="请选择" >
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
            :data="{key: 'create', id: '0'}"
            :before-upload="beforeUpload"
            :showUploadList="{ showDownloadIcon: false, showRemoveIcon: true }"
            :remove="handleDeleteFile"
            @change="afterUpload"
          >
            <a-button :disabled="!ready || disableUploadBtn"> <a-icon type="upload" /> 点击上传 </a-button>
          </a-upload>
        </a-form-model-item>

        <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
          <a-button type="primary" @click="handleSubmit" :disabled="!ready" >提交</a-button>
          <a-button style="margin-left: 16px" @click="handleCancel">取消</a-button>
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
import { apiQuery, apiDeleteFile, apiCreate } from '@/api/mix/drawing'

export default {
  name: 'BlankForm',
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
      record: {
        station_id: '',
        dwg_name: '',
        category_id: '',
        keywords: '',
        info: ''
      },
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
  created: function () {
    this.ready = false
    this.fileNumber = 0
    this.config.allowedFileTypes.forEach((item) => {
      this.allowedFileTypes = this.allowedFileTypes + item + ', '
    })
    this.prepareBlankForm()
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ]),
    disableUploadBtn: function () {
      return this.fileNumber >= this.config.maxFileNumber
    }
  },
  methods: {
    prepareBlankForm () {
      apiQuery({ resource: 'blank_form' })
        .then((data) => {
            this.stationItems = data.stationItems
            this.categoryItems = data.categoryItems
            this.record.station_id = this.userInfo.allowDefaultDeptId
            //
            this.ready = true
          })
          .catch(() => {
            this.record.info = ''
            this.ready = false
          })
    },

    // 上传文件
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
      this.fileList = [...info.fileList]
      if (info.file.status === 'error' || info.file.status === 'done') {
        this.fileNumber = this.fileNumber + 1
        // this.fileList = info.fileList
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
        return apiDeleteFile({ key: 'create', id: file.response.id, file_org_name: file.name })
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

    handleSubmit () {
      const files = []
      this.fileList.forEach(element => {
        if (element.status === 'done') {
          files.push(element.response)
        }
      })
      this.$refs.form.validate(valid => {
        if (valid) {
          var data = { ...this.record }
          data.dwg_name = this.record.dwg_name.trim()
          data.keywords = this.record.keywords.trim()
          data.files = files
          //
          var part1 = ''
          var part2 = ''
          for (var i = 0; i < this.stationItems.length; i++) {
            if (this.stationItems[i].id === data.station_id) {
              part1 = this.stationItems[i].alias
            }
          }
          for (i = 0; i < this.categoryItems.length; i++) {
            if (this.categoryItems[i].id === data.category_id) {
              part2 = this.categoryItems[i].alias
            }
          }
          if (part1 !== '' && part2 !== '') {
            data.dwg_num = part1 + '-' + part2
          } else {
            return false
          }
          //
          apiCreate(data)
            .then(() => {
              // this.$router.push({ path: `/dashboard/drawing/list` })
              this.$router.back()
            })
            .catch(() => {
              // this.ready = false
            })
        } else {
          return false
        }
      })
    },

    handleCancel () {
      if (this.fileList.length > 0) {
        this.$message.info('请删除上传的文件后，再取消')
      } else {
        // this.$router.push({ path: `/dashboard/drawing/list` })
        this.$router.back()
      }
    }
  }
}
</script>
