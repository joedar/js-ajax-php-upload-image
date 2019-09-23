const JOEDAR = (function(){
  return {
    //------------------------------
    // 原生js封装fromData的ajax请求
    //------------------------------
    formAjax: function (param, callback) {
      var xhr = new XMLHttpRequest()
      xhr.open('POST', param.url, true)
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            callback(JSON.parse(xhr.response))
          } else {
            callback('请求失败')
          }
        }
      }
      xhr.send(param.data)
    },

    //--------------------------
    // 将图片转换为Base64格式
    //--------------------------
    imgToBase64: function (file, callback) {
      let reader = new FileReader()
      reader.readAsDataURL(file)
      reader.onload = function (file) {
        callback(this.result)
      }
    }
  }
}())

const UPLOADIMAGE = (function(){
  return {
    init: function(DATA, callback) {
      // 首先判断有没有传apiUrl
      if (!DATA.apiUrl) {
        console.warn('no apiUrl')
        return
      }

      // 首先判断有没有传files
      if (!DATA.files) {
        console.warn('no files')
        return
      }

      // 上传图片的接口
      this.apiUrl = DATA.apiUrl

      // 上传文件的files
      this.files = DATA.files

      // 展示图片的容器
      this.showBox = DATA.showBox || ''

      //----------------------------------
      // 上传图片的一些配置
      // instanceof 校验是否是 {}
      // JSON.stringify() 校验是否是空对象
      //----------------------------------
      this.config = DATA.config instanceof Object && JSON.stringify(DATA.config) !== '{}' ? DATA.config : {}

      //--------------------------------------------
      // 判断是单张图还是多图，最主要的还是length属性
      // Object 没有该属性，Array 有该属性
      //--------------------------------------------
      this.single = this.files && typeof this.files === 'object' && this.files.length === undefined

      //---------------------------------------
      // 构建FormData
      // 上传类的，传给后端的必须是FormData形式
      //---------------------------------------
      this.formData = new FormData()

      this.upload(callback)
    },

    upload: function (callback) {
      
      // 如果上传的是 e.target.files[0]
      if (this.single) {
        // 就直接将 this.files 追加到 this.formData 里
        this.formData.append('0', this.files)
      // 如果上传的是 e.target.files
      } else {
        // 循环遍历 this.files 数组对象
        for (let key in this.files) {
          if (typeof this.files[key] === 'object') {
            //--------------------------------------
            // 向formData对象中追加item
            // 由于是formData对象，以append追加的内容
            // 控制台console.log是不可见的
            //--------------------------------------
            this.formData.append(key, this.files[key])
          }
        }
      }

      //----------------------------
      // 将图片文件转换成base64文件
      // 并在显示图片区域显示缩略图
      // 如果有显示区域的话...
      //----------------------------
      if (this.showBox) {
        if (this.single) {
          JOEDAR.imgToBase64(this.files, this.imgToBase64.bind(this))
        } else {
          for (let key in this.files) {
            if (typeof this.files[key] === 'object') {
              JOEDAR.imgToBase64(this.files[key], this.imgToBase64.bind(this))
            }
          }
        }
      }

      //-------------------------------------
      // 校验 this.config 里是否有值、是否合法
      // 以生成 ?path=article&size=51654536
      // 以REQUEST方式传给后端
      //-------------------------------------
      const config = []
      for (let key in this.config) {
        (key && /path|size/.test(key)) && config.push(key + '=' + this.config[key])
      }
      const search = config.length ? '?' + config.toString().replace(',', '&') : ''

      //---------------------
      // 构造提交后台的data
      //---------------------
      const param = {}
      param.url = this.apiUrl + search
      param.data = this.formData

      JOEDAR.formAjax(param, callback)
    },

    imgToBase64: function(src) {
      this.showBox.innerHTML += '<img src="'+ src +'">'
    }
  }
}())
