<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-14 15:53:46
-->
<template>
  <page-header-wrapper :title="false">
    <a-card title="新问题单" :bordered="false" :body-style="{marginBottom: '8px'}" >
      <a-form-model
        ref="form"
        :model="record"
        :rules="rules"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="站点" prop="station_id">
          <a-select v-model="record.station_id" @change="onStationSelectChange" placeholder="请选择" >
            <a-select-option v-for="d in stationItems" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="类别" prop="type">
          <a-radio-group name="radioGroup1" v-model="record.type">
            <a-radio value="1">隐患</a-radio>
            <a-radio value="2">缺陷</a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item label="标题" prop="title">
          <a-input v-model="record.title"></a-input>
        </a-form-model-item>
        <a-form-model-item label="影响等级" prop="level">
          <a-select v-model="record.level" placeholder="请选择" >
            <a-select-option value="1">紧急</a-select-option>
            <a-select-option value="2">严重</a-select-option>
            <a-select-option value="3">一般</a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="设备" prop="device">
          <a-cascader
            :options="deviceItems"
            v-model="device"
            :allowClear="true"
            expand-trigger="hover"
            change-on-select
            :fieldNames="{ label: 'name', value: 'id', children: 'children' }"
            placeholder="请选择"
          />
        </a-form-model-item>

        <a-form-model-item label="描述" prop="description">
          <a-textarea v-model="record.description" :rows="10" />
        </a-form-model-item>

        <a-form-model-item label="附件" prop="attachment">
          <a-upload
            accept="text/plain, image/jpeg, image/png, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-powerpoint, application/vnd.openxmlformats-officedocument.presentationml.presentation, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, application/pdf, application/zip"
            action="/api/dts/ticket/upload/attachment"
            :before-upload="beforeUploadAttachment"
            :data="uploadAttachmentParam"
            :file-list="attachmentList"
            :showUploadList="{ showDownloadIcon: true, showRemoveIcon: true }"
            @download="onClickAttachment"
          >
            <a-button> <a-icon type="upload" /> 点击上传 </a-button>
          </a-upload>
        </a-form-model-item>

        <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
          <a-button type="primary" @click="onSubmit" :disabled="!ready" >提交</a-button>
          <!-- <router-link slot="extra" to="/dashboard/dts"><a-button>取消</a-button></router-link> -->
          <a-button style="margin-left: 16px" @click="onCancel">取消</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { getBlankForm, getDeviceList, postDraft } from '@/api/mix/dts'
import { listToTree } from '@/utils/util'

export default {
  name: 'DtsBlankForm',
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
      deviceItems: [],
      device: [],
      //
      record: {
        station_id: '',
        description: ''
      },
      uploadAttachmentParam: { uid: '666' },
      attachmentList: [],
      rules: {
        station_id: [{ required: true, message: '请选择', trigger: ['change'] }],
        type: [{ required: true, message: '请选择', trigger: ['change'] }],
        title: [{ required: true, message: '请输入', trigger: ['change'] }],
        level: [{ required: true, message: '请选择', trigger: ['change'] }]
        // device: [{ required: true, message: '请选择', trigger: ['change'] }]
      }
    }
  },
  created: function () {
    this.ready = false
    this.loadBlankForm()
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  methods: {
    loadBlankForm () {
      getBlankForm()
        .then((data) => {
            this.record.description = data.description
            this.stationItems = data.station
            listToTree(data.deviceList, this.deviceItems, this.userInfo.allowDefaultDeptId)
            this.record.station_id = this.userInfo.allowDefaultDeptId
            this.ready = true
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            this.deviceItems.splice(0, this.deviceItems.length)
            this.record.description = ''
            this.ready = false
            if (err.response) {
            }
          })
    },

    onStationSelectChange (value) {
      const query = { station_id: value }
      getDeviceList(query)
        .then((data) => {
            this.deviceItems.splice(0, this.deviceItems.length)
            listToTree(data.deviceList, this.deviceItems, value)
            this.ready = true
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            this.deviceItems.splice(0, this.deviceItems.length)
            this.ready = false
            if (err.response) {
            }
          })
    },

    // 附件
    beforeUploadAttachment (file) {
      console.log(file)
      let fileType = file.type === 'text/plain' || file.type === 'image/jpeg' || file.type === 'image/png'
      fileType = fileType || file.type === 'application/msword' || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
      fileType = fileType || file.type === 'application/vnd.ms-powerpoint' || file.type === 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
      fileType = fileType || file.type === 'application/vnd.ms-excel' || file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      fileType = fileType || file.type === 'application/pdf' || file.type === 'application/zip'
      if (!fileType) {
        this.$message.error('允许的文件类型: jpg, png, txt, pdf, doc, docx, xls, xlsx, ppt, pptx, zip')
      }
      //
      const fileSize = file.size / 1024 / 1024 < 2
      if (!fileSize) {
        this.$message.error('允许的文件大小: 小于2MB')
      }
      return fileType && fileSize
    },

    onClickAttachment (file) {
      console.log(file)
    },

    onSubmit () {
      this.$refs.form.validate(valid => {
        if (valid) {
          let temp = '+'
          this.device.forEach(element => {
            temp = temp + element + '+'
          })
          //
          const data = { ...this.record }
          data.device = temp

          postDraft(data)
            .then(() => {
              this.$router.push({ path: `/dashboard/dts/list` })
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              this.ready = false
              if (err.response) {
              }
            })
        } else {
          return false
        }
      })
    },

    onCancel () {
      this.$router.push({ path: `/dashboard/dts/list` })
    }
  }
}
</script>
