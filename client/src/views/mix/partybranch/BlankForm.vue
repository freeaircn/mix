<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-17 00:34:33
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
        <a-form-model-item label="标题" prop="title">
          <a-input v-model="record.title" placeholder="请输入"></a-input>
        </a-form-model-item>
        <a-form-model-item label="类别" prop="category">
          <a-cascader
            :options="categoryItems"
            v-model="record.category"
            :allowClear="true"
            expand-trigger="hover"
            change-on-select
            :fieldNames="{ label: 'name', value: 'id', children: 'children' }"
            placeholder="请选择"
          />
        </a-form-model-item>
        <a-form-model-item label="关键词" prop="keywords">
          <a-input v-model="record.keywords" placeholder="多个关键词用逗号分隔"></a-input>
        </a-form-model-item>
        <a-form-model-item label="密级" prop="secret_level">
          <a-select v-model="record.secret_level" allowClear placeholder="请选择" >
            <a-select-option v-for="d in secretLevelItems" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="保存期限" prop="retention_period">
          <a-select v-model="record.retention_period" allowClear placeholder="请选择" >
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
          <a-upload
            :accept="allowedFileTypes"
            :fileList="fileList"
            :before-upload="beforeUpload"
            :showUploadList="{ showDownloadIcon: false, showRemoveIcon: true }"
            :remove="handleRemoveFile"
          >
            <a-button
              :disabled="fileList.length >= config.maxFileNumber" >
              <a-icon type="file" /> 添加文件
            </a-button>
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
import * as pattern from '@/utils/validateRegex'
import { mapGetters } from 'vuex'
//
import { partyBranch as CONFIG } from '@/config/myConfig'
import { apiQuery, apiCreate, apiUpload } from '@/api/mix/party_branch'

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
      secretLevelItems: [],
      retentionPeriodItems: [],
      //
      record: {
        station_id: '',
        category_id: '',
        category: [],
        title: '',
        keywords: '',
        secret_level: undefined,
        retention_period: undefined,
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
  created: function () {
    this.ready = false
    CONFIG.allowedFileTypes.forEach((item) => {
      this.allowedFileTypes = this.allowedFileTypes + item + ', '
    })
    this.prepareBlankForm()
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  methods: {
    prepareBlankForm () {
      apiQuery({ resource: 'blank_form' })
        .then((data) => {
            this.stationItems = data.stationItems
            this.categoryItems = data.categoryItems
            this.secretLevelItems = data.secretLevelItems
            this.retentionPeriodItems = data.retentionPeriodItems
            this.record.station_id = this.userInfo.allowDefaultDeptId
            //
            this.ready = true
          })
          .catch(() => {
            this.record.info = ''
            this.ready = false
          })
    },

    // 添加文件
    beforeUpload (file) {
      let conflict = false
      this.fileList.forEach(f => {
        if (file.name === f.name) {
          conflict = true
        }
      })
      if (conflict) {
        this.$message.error('重复添加文件')
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

      // 注意
      this.fileList = [...this.fileList, file]
      return false
    },

    handleRemoveFile (file) {
      const index = this.fileList.indexOf(file)
      const newFileList = this.fileList.slice()
      newFileList.splice(index, 1)
      this.fileList = newFileList
    },

    handleSubmit2 () {
      const { fileList } = this
      var formData = new FormData()
      fileList.forEach(file => {
        formData.append('files[]', file)
      })
      formData.append('my_id', 1)
      formData.append('my_key', 'create')
      apiUpload(formData)
        .then(() => {
          // this.$router.push({ path: `/dashboard/drawing/list` })
          this.$router.back()
        })
        .catch(() => {
          // this.ready = false
        })
    },

    handleSubmit () {
      this.$refs.form.validate(valid => {
        if (valid) {
          var data = { ...this.record }
          data.title = data.title.trim()
          data.keywords = data.keywords.trim()
          data.category_id = this.record.category.pop()
          //
          console.log('data', data)
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
      this.$router.back()
    }
  }
}
</script>
