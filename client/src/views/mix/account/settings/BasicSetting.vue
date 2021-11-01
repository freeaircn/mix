<template>
  <div class="account-settings-info-view">
    <a-row :gutter="16" type="flex" justify="center">
      <a-col :order="isMobile ? 2 : 1" :md="24" :lg="16">

        <a-form-model
          ref="form"
          :model="record"
          :rules="rules"
          :label-col="labelCol"
          :wrapper-col="wrapperCol"
        >
          <a-form-model-item label="姓名" prop="username">
            <a-input v-model="record.username"></a-input>
          </a-form-model-item>

          <a-form-model-item label="性别" prop="sex">
            <a-radio-group name="radioGroup" v-model="record.sex">
              <a-radio value="男">男</a-radio>
              <a-radio value="女">女</a-radio>
            </a-radio-group>
          </a-form-model-item>

          <a-form-model-item label="身份证" prop="IdCard">
            <a-input v-model="record.IdCard"></a-input>
          </a-form-model-item>

          <a-form-model-item label="政治面貌" prop="politic">
            <a-select v-model="record.politic" placeholder="请选择" >
              <a-select-option v-for="d in politicOptions" :key="d.id" :value="d.id" :disabled="d.status === '0'">
                {{ d.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item label="部门" prop="department">
            <a-cascader
              :options="departmentOptions"
              v-model="record.department"
              :allowClear="true"
              expand-trigger="hover"
              change-on-select
              :fieldNames="{ label: 'name', value: 'id', children: 'children' }"
              placeholder="请选择"
            />
          </a-form-model-item>

          <a-form-model-item label="岗位" prop="job">
            <a-select v-model="record.job" placeholder="请选择" >
              <a-select-option v-for="d in jobOptions" :key="d.id" :value="d.id" :disabled="d.status === '0'">
                {{ d.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
          <a-form-model-item label="职称" prop="title">
            <a-select v-model="record.title" placeholder="请选择" >
              <a-select-option v-for="d in titleOptions" :key="d.id" :value="d.id" :disabled="d.status === '0'">
                {{ d.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>

          <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
            <a-button htmlType="submit" type="primary" @click="handleSaveUserInfo">保存</a-button>
          </a-form-model-item>
        </a-form-model>

      </a-col>
      <a-col :order="1" :md="24" :lg="8" :style="{ minHeight: '180px' }">
        <div class="ant-upload-preview" >
          <img :src="avatarImg"/>
          <div class="upload">
            <a-upload
              name="file"
              :action="uploadAction"
              :showUploadList="false"
              :before-upload="handleBeforeUploadAvatar"
              @change="handleUploadAvatarChange"
            >
              <a-button :loading="isUploading"> <a-icon type="upload" /> 更换头像 </a-button>
            </a-upload>
          </div>
        </div>
      </a-col>
    </a-row>
  </div>
</template>

<script>
import mixConfig from '@/config/mix_config'
import { baseMixin } from '@/store/app-mixin'
// import store from '@/store'
import { mapGetters, mapActions } from 'vuex'
import { getBasicSettingFormParam } from '@/api/mix/account'
import { listToTree } from '@/utils/util'
import * as pattern from '@/utils/validateRegex'

export default {
  mixins: [baseMixin],
  data () {
    return {
      uploadAction: mixConfig.uploadAvatarApi,
      isUploading: false,
      // Mix code
      labelCol: {
        lg: { span: 7 }, sm: { span: 7 }
      },
      wrapperCol: {
        lg: { span: 10 }, sm: { span: 17 }
      },
      // 下拉列表元素
      departmentOptions: [],
      jobOptions: [],
      titleOptions: [],
      politicOptions: [],
      //
      record: {},
      rules: {
        username: [
          { required: true, message: '请输入中文名字', trigger: ['blur'] },
          { pattern: pattern.username.regex, message: pattern.username.msg, trigger: ['blur'] }
        ],
        sex: [ { required: true, message: '请选择性别', trigger: ['blur'] } ],
        IdCard: [
          { pattern: pattern.IdCard.regex, message: pattern.IdCard.msg, trigger: ['blur'] }
        ],
        politic: [ { required: true, message: '请选择政治面貌', trigger: ['blur'] } ],
        department: [ { required: true, message: '请选择部门', trigger: ['blur'] } ],
        job: [ { required: true, message: '请选择岗位', trigger: ['blur'] } ],
        title: [ { required: true, message: '请选择职称', trigger: ['blur'] } ]
      }
    }
  },
  created: function () {
    this.loadFormParam()
  },
  computed: {
    ...mapGetters([
      'avatar',
      'userInfo'
    ]),
    avatarImg: function () {
      return process.env.VUE_APP_PUBLIC_BASE_URL + this.avatar
      // return 'http://127.0.0.1:8080/' + store.getters.avatar
    }
  },
  methods: {
    ...mapActions(['UpdateUserInfo', 'UpdateUserAvatar']),

    loadFormParam () {
      getBasicSettingFormParam()
        .then((res) => {
          this.politicOptions.splice(0)
          this.politicOptions = res.politic
          //
          const tempDept = res.dept
          tempDept.forEach((elem, index) => {
            for (var key in elem) {
              if (key === 'status' && elem[key] === '0') {
                elem.disabled = true
              }
            }
          })
          listToTree(tempDept, this.departmentOptions)
          //
          this.jobOptions.splice(0)
          this.jobOptions = res.job
          //
          this.titleOptions.splice(0)
          this.titleOptions = res.title
          //
          this.filterUserInfo(this.userInfo)
        })
        //  网络异常，清空页面数据显示，防止错误的操作
        .catch((err) => {
          this.politicOptions.splice(0)
          this.jobOptions.splice(0)
          this.titleOptions.splice(0)
          if (err.response) { }
        })
    },

    handleSaveUserInfo () {
      this.$refs.form.validate(valid => {
        if (valid) {
          this.UpdateUserInfo(this.record)
            .then(() => {
              this.filterUserInfo(this.userInfo)
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              this.filterUserInfo(this.userInfo)
              if (err.response) { }
            })
        } else {
          return false
        }
      })
    },

    // 处理上传图片
    handleBeforeUploadAvatar (file) {
      const isJpgOrPng = file.type === 'image/jpeg' || file.type === 'image/png'
      if (!isJpgOrPng) {
        this.$message.error('图片只能是 JPG或PNG 格式！')
      }
      const isLt2M = file.size / 1024 / 1024 < 2
      if (!isLt2M) {
        this.$message.error('图片大小不能超过 2MB！')
      }
      return isJpgOrPng && isLt2M
    },

    handleUploadAvatarChange (info) {
      if (info.file.status === 'uploading') {
        this.isUploading = true
        return
      } else {
        this.isUploading = false
      }

      if (info.file.status === 'done') {
        if (info.file.response.code === 0) {
          this.UpdateUserAvatar(info.file.response.avatarFile)
            .then(() => {
              this.$message.success(`图片上传成功！`)
            })
        } else {
          this.$message.error(`图片上传失败，稍后再试！`)
        }
      } else if (info.file.status === 'error') {
        this.$message.error(`图片上传失败，稍后再试！`)
      }
    },

    // 内部方法
    filterUserInfo (user) {
      const department = user.dept_ids
      const { id, username, sex, IdCard, politic, job, title } = user

      this.record = { id, username, sex, IdCard, politic, job, title, department }
    }
  }
}
</script>

<style lang="less" scoped>

  .avatar-upload-wrapper {
    height: 200px;
    width: 100%;
  }

  .ant-upload-preview {
    position: relative;
    // margin: 0 auto;
    margin-bottom: 12px;
    width: 100%;
    max-width: 144px;
    border-radius: 50%;
    flex: 1 1;
    // box-shadow: 0 0 4px #ccc;

    // .upload-icon {
    //   position: absolute;
    //   top: 0;
    //   right: 10px;
    //   font-size: 1.4rem;
    //   padding: 0.5rem;
    //   background: rgba(222, 221, 221, 0.7);
    //   border-radius: 50%;
    //   border: 1px solid rgba(0, 0, 0, 0.2);
    // }
    // .mask {
    //   opacity: 0;
    //   position: absolute;
    //   background: rgba(0,0,0,0.4);
    //   cursor: pointer;
    //   transition: opacity 0.4s;

    //   &:hover {
    //     opacity: 1;
    //   }

    //   i {
    //     font-size: 2rem;
    //     position: absolute;
    //     top: 50%;
    //     left: 50%;
    //     margin-left: -1rem;
    //     margin-top: -1rem;
    //     color: #d6d6d6;
    //   }
    // }

    // img, .mask {
    //   width: 100%;
    //   max-width: 180px;
    //   height: 100%;
    //   border-radius: 50%;
    //   overflow: hidden;
    // }
    img {
      width: 100%;
      max-width: 144px;
      height: 100%;
      border-radius: 50%;
      overflow: hidden;
      margin-bottom: 1rem;
    }

    .upload {
      width: 100%;
      max-width: 110px;
      margin: 0 auto;
    }
  }
</style>
