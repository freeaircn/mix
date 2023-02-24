<!--
 * @Description:
 * @Author: freeair
 * @Date: 2023-02-24 20:33:17
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-25 00:06:24
-->
<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" :loading="loading" :body-style="{marginBottom: '8px'}">
      <div style="margin-bottom: 8px">
        <span>
          <a-button type="primary" style="margin-right: 8px" ghost @click="handlePrePage"> <a-icon type="step-backward" /> </a-button>
          <a-input v-model="headerPageIndex" style="margin-right: 8px; width: 55px; text-align: center" @pressEnter="handleGotoPage"/>
          <span style="margin-right: 8px">/</span>
          <span style="margin-right: 8px">{{ pageTotal }}</span>
          <a-button type="primary" ghost @click="handleNextPage"> <a-icon type="step-forward" /> </a-button>
        </span>
        <a-divider type="vertical" />
        <span>
          <a-button type="primary" style="margin-right: 8px" ghost @click="handleZoomOut"> <a-icon type="zoom-out" /> </a-button>
          <a-input v-model="headerScale" addon-after="%" style="margin-right: 8px; width: 100px; text-align: center" @pressEnter="handleZoom"/>
          <a-button type="primary" ghost @click="handleZoomIn"> <a-icon type="zoom-in" /> </a-button>
        </span>
        <a-divider type="vertical" />
        <span>
          <a-button type="primary" style="margin-right: 8px" ghost @click="handleRotate"> <a-icon type="redo" /> </a-button>
        </span>
      </div>

      <div style="background:#ECECEC; padding: 8px">
        <a-card :bordered="false">
          <pdf
            ref="wrapper"
            :src="pdf_url"
            :page="pageIndex"
            :rotate="pageRotate"
            @num-pages="pageTotal=$event"
            @error="pdfError($event)"
          >
          </pdf>
        </a-card>
      </div>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { apiDownloadFile } from '@/api/mix/drawing'
//
import pdf from 'vue-pdf'

export default {
  name: 'FilePreview',
  components: {
      pdf
  },
  mixins: [baseMixin],
  data () {
    return {
      id: '0',
      file: '',
      loading: false,
      //
      pdf_url: '',
      pageIndex: 1,
      pageTotal: 1,
      pageRotate: 0,
      headerPageIndex: 1,
      scale: 100,
      headerScale: 100
      // -- 2023-2-22
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {
    this.id = (this.$route.params.id) ? this.$route.params.id : '0'
    this.file = (this.$route.params.file) ? this.$route.params.file : ''
  },
  mounted () {
    this.handlePreviewFile()
  },
  methods: {
    handlePreviewFile () {
      if (this.file === '') {
        this.$message.info('没有可预览的文件')
        return true
      }
      //
      const param = {
        id: this.id,
        file_org_name: this.file
      }
      apiDownloadFile(param)
        .then((res) => {
          const { data, headers } = res

          const str = headers['content-type']
          if (str.indexOf('json') !== -1) {
            this.$message.warning('没有权限')
          } else {
            // const blob = new Blob([data], { type: headers['content-type'] })
            const url = window.URL.createObjectURL(data)
            this.pdf_url = url
          }
        })
        .catch(() => {
          this.$message.info('文件预览失败')
        })
    },

    handlePrePage () {
      var p = this.pageIndex
      p = p > 1 ? p - 1 : this.pageTotal
      this.pageIndex = p
      this.headerPageIndex = p
    },

    handleNextPage () {
      var p = this.pageIndex
      p = p < this.pageTotal ? p + 1 : 1
      this.pageIndex = p
      this.headerPageIndex = p
    },

    handleGotoPage () {
      var reg = /^[0-9]{1,4}$/
      if (reg.test(this.headerPageIndex) === false) {
        this.headerPageIndex = this.pageIndex
        return false
      }
      var p = parseInt(this.headerPageIndex)
      if (p < 1) {
        p = 1
      } else {
        p = p <= this.pageTotal ? p : 1
      }
      this.pageIndex = p
      this.headerPageIndex = p
    },

    handleZoomOut () {
      var s = this.scale
      s -= 5
      if (s < 0) {
        s = 5
      }
      if (s > 100) {
        s = 100
      }
      this.scale = s
      this.headerScale = s
      this.$refs.wrapper.$el.style.width = parseInt(s) + '%'
    },

    handleZoomIn () {
      var s = this.scale
      s += 5
      if (s < 0) {
        s = 5
      }
      if (s > 100) {
        s = 100
      }
      this.scale = s
      this.headerScale = s
      this.$refs.wrapper.$el.style.width = parseInt(s) + '%'
    },

    handleZoom () {
      var reg = /^[0-9]{1,3}$/
      if (reg.test(this.headerScale) === false) {
        this.headerScale = this.scale
        return false
      }
      var s = parseInt(this.headerScale)
      if (s < 0) {
        s = 5
      }
      if (s > 100) {
        s = 100
      }
      this.scale = s
      this.headerScale = s
      this.$refs.wrapper.$el.style.width = parseInt(s) + '%'
    },

    handleRotate () {
      this.pageRotate += 90
    },

    pdfError () {
      this.$message.info('文件预览失败')
    },

    handleExit () {
      this.$router.back()
    }
  }
}
</script>
