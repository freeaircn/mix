<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-12 19:31:33
-->
<template>
  <a-modal
    :title="title"
    :visible="modalVisible"
    :width="800"
    :centered="true"
    :maskClosable="false"
    destroyOnClose
    okText="关闭"
    @ok="handleOk"
    @change="handleVisibleChange"
    @afterClose="handleClose"
  >
    <a-table
      ref="table"
      rowKey="dept_id"
      :columns="columns"
      :data-source="data2"
      :pagination="false"
    >
      <span slot="serial" slot-scope="text, record, index">
        {{ index + 1 }}
      </span>
      <span slot="data_writable" slot-scope="text, record">
        <template>
          <a-input
            v-if="record.editable"
            style="margin: -5px 0; width: 50px"
            :value="text"
            @change="e => handleInputChange(e.target.value, record, 'data_writable')"
          />
          <template v-else>
            {{ text }}
          </template>
        </template>
      </span>
      <span slot="is_default" slot-scope="text, record">
        <template>
          <a-input
            v-if="record.editable"
            style="margin: -5px 0; width: 50px"
            :value="text"
            @change="e => handleInputChange(e.target.value, record, 'is_default')"
          />
          <template v-else>
            {{ text }}
          </template>
        </template>
      </span>
      <span slot="operation" slot-scope="text, record">
        <template>
          <div>
            <span v-if="record.editable">
              <a @click="() => handleSaveEditing(record)" style="margin-right: 8px;">保存</a>
              <a @click="() => handleCancelEditing(record)">取消</a>
            </span>
            <span v-else>
              <a :disabled="editingKey !== ''" @click="() => handleEdit(record)">编辑</a>
            </span>
          </div>
        </template>
      </span>
    </a-table>
  </a-modal>
</template>

<script>

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '部门',
    dataIndex: 'name'
  },
  {
    title: '数据写权限',
    dataIndex: 'data_writable',
    scopedSlots: { customRender: 'data_writable' }
  },
  {
    title: '默认部门（多部门）',
    dataIndex: 'is_default',
    scopedSlots: { customRender: 'is_default' }
  },
  {
    title: '操作',
    dataIndex: 'operation',
    width: '200px',
    scopedSlots: { customRender: 'operation' }
  }
]

export default {
  name: 'ModalTable',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: ''
    },
    objId: {
      type: String,
      default: ''
    },
    data: {
      type: Array,
      default: () => []
    }
  },
  data () {
    this.columns = columns
    return {
      data2: [],
      cacheData: [],
      modalVisible: false,
      editingKey: '',
      labelCol: {
        xs: { span: 24 },
        sm: { span: 7 }
      },
      wrapperCol: {
        xs: { span: 24 },
        sm: { span: 13 }
      }
    }
  },
  watch: {
    visible: {
      handler: function (val) {
        this.modalVisible = val
        if (val) {
          this.data2 = this.data
          this.cacheData = this.data.map(item => ({ ...item }))
        }
      },
      immediate: true
    }
  },
  methods: {
    handleEdit (record) {
      const key = record.dept_id
      const newData = [...this.data2]
      const target = newData.find(item => key === item.dept_id)
      this.editingKey = key
      if (target) {
        target.editable = true
        this.data2 = newData
      }
    },

    handleInputChange (value, record, column) {
      const key = record.dept_id
      const newData = [...this.data2]
      const target = newData.find(item => key === item.dept_id)
      console.log(target)
      if (target) {
        target[column] = value
        this.data2 = newData
      }
      console.log(target)
    },

    handleSaveEditing (record) {
      console.log(this.data2)
      const key = record.dept_id
      const newData = [...this.data2]
      const newCacheData = [...this.cacheData]
      const target = newData.find(item => key === item.dept_id)
      console.log(target)
      const targetCache = newCacheData.find(item => key === item.dept_id)
      if (target && targetCache) {
        delete target.editable
        this.data2 = newData
        Object.assign(targetCache, target)
        this.cacheData = newCacheData
      }
      this.editingKey = ''
      console.log(this.data2)
    },

    handleCancelEditing (record) {
      const key = record.dept_id
      const newData = [...this.data2]
      const target = newData.find(item => key === item.dept_id)
      this.editingKey = ''
      if (target) {
        Object.assign(target, this.cacheData.find(item => key === item.dept_id))
        delete target.editable
        this.data2 = newData
      }
    },

    handleOk () {
      // const res = {
      //   objId: this.objId
      // }
      // this.$emit('submit', res)
      this.modalVisible = false
      this.$emit('update:visible', false)
      this.editingKey = ''
    },

    handleVisibleChange (visible) {
      this.$emit('update:visible', visible)
      this.editingKey = ''
    },

    handleClose () {
      this.data2 = []
      this.editingKey = ''
    }
  }
}
</script>
