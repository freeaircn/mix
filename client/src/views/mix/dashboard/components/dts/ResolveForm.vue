<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 16:48:52
-->
<template>
  <a-modal
    :title="title"
    :visible="modalVisible"
    :width="700"
    :centered="true"
    :destroyOnClose="true"
    :maskClosable="false"
    @ok="handleOk"
    @change="handleVisibleChange"
  >
    <a-form-model
      ref="form"
      :model="record"
      :rules="rules"
      :label-col="labelCol"
      :wrapper-col="wrapperCol"
    >
      <a-form-model-item label="说明" prop="text">
        <a-textarea v-model="record.text" :rows="6" />
      </a-form-model-item>
      <a-form-model-item label="原因分类" prop="cause_id">
        <a-select v-model="record.cause_id" placeholder="请选择" >
          <a-select-option v-for="d in causes" :key="d.id" :value="d.id">
            {{ d.name }}
          </a-select-option>
        </a-select>
      </a-form-model-item>
      <a-form-model-item label="原因分析" prop="cause_analysis">
        <a-textarea v-model="record.cause_analysis" :rows="6" />
      </a-form-model-item>
    </a-form-model>
  </a-modal>
</template>

<script>

export default {
  name: 'Form',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: ''
    },
    causes: {
      type: Array,
      default: () => []
    },
    record: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      modalVisible: false,
      labelCol: {
        lg: { span: 4 }, sm: { span: 4 }
      },
      wrapperCol: {
        lg: { span: 18 }, sm: { span: 18 }
      },
      rules: {
        text: [{ required: true, message: '请输入', trigger: ['change'] }],
        cause_id: [{ required: true, message: '请选择', trigger: ['change'] }],
        cause_analysis: [{ required: true, message: '请输入', trigger: ['change'] }]
      }
    }
  },
  watch: {
    visible: {
      handler: function (val) {
        this.modalVisible = val
      },
      immediate: true
    }
  },
  methods: {
    handleOk () {
      this.$confirm({
        title: '确认提交？',
        onOk: () => {
          return new Promise((resolve, reject) => {
            const result = Object.assign({}, this.record)
            this.$emit('confirm', result)
            this.modalVisible = false
            this.$emit('update:visible', false)
            resolve()
          })
        }
      })
    },
    handleVisibleChange (visible) {
      this.$emit('update:visible', visible)
    }
  }
}
</script>
