<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2023-04-22 15:23:15
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
          <a-select v-model="record.station_id">
            <a-select-option v-for="d in stationItems" :key="d.id" :value="d.id" :disabled="d.id !== record.station_id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="标题" prop="title">
          <a-input v-model="record.title"></a-input>
        </a-form-model-item>
        <a-form-model-item label="类别" prop="category">
          <a-cascader
            :options="categoryItems"
            v-model="record.category"
            :allowClear="true"
            expand-trigger="hover"
            change-on-select
            :displayRender="cascaderDisplayRender"
            :fieldNames="{ label: 'name', value: 'id', children: 'children' }"
            placeholder="请选择"
          />
        </a-form-model-item>
        <a-form-model-item label="关键词" prop="keywords">
          <a-input v-model="record.keywords" placeholder="多个关键词用逗号分隔"></a-input>
        </a-form-model-item>
        <a-form-model-item label="密级" prop="secret_level">
          <a-select v-model="record.secret_level">
            <a-select-option v-for="d in secretLevelItems" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="保存期限" prop="retention_period">
          <a-select v-model="record.retention_period">
            <a-select-option v-for="d in retentionPeriodItems" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="存放地点" prop="store_place">
          <a-input v-model="record.store_place" placeholder="示例：X站点Y办公楼Z档案室"></a-input>
        </a-form-model-item>
        <a-form-model-item label="概要" prop="summary">
          <a-textarea v-model="record.summary" :rows="4" />
        </a-form-model-item>

        <a-form-model-item label="文件" prop="file">
          <files-table :listData="fileList" :showDelete="true" @delete="handleDeleteFile"></files-table>
        </a-form-model-item>

        <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
          <a-button type="primary" @click="handleSubmit" :disabled="!ready" style="margin-right: 16px">提交</a-button>
          <a-upload
            :accept="allowedFileTypes"
            :action="config.uploadUrl"
            :data="{associated_id: record.uuid}"
            :before-upload="beforeUpload"
            :showUploadList="false"
            @change="afterUpload"
          >
            <a-button type="default" :disabled="fileList.length >= config.maxFileNumber" style="margin-right: 16px"> 上传文件 </a-button>
          </a-upload>
          <a-button @click="handleCancel">取消</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import { partyBranch as CONFIG } from '@/config/myConfig'
import { mapGetters } from 'vuex'
import * as pattern from '@/utils/validateRegex'
import { apiQuery, apiUpdate, apiDeleteFile } from '@/api/mix/party_branch'
import FilesTable from '@/views/mix/partybranch/components/FilesTable'

export default {
  name: 'EditForm',
  components: {
      FilesTable
  },
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
      //
      uuid: '0',
      stationItems: [],
      categoryItems: [],
      secretLevelItems: [],
      retentionPeriodItems: [],
      //
      record: {
        uuid: '0',
        station_id: '',
        category_id: '',
        category: [],
        title: '',
        keywords: '',
        secret_level: '',
        retention_period: '',
        store_place: '',
        summary: ''
      },
      category: [],
      rules: {
        station_id: [{ required: true, message: '请选择', trigger: ['change'] }],
        category: [{ required: true, message: '请选择', trigger: ['change'] }],
        title: [
          { required: true, message: '请输入', trigger: ['change'] },
          { pattern: pattern.TITLE.regex, message: pattern.TITLE.msg, trigger: ['change'] }
        ],
        keywords: [
          { required: true, message: '请输入', trigger: ['change'] },
          { pattern: pattern.KEY_WORDS.regex, message: pattern.KEY_WORDS.msg, trigger: ['change'] }
        ],
        secret_level: [{ required: true, message: '请选择', trigger: ['change'] }],
        retention_period: [{ required: true, message: '请选择', trigger: ['change'] }],
        summary: [{ pattern: pattern.TEXT.regex, message: pattern.TEXT.msg, trigger: ['change'] }]
      },
      //
      allowedFileTypes: '',
      fileList: [],
      config: CONFIG
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ]),
    disableUploadBtn: function () {
      return this.fileNumber >= CONFIG.maxFileNumber
    }
  },
  created: function () {
    this.uuid = (this.$route.params.uuid) ? this.$route.params.uuid : '0'
    CONFIG.allowedFileTypes.forEach((item) => {
      this.allowedFileTypes = this.allowedFileTypes + item + ', '
    })
    //
  },
  mounted () {
    this.handleQueryDetails(this.uuid)
  },
  methods: {
    handleQueryDetails (uuid) {
      if (uuid === '0') {
        return
      }
      const params = { resource: 'edit', uuid: this.uuid }
      apiQuery(params)
        .then(data => {
          Object.assign(this.record, data.record)
          this.stationItems = data.stationItems
          this.categoryItems = data.categoryItems
          this.secretLevelItems = data.secretLevelItems
          this.retentionPeriodItems = data.retentionPeriodItems
          //
          // this.record = data.record
          this.fileList = data.files
          //
          this.ready = true
        })
        .catch(() => {
          this.ready = false
        })
    },

    beforeUpload (file) {
      return new Promise((resolve, reject) => {
        let conflict = false
        this.fileList.forEach(f => {
          if (file.name === f.file_org_name) {
            conflict = true
          }
        })
        if (conflict) {
          this.$message.error('已经存在同名的文件')
          const error = false
          return Promise.reject(error)
        }

        if (CONFIG.allowedFileTypes.indexOf(file.type) === -1) {
          this.$message.error('允许文件类型: pdf, zip')
          const error = false
          return Promise.reject(error)
        }

        if (file.size === 0) {
          this.$message.error('空文件')
          const error = false
          return Promise.reject(error)
        }

        if (file.size > CONFIG.maxFileSize) {
          this.$message.error('文件大小超过 100MB')
          const error = false
          return Promise.reject(error)
        }

        return resolve(true)
      })
    },

    afterUpload (info) {
      if (info.file.status === 'done') {
        if (info.file.response.hasOwnProperty('msg')) {
          this.$message.success(info.file.response.msg)
        } else {
          this.$message.success('文件上传成功')
        }
        this.fileList.push(info.file.response.file)
      } else if (info.file.status === 'error') {
        if (info.file.response.hasOwnProperty('messages')) {
          this.$message.error(info.file.response.messages.error)
        } else {
          this.$message.error('文件上传失败')
        }
      }
    },

    handleDeleteFile (record) {
      if (record.file_org_name === '') {
        this.$message.info('没有可以删除的文件')
        return true
      }
      if (record.id) {
        this.$confirm({
          title: '确定删除吗?',
          content: record.file_org_name,
          onOk: () => {
            const params = {
              id: record.id,
              file_org_name: record.file_org_name
            }
            apiDeleteFile(params)
              .then(() => {
                const index = this.fileList.indexOf(record)
                const newFileList = this.fileList.slice()
                newFileList.splice(index, 1)
                this.fileList = newFileList
              })
              .catch(() => {
              })
          }
        })
      }
    },

    handleSubmit () {
      console.log('record: ', this.record)
      this.$refs.form.validate(valid => {
        if (valid) {
          var record = { ...this.record }
          record.title = record.title.trim()
          record.keywords = record.keywords.trim()
          record.category_id = record.category.pop()
          //
          // var formData = new FormData()
          // Object.keys(record).forEach((key) => {
          //   formData.append(key, record[key])
          // })
          //
          apiUpdate(record)
            .then(() => {
              this.$router.back()
            })
            .catch(() => {
              this.handleQueryDetails(this.uuid)
            })
        } else {
          return false
        }
      })
    },

    handleCancel () {
      this.$router.back()
    },

    cascaderDisplayRender ({ labels, selectedOptions }) {
      return labels[labels.length - 1]
    }
  }
}
</script>
