<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-10 22:36:56
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

        <a-form-model-item label="密码" prop="password">
          <a-input v-model="record.password"></a-input>
        </a-form-model-item>

        <a-form-model-item label="政治面貌" prop="politic">
          <a-select v-model="record.politic" placeholder="请选择" >
            <a-select-option v-for="d in politicOptions" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="部门" prop="department">
          <a-cascader
            :options="departmentOptions"
            @change="onCascaderChange"
            expand-trigger="hover"
            change-on-select
            :fieldNames="{ label: 'name', value: 'id', children: 'children' }"
            placeholder="请选择"
          />
        </a-form-model-item>

        <a-form-model-item label="岗位" prop="job">
          <a-select v-model="record.job" placeholder="请选择" >
            <a-select-option v-for="d in jobOptions" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="职称" prop="title">
          <a-select v-model="record.title" placeholder="请选择" >
            <a-select-option v-for="d in titleOptions" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="用户角色" prop="role">
          <a-select v-model="record.role" mode="multiple" placeholder="请选择" >
            <a-select-option v-for="d in roleOptions" :key="d.id" :value="d.id">
              {{ d.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
          <a-button htmlType="submit" type="primary" @click="onSubmit">提交</a-button>
          <a-button style="margin-left: 8px">保存</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import { getPoliticTbl, getDeptTbl, getJobTbl, getTitleTbl, getRoleTbl, saveUser } from '@/api/manage'
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
      record: {
        password: '666',
        department: ''
      },
      rules: {

      }
    }
  },
  created: function () {
    this.getAllFormParams()
  },
  methods: {
    getAllFormParams () {
      Promise.all([getPoliticTbl(), getDeptTbl(), getJobTbl(), getTitleTbl(), getRoleTbl()])
        .then((res) => {
          this.politicOptions.splice(0)
          this.politicOptions = res[0].data.slice(0)
          //
          listToTree(res[1].data, this.departmentOptions)
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
              this.getAllFormParams()
            }, 1000)
          }
        })
    },
    onCascaderChange (value, selectedOptions) {
      console.log('selectedOptions', selectedOptions)
      this.record.department = selectedOptions.map(o => o.name).join(', ')
    },
    onSubmit () {
      this.$refs.form.validate(valid => {
        if (valid) {
          saveUser(this.record)
            .then(() => {

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
