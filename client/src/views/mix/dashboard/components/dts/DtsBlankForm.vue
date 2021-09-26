<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-25 21:29:10
-->
<template>
  <page-header-wrapper :title="false">
    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <a-card title="新问题单" :bordered="false" :body-style="{marginBottom: '8px'}" >
      <router-link slot="extra" to="/dashboard/dts">返回</router-link>

      <a-form-model
        ref="form"
        :model="record"
        :rules="rules"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="类别" prop="type">
          <a-radio-group name="radioGroup1" v-model="record.type">
            <a-radio value="1">隐患</a-radio>
            <a-radio value="2">故障</a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item label="标题" prop="title">
          <a-input v-model="record.title"></a-input>
        </a-form-model-item>
        <a-form-model-item label="影响程度" prop="level">
          <a-select v-model="record.level" placeholder="请选择" >
            <a-select-option value="1">紧急</a-select-option>
            <a-select-option value="2">严重</a-select-option>
            <a-select-option value="3">一般</a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="所属单元" prop="unit">
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

        <a-form-model-item label="进展" prop="progress">
          <a-textarea v-model="record.progress" :rows="8" />
        </a-form-model-item>

        <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
          <a-button >保存</a-button>
          <a-button type="primary" @click="onSubmit" style="margin-left: 8px">提交</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import md5 from 'md5'
import { mapGetters } from 'vuex'
import { getUserTbl, getPoliticTbl, getDeptTbl, getJobTbl, getTitleTbl, getRoleTbl, saveUser, getUserRole } from '@/api/manage'
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
      // 下拉列表元素
      departmentOptions: [],
      jobOptions: [],
      titleOptions: [],
      politicOptions: [],
      roleOptions: [],
      //
      isNewUserPage: true,
      btnLabel: '新建',
      record: {},
      rules: {}
    }
  },
  created: function () {
    const uid = (this.$route.params.uid) ? this.$route.params.uid : '0'
    if (uid === '0') {
      this.isNewUserPage = true
      this.btnLabel = '新建'
      this.record = Object.assign({}, { password: '666' })
    } else {
      this.isNewUserPage = false
      this.btnLabel = '修改'
    }
    this.getAllFormParams(this.isNewUserPage, uid)
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  methods: {
    getAllFormParams (isNewUserPage = true, uid = 0) {
      if (isNewUserPage) {
        Promise.all([getPoliticTbl(), getDeptTbl({ columnName: ['id', 'name', 'pid', 'status'] }), getJobTbl(), getTitleTbl(), getRoleTbl()])
          .then((res) => {
            this.politicOptions.splice(0)
            this.politicOptions = res[0].data.slice(0)
            //
            const tempDept = res[1].data
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
            this.jobOptions = res[2].data.slice(0)
            //
            this.titleOptions.splice(0)
            this.titleOptions = res[3].data.slice(0)
            //
            this.roleOptions.splice(0)
            this.roleOptions = res[4].data.slice(0)
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) {
              setTimeout(() => {
                this.getAllFormParams(this.isNewUserPage)
              }, 1000)
            }
          })
      } else {
        Promise.all([getPoliticTbl(), getDeptTbl({ columnName: ['id', 'name', 'pid', 'status'] }), getJobTbl(), getTitleTbl(), getRoleTbl(), getUserTbl({ 'uid': uid }), getUserRole({ 'uid': uid })])
          .then((res) => {
            this.politicOptions.splice(0)
            this.politicOptions = res[0].data.slice(0)
            //
            const tempDept = res[1].data
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
            this.jobOptions = res[2].data.slice(0)
            //
            this.titleOptions.splice(0)
            this.titleOptions = res[3].data.slice(0)
            //
            this.roleOptions.splice(0)
            this.roleOptions = res[4].data.slice(0)
            //
            const user = res[5].data[0]
            //
            const role = res[6].data.slice(0)
            // 合并数据
            this.fillRecordObj(user, role)
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) {
              setTimeout(() => {
                this.getAllFormParams(this.isNewUserPage, uid)
              }, 1000)
            }
          })
      }
    },

    fillRecordObj (user, role) {
      const temp = {}
      temp.department = []
      for (var p in user) {
        if ((p.indexOf('deptLev') === -1) && user.hasOwnProperty(p)) {
          temp[p] = user[p]
        }
        if (p.indexOf('deptLev') !== -1 && user[p] !== '0') {
          temp.department.push(user[p])
        }
      }
      this.record = Object.assign({}, temp, { role: role })
    },

    onSubmit () {
      this.$refs.form.validate(valid => {
        if (valid) {
          const data = { ...this.record }
          if (this.record.password) {
            data.password = md5(this.record.password)
          }
          saveUser(data)
            .then(() => {
              if (this.isNewUserPage) {
                this.$confirm({
                  title: '继续添加新用户？',
                  content: h => <div style="">点击取消，将转至用户列表</div>,
                  onOk: () => {
                    this.$refs.form.clearValidate()
                    this.record = Object.assign({}, { password: '666' })
                  },
                  onCancel: () => {
                    this.$router.push({ path: `/app/user/list` })
                  },
                  class: 'test'
                })
              } else {
                this.$router.push({ path: `/app/user/list` })
              }
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            // .catch((err) => {
            //   if (err.response) {
            //     setTimeout(() => {
            //       this.getAllFormParams()
            //     }, 1000)
            //   }
            // })
        } else {
          return false
        }
      })
    }
  }
}
</script>
