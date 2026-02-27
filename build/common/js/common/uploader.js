/**
 *
 * @param {object} obj {
 *  {function} func : アップロード処理が完了した際に実行する
 * }
 */
Fn.uploader = (obj = {}) => {
  const config = {
    concurrency: 3,
    endpoint: '/assets/api/uploader.php',
    fadeout_duration: 400,
    max_filesize: -1 * 1024 * 1024,
    allowed_types: [
      'image/jpeg',
      'image/png',
      'image/gif',
      'image/webp',
      'image/heic',
      'image/heif'
    ],
    delete_btn_label: '削除',
    retry_btn_label: '再試行'
  }

  const status_label = {
    waiting: 'アップロードボタンのクリック待ち',
    uploading: 'アップロード中',
    done: 'アップロード完了しました',
    error: 'エラーが発生しました',

    403: 'セッションが切れました。再読み込みしてください',
    413: 'ファイルサイズが大きすぎます',

    CSRF_INVALID: 'セッションが切れました。再読み込みしてください',
    METHOD_NOT_ALLOWED: '不正なリクエストです',
    NO_DIRECTORY: 'アップロード先が不明です',
    NO_FILE: 'ファイルが選択されていません',
    TIMEOUT_ERROR: '通信がタイムアウトしました',
    UPLOAD_ERROR: 'アップロードに失敗しました',
    SERVER_CONNECT_ERROR: 'サーバーに接続できませんでした',
    INVALID_TYPE: '対応していないファイル形式です',
    FILE_TOO_LARGE: 'ファイルサイズが大きすぎます',
    MOVE_FAILED: 'サーバーエラーが発生しました',
    OTHER: '不正なレスポンスです'
  }

  const Uploader = (() => {
    const state = {
      files: [],
      activeCount: 0
    }

    const setStatus = (fileObj, status, error_code = '') => {
      fileObj.status = status
      fileObj.error_code = error_code
      updateFileCard(fileObj)
    }

    // NOTE: すべてのキューが完了した際に何かを表示する場合
    const checkAllCompleted = () => {
      const hasUploading = state.files.some(f => f.status === 'uploading'),
            hasWaiting = state.files.some(f => f.status === 'waiting'),
            hasDone = state.files.some(f => f.status === 'done')


      if (!hasUploading && !hasWaiting && hasDone && obj.func) {
        obj.func()
      }
    }

    const addFiles = (fileList, { autoStart = false } = {}) => {
      Array.from(fileList).forEach(file => {

        const fileObj = {
          id: crypto.randomUUID(),
          file,
          status: 'waiting',
          progress: 0,
          error_code: ''
        }

        if (file.size === 0) {
          fileObj.status = 'error'
          fileObj.error_code = 'NO_FILE'
        } else if (config.max_filesize > 0 && file.size > config.max_filesize) {
          fileObj.status = 'error'
          fileObj.error_code = 'FILE_TOO_LARGE'
        } else if (!config.allowed_types.includes(file.type) && !(
          !file.type.startsWith('image/') &&
          !file.name.toLowerCase().endsWith('.heic') &&
          !file.name.toLowerCase().endsWith('.heif')
        )) {
          fileObj.status = 'error'
          fileObj.error_code = 'INVALID_TYPE'
        } else {
          fileObj.preview_url = URL.createObjectURL(file)
        }

        switch (true) {
          case (file.size === 0):
            fileObj.status = 'error'
            fileObj.error_code = 'NO_FILE'
            break
          case (config.max_filesize > 0 && file.size > config.max_filesize):
            fileObj.status = 'error'
            fileObj.error_code = 'FILE_TOO_LARGE'
            break
          default:
            const filename = file.name.toLowerCase()

            // NOTICE: heic/heif のときは、file.type が ''（空）になる場合があるためのフォールバック対応
            if (!(
                config.allowed_types.includes(file.type) ||
                filename.endsWith('.heic') ||
                filename.endsWith('.heif')
            )) {
              fileObj.status = 'error'
              fileObj.error_code = 'INVALID_TYPE'
            } else {
              fileObj.preview_url = URL.createObjectURL(file)
            }
        }

        state.files.push(fileObj)
        _container.appendChild(createFileCard(fileObj))
      })

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
      formData.append('dir1', DIR1)


      setStatus(fileObj, 'uploading')
      state.activeCount++

      xhr.upload.addEventListener('progress', e => {

        if (e.lengthComputable) {
          fileObj.progress = Math.round((e.loaded / e.total) * 100)
          updateFileCard(fileObj)
        }
      })

      xhr.responseType = 'json'
      xhr.timeout = 30000

      const finalize = () => {
        state.activeCount--
        updateButton()
        processQueue()
        checkAllCompleted()
      }

      const handleResult = () => {
        const res = xhr.response
        let error_code

        // NOTE: 成功は即終了
        if (res?.status === 'success') {
          fileObj.progress = 100
          setStatus(fileObj, 'done')
          finalize()
          return
        }

        // NOTE; JSONエラー優先
        if (res?.status === 'error') {
          error_code = res.code || 'UPLOAD_ERROR'
        } else if (xhr.status >= 400) {
          error_code = xhr.status
        } else {
          error_code = 'OTHER'
        }

        if (error_code) {
          fileObj.progress = 0
          setStatus(fileObj, 'error', error_code)
        }

        finalize()
      }

      xhr.addEventListener('load', () => {
        handleResult()
      })

      xhr.addEventListener('error', () => {
        setStatus(fileObj, 'error', 'SERVER_CONNECT_ERROR')
        finalize()
      })

      xhr.addEventListener('timeout', () => {
        setStatus(fileObj, 'error', 'TIMEOUT_ERROR')
        finalize()
      })

      xhr.open('POST', config.endpoint)
      xhr.send(formData)
    }

    const fadeOutAndRemove = (fileObj) => {
      fileObj.el.classList.add('is-fadeout')

      setTimeout(() => {

        if (fileObj.preview_url) {
          URL.revokeObjectURL(fileObj.preview_url)
        }
       fileObj.el.remove()
        state.files = state.files.filter(f => f !== fileObj)
      }, config.fadeout_duration)
    }

    const getStatusLabel = (fileObj) => {
      return (fileObj.status === 'error' && fileObj.error_code)
        ? status_label[fileObj.error_code] || fileObj.error_code
        : status_label[fileObj.status] || fileObj.status
    }

    const updateFileCard = (fileObj) => {
      fileObj.barEl.style.width = fileObj.progress + '%'
      fileObj.statusEl.textContent = getStatusLabel(fileObj)
      fileObj.el.className = `uploader-filecard is-${fileObj.status}`
      fileObj.updateButton?.()

      if (fileObj.status === 'done') {
        fadeOutAndRemove(fileObj)
      }
    }


    const createFileCard = (fileObj) => {
      const card = document.createElement('div'),
            text_wrap = document.createElement('div'),
            name = document.createElement('div'),
            img_wrap = document.createElement('div'),
            img = document.createElement('img'),
            progress_wrap = document.createElement('div'),
            bar = document.createElement('div'),
            status = document.createElement('div'),
            action_btn = document.createElement('button')

      card.className = `uploader-filecard is-${fileObj.status}`
      text_wrap.className = 'uploader-text'
      name.className = 'uploader-name'
      img_wrap.className = 'uploader-thumbnail'
      progress_wrap.className = 'uploader-filecardprogress'
      bar.className = 'uploader-filecardbar'
      status.className = 'uploader-filecardstatus'
      action_btn.type = 'button'
      action_btn.className = 'uploader-filecardremove'
      action_btn.textContent = config.delete_btn_label

      name.textContent = fileObj.file.name
      img.src = fileObj.preview_url

      img.onerror = () => {
        img.remove()

        img_wrap.classList.add('uploader-nothumbnail')
        img_wrap.onclick = () => {
          fadeOutAndRemove(fileObj)
        }
      }

      const updateButton = () => {

        if (fileObj.status === 'error') {
          action_btn.textContent = config.retry_btn_label
          action_btn.onclick = () => {
            fileObj.status = 'waiting'
            fileObj.error_code = ''
            updateFileCard(fileObj)
            Uploader.processQueue()
          }

          // テスト用: 失敗フラグを送る
      const originalUploadFile = Uploader.uploadFile
      Uploader.uploadFile = (f) => {
        if (f === fileObj && fileObj.retryFail) {
          const xhr = new XMLHttpRequest()
          const formData = new FormData()
          formData.append('image', fileObj.file)
          formData.append('csrf_token', CSRF_TOKEN)
          formData.append('fail', '1') // 擬似失敗
          fileObj.retryFail = false // 1回だけ失敗
          setStatus(fileObj, 'uploading')
          state.activeCount++
          xhr.responseType = 'json'
          xhr.timeout = 30000

          xhr.addEventListener('load', () => {
            const res = xhr.response
            if (res?.status === 'success') setStatus(fileObj, 'done')
            else setStatus(fileObj, 'error', res?.code || 'UPLOAD_ERROR')
            state.activeCount--
            updateButton()
            Uploader.processQueue()
          })
          xhr.addEventListener('error', () => {
            setStatus(fileObj, 'error', 'SERVER_CONNECT_ERROR')
            state.activeCount--
            updateButton()
            Uploader.processQueue()
          })
          xhr.open('POST', config.endpoint)
          xhr.send(formData)
        } else {
          originalUploadFile(f)
        }
      }

      // フラグをセットして再試行
      fileObj.retryFail = true
      Uploader.processQueue()
        } else {
          action_btn.textContent = config.delete_btn_label
          action_btn.onclick = () => fadeOutAndRemove(fileObj)
        }
      }

      updateButton()

      progress_wrap.appendChild(bar)
      img_wrap.appendChild(img)
      text_wrap.appendChild(name)
      text_wrap.appendChild(progress_wrap)
      text_wrap.appendChild(status)
      card.appendChild(img_wrap)
      card.appendChild(text_wrap)
      card.appendChild(action_btn)

      fileObj.el = card
      fileObj.barEl = bar
      fileObj.statusEl = status
      fileObj.updateButton = updateButton

      updateFileCard(fileObj)

      return card
    }

    const updateButton = () => {
      _upload_btn.disabled = !state.files.some(f => f.status === 'waiting')
    }

    return {
      addFiles,
      processQueue
    }
  })()

  /* =====================
    DOM Events
  ===================== */
  const _container = document.querySelector('.js-uploader-filelist'),
        _drop_area = document.querySelector('.js-uploader-droparea'),
        _input = document.querySelector('.js-uploader-input'),
        _upload_btn = document.querySelector('.js-uploader-button')

  _drop_area.addEventListener('click', () => _input.click())

  _input.addEventListener('change', e => {
    Uploader.addFiles(e.target.files, { autoStart: false })
  })

  _drop_area.addEventListener('dragover', e => {
    e.preventDefault()
    _drop_area.classList.add('is-dragover')
  })

  _drop_area.addEventListener('dragleave', () => {
    _drop_area.classList.remove('is-dragover')
  })

  _drop_area.addEventListener('drop', e => {
    e.preventDefault()
    _drop_area.classList.remove('is-dragover')
    Uploader.addFiles(e.dataTransfer.files, { autoStart: true })
  })

  _upload_btn.addEventListener('click', () => {
    Uploader.processQueue()
  })
}
