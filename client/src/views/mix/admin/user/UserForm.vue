<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-01 21:04:14
-->
<template>
  <!-- hidden PageHeaderWrapper title demo -->
  <page-header-wrapper >
    <a-card :body-style="{padding: '24px 32px'}" :bordered="false">
      <div class="table-page-search-wrapper">
        <router-link to="/app/user/list"><a-button type="primary" icon="double-left" >返回</a-button></router-link>
      </div>

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

        <a-form-model-item label="工号" prop="workID">
          <a-input v-model="record.workID"></a-input>
        </a-form-model-item>
        <a-form-model-item label="身份证" prop="IdCard">
          <a-input v-model="record.IdCard"></a-input>
        </a-form-model-item>
        <a-form-model-item label="手机号" prop="phone">
          <a-input v-model="record.phone"></a-input>
        </a-form-model-item>
        <a-form-model-item label="电子邮箱" prop="email">
          <a-input v-model="record.email"></a-input>
        </a-form-model-item>

        <a-form-model-item label="状态" prop="status">
          <a-radio-group name="radioGroup2" v-model="record.status">
            <a-radio value="0">禁用</a-radio>
            <a-radio value="1">启用</a-radio>
          </a-radio-group>
        </a-form-model-item>

        <a-form-model-item label="密码" prop="password" >
          <a-input v-model="record.password"></a-input>
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
        <a-form-model-item label="用户角色" prop="role">
          <a-select v-model="record.role" mode="multiple" placeholder="请选择" >
            <a-select-option v-for="d in roleOptions" :key="d.id" :value="d.id" :disabled="d.status === '0'">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
          <a-button htmlType="submit" type="primary" @click="onSubmit">{{ btnLabel }}</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import md5 from 'md5'
import { getUserTbl, getPoliticTbl, getDeptTbl, getJobTbl, getTitleTbl, getRoleTbl, saveUser, getUserRole } from '@/api/manage'
import { listToTree } from '@/utils/util'

export default {
  name: 'UserForm',
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
              // setTimeout(() => {
              //   this.getAllFormParams(this.isNewUserPage)
              // }, 1000)
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
            this.record = Object.assign({}, user, { role: role })
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) {
              // setTimeout(() => {
              //   this.getAllFormParams(this.isNewUserPage, uid)
              // }, 1000)
            }
          })
      }
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
            // 网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              if (err.response) {
              }
            })
        } else {
          return false
        }
      })
    }
  }
}
</script>
