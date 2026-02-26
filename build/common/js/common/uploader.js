Fn.uploader = () => {
  const config = {
    concurrency: 3,
    endpoint: '/assets/api/uploader.php'
  }

  const STATUS_LABEL = {
    waiting: 'アップロードボタンのクリック待ち',
    uploading: 'アップロード中',
    done: 'アップロード完了しました',
    error: 'エラーが発生しました',
    403: 'セッションが切れました。再読み込みしてください',
    413: 'ファイルサイズが大きすぎます',
    CSRF_INVALID: 'セッションが切れました。再読み込みしてください',
    METHOD_NOT_ALLOWED: '不正なリクエストです',
    NO_FILE: 'ファイルが選択されていません',
    UPLOAD_ERROR: 'アップロードに失敗しました',
    INVALID_TYPE: '対応していないファイル形式です',
    FILE_TOO_LARGE: 'ファイルサイズが大きすぎます',
    MOVE_FAILED: 'サーバーエラーが発生しました'
  }

  const Uploader = (() => {

    const state = {
      files: [],
      activeCount: 0
    }

    const checkAllCompleted = () => {
      const hasUploading = state.files.some(f => f.status === 'uploading')
      const hasWaiting = state.files.some(f => f.status === 'waiting')
      const hasDone = state.files.some(f => f.status === 'done')

      if (!hasUploading && !hasWaiting && hasDone) {
        // const message = document.querySelector('.js-message')
        // if (message) {
        //   message.textContent = 'アップロードありがとうございます！'
        // }
      }
    }

    const addFiles = (fileList, { autoStart = false } = {}) => {

      Array.from(fileList).forEach(file => {
        state.files.push({
          id: crypto.randomUUID(),
          file,
          status: 'waiting',
          progress: 0,
          error_code: ''
        })
      })

      render()
      updateButton()

      if (autoStart) {
        processQueue()
      }


    }

    const processQueue = () => {

      while (
        state.activeCount < config.concurrency
      ) {

        const next = state.files.find(f => f.status === 'waiting')
        if (!next) return

        uploadFile(next)
      }
    }

    const uploadFile = (fileObj) => {

      const xhr = new XMLHttpRequest()
      const formData = new FormData()

      formData.append('image', fileObj.file)
      formData.append('csrf_token', CSRF_TOKEN)

      fileObj.status = 'uploading'
      state.activeCount++
      render()

      xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) {
          fileObj.progress = Math.round((e.loaded / e.total) * 100)
          render()
        }
      })

      xhr.responseType = 'json'
      xhr.timeout = 30000

      const setError = (error_code) => {
        fileObj.status = 'error'
        fileObj.error_code = error_code
      }

      const finalize = () => {
        state.activeCount--
        render()
        processQueue()
        checkAllCompleted()
      }

      const handleResult = () => {
        const res = xhr.response
        let error_code = null

        // 成功は即終了
        if (res?.status === 'success') {
          fileObj.status = 'done'
          fileObj.error_code = ''
          finalize()
          return
        }

        // JSONエラー優先
        if (res?.status === 'error') {
          error_code = res.code || 'アップロードに失敗しました'
        } else {
          const message = STATUS_LABEL[xhr.status]

          if (message) {
            error_code = message
          } else if (xhr.status >= 400) {
            error_code = `HTTP Error ${xhr.status}`
          } else {
            error_code = '不正なレスポンスです'
          }
        }

        if (error_code) {
          setError(error_code)
        }

        finalize()
      }



      xhr.addEventListener('load', () => {
        handleResult()
      })

      xhr.addEventListener('error', () => {
        setError('サーバーに接続できませんでした')
        finalize()
      })

      xhr.addEventListener('timeout', () => {
        setError('通信がタイムアウトしました')
        finalize()
      })

      xhr.open('POST', config.endpoint)
      xhr.send(formData)
    }

    const render = () => {

      const container = document.querySelector('.js-uploader-filelist')
      container.innerHTML = ''

      state.files.forEach(fileObj => {

        const card = document.createElement('div')
        card.className = 'uploader-filecard'

        const name = document.createElement('div')
        name.textContent = fileObj.file.name

        const progressWrap = document.createElement('div')
        progressWrap.className = 'uploader-filecardprogress'

        const bar = document.createElement('div')
        bar.className = 'uploader-filecardbar'
        bar.style.width = fileObj.progress + '%'

        progressWrap.appendChild(bar)

        const status = document.createElement('div')
        status.className = 'uploader-filecardstatus'
        status.textContent = (fileObj.status === 'error' && fileObj.error_code)
                              ? STATUS_LABEL[fileObj.error_code]
                              : STATUS_LABEL[fileObj.status] || fileObj.status

        card.appendChild(name)
        card.appendChild(progressWrap)
        card.appendChild(status)
        card.classList.add(`is-${fileObj.status}`)

        container.appendChild(card)
      })
    }

    const updateButton = () => {
      const btn = document.querySelector('.js-uploader-start')
      btn.disabled = !state.files.some(f => f.status === 'waiting')
    }

    return {
      addFiles,
      processQueue
    }

  })()

  /* =====================
    DOM Events
  ===================== */

  const dropArea = document.querySelector('.js-uploader-droparea')
  const input = document.querySelector('.js-uploader-input')
  const startBtn = document.querySelector('.js-uploader-start')

  dropArea.addEventListener('click', () => input.click())

  input.addEventListener('change', e => {
    Uploader.addFiles(e.target.files, { autoStart: false })
  })

  dropArea.addEventListener('dragover', e => {
    e.preventDefault()
    dropArea.classList.add('is-dragover')
  })

  dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('is-dragover')
  })

  dropArea.addEventListener('drop', e => {
    e.preventDefault()
    dropArea.classList.remove('is-dragover')
    Uploader.addFiles(e.dataTransfer.files, { autoStart: true })
  })

  startBtn.addEventListener('click', () => {
    Uploader.processQueue()
  })
}
