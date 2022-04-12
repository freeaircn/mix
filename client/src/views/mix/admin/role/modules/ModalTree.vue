<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-11 14:03:15
-->
<template>
  <a-modal
    :title="title"
    :visible="modalVisible"
    :width="700"
    :centered="true"
    :maskClosable="false"
    :destroyOnClose="true"
    @ok="handleOk"
    @change="handleVisibleChange"
  >
    <a-button style="margin-right: 18px;" @click="handleSelectAll">全选</a-button>
    <a-button @click="handleClearAll">清空</a-button>

    <a-tree
      v-model="checkedKeys2"
      checkable
      :showIcon="true"
      :replaceFields="replaceFields"
      :checkStrictly="true"
      autoExpandParent
      defaultExpandAll
      :tree-data="treeData"
    >
    </a-tree>
  </a-modal>
</template>

<script>

export default {
  name: 'ModalTree',
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
    treeData: {
      type: Array,
      default: () => []
    },
    allCheckableId: {
      type: Array,
      default: () => []
    },
    checkedKeys: {
      type: Array,
      default: () => []
    },
    replaceFields: {
      type: Object,
      default: function () {
        return { children: 'children', title: 'title', key: 'id' }
      }
    }
  },
  data () {
    return {
      checkedKeys2: {
        checked: [],
        halfChecked: []
      },
      //
      modalVisible: false,
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
      },
      immediate: true
    },
    checkedKeys: {
      handler: function (val) {
        this.checkedKeys2.checked = val
      },
      immediate: true
    }
  },
  methods: {
    handleOk () {
      const res = {
        objId: this.objId,
        selected: this.checkedKeys2.checked
      }
      this.$emit('submit', res)
      this.modalVisible = false
      this.$emit('update:visible', false)
    },

    handleVisibleChange (visible) {
      this.$emit('update:visible', visible)
    },

    handleSelectAll () {
      // this.checkedKeys2.checked.splice(0)
      // this.checkedKeys2.checked = this.allCheckableId
      // this.checkedKeys2.halfChecked.splice(0)
      this.checkedKeys2 = {}
      this.checkedKeys2.checked = this.allCheckableId
      this.checkedKeys2.halfChecked = []
    },

    handleClearAll () {
      // this.checkedKeys2.checked.splice(0)
      // this.checkedKeys2.halfChecked.splice(0)
      this.checkedKeys2 = {}
      this.checkedKeys2.checked = []
      this.checkedKeys2.halfChecked = []
    }
  }
}
</script>
