/**
 *
 * @param {object} obj {
 *  {function} func : すべてのキューが完了した際に何かを実行する場合
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
    ]
  }

  const button_label = {
    delete: '削除',
    retry: '再試行'
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
      active_count: 0
    }

    const setStatus = (file_obj, status, error_code = '') => {
      file_obj.status = status
      file_obj.error_code = error_code
      updateFileCard(file_obj)
    }

    const checkAllCompleted = () => {
      const hasUploading = state.files.some(f => f.status === 'uploading'),
            hasWaiting = state.files.some(f => f.status === 'waiting'),
            hasDone = state.files.some(f => f.status === 'done')

      if (!hasUploading && !hasWaiting && hasDone && obj.func) {
        obj.func()
      }
    }

    const addFiles = (filelist, { auto_start = false } = {}) => {
      Array.from(filelist).forEach(file => {
        const file_obj = {
          id: crypto.randomUUID(),
          file,
          status: 'waiting',
          progress: 0,
          error_code: ''
        }

        switch (true) {
          case (file.size === 0):
            file_obj.status = 'error'
            file_obj.error_code = 'NO_FILE'
            break
          case (config.max_filesize > 0 && file.size > config.max_filesize):
            file_obj.status = 'error'
            file_obj.error_code = 'FILE_TOO_LARGE'
            break
          default:
            const filename = file.name.toLowerCase()

            // NOTICE: heic/heif のときは、file.type が ''（空）になる場合があるためのフォールバック対応
            if (!(
                config.allowed_types.includes(file.type) ||
                filename.endsWith('.heic') ||
                filename.endsWith('.heif')
            )) {
              file_obj.status = 'error'
              file_obj.error_code = 'INVALID_TYPE'
            } else {
              file_obj.preview_url = URL.createObjectURL(file)
            }
        }

        state.files.push(file_obj)
        _container.appendChild(createFileCard(file_obj))
      })

      updateButton()

      if (auto_start) {
        processQueue()
      }
    }

    const processQueue = () => {

      while (state.active_count < config.concurrency) {
        const next = state.files.find(f => f.status === 'waiting')

        if (!next) {
          return
        }

        uploadFile(next)
      }
    }

    const uploadFile = (file_obj) => {
      const xhr = new XMLHttpRequest(),
            form_data = new FormData()

      form_data.append('image', file_obj.file)
      form_data.append('csrf_token', CSRF_TOKEN)
      form_data.append('dir1', DIR1)

      setStatus(file_obj, 'uploading')
      state.active_count++

      xhr.upload.addEventListener('progress', e => {

        if (e.lengthComputable) {
          file_obj.progress = Math.min(96, Math.round((e.loaded / e.total) * 100))
          updateFileCard(file_obj)
        }
      })

      xhr.responseType = 'json'
      xhr.timeout = 30000

      const finalize = () => {
        state.active_count--
        updateButton()
        processQueue()
        checkAllCompleted()
      }

      const handleResult = () => {
        const res = xhr.response

        let error_code

        // NOTE: 成功は即終了
        if (res?.status === 'success') {
          file_obj.progress = 100
          setStatus(file_obj, 'done')
          finalize()
          return
        }

        // NOTE: JSONエラー優先
        if (res?.status === 'error') {
          error_code = res.code || 'UPLOAD_ERROR'
        } else if (xhr.status >= 400) {
          error_code = xhr.status
        } else {
          error_code = 'OTHER'
        }

        if (error_code) {
          file_obj.progress = 0
          setStatus(file_obj, 'error', error_code)
        }

        finalize()
      }

      xhr.addEventListener('load', () => {
        handleResult()
      })

      xhr.addEventListener('error', () => {
        setStatus(file_obj, 'error', 'SERVER_CONNECT_ERROR')
        finalize()
      })

      xhr.addEventListener('timeout', () => {
        setStatus(file_obj, 'error', 'TIMEOUT_ERROR')
        finalize()
      })

      xhr.open('POST', config.endpoint)
      xhr.send(form_data)
    }

    const fadeOutAndRemove = (file_obj) => {
      file_obj.el.classList.add('is-fadeout')

      setTimeout(() => {

        if (file_obj.preview_url) {
          URL.revokeObjectURL(file_obj.preview_url)
        }
        file_obj.el.remove()
        state.files = state.files.filter(f => f !== file_obj)
      }, config.fadeout_duration)
    }

    const getStatusLabel = (file_obj) => {
      return (file_obj.status === 'error' && file_obj.error_code)
        ? status_label[file_obj.error_code] || file_obj.error_code
        : status_label[file_obj.status] || file_obj.status
    }

    const updateFileCard = (file_obj) => {
      file_obj.barEl.style.width = file_obj.progress + '%'
      file_obj.statusEl.textContent = getStatusLabel(file_obj)
      file_obj.el.className = `uploader-filecard is-${file_obj.status}`
      file_obj.updateButton?.()

      if (file_obj.status === 'done') {
        fadeOutAndRemove(file_obj)
      }
    }


    const createFileCard = (file_obj) => {
      const card = document.createElement('div'),
            text_wrap = document.createElement('div'),
            name = document.createElement('div'),
            img_wrap = document.createElement('div'),
            img = document.createElement('img'),
            progress_wrap = document.createElement('div'),
            bar = document.createElement('div'),
            status = document.createElement('div'),
            action_btn = document.createElement('button')

      card.className = `uploader-filecard is-${file_obj.status}`
      text_wrap.className = 'uploader-text'
      name.className = 'uploader-name'
      img_wrap.className = 'uploader-thumbnail'
      progress_wrap.className = 'uploader-filecardprogress'
      bar.className = 'uploader-filecardbar'
      status.className = 'uploader-filecardstatus'
      action_btn.type = 'button'
      action_btn.className = 'uploader-filecardremove'
      action_btn.textContent = button_label.delete

      name.textContent = file_obj.file.name
      img.src = file_obj.preview_url

      img.onerror = () => {
        img.remove()

        img_wrap.classList.add('uploader-nothumbnail')
        img_wrap.onclick = () => {
          fadeOutAndRemove(file_obj)
        }
      }

      const updateButton = () => {

        if (file_obj.status === 'error') {
          action_btn.textContent = button_label.retry
          action_btn.onclick = () => {
            file_obj.status = 'waiting'
            file_obj.error_code = ''
            updateFileCard(file_obj)
            Uploader.processQueue()
          }

          Uploader.uploadFile = (f) => {
            const xhr = new XMLHttpRequest()
            const form_data = new FormData()

            form_data.append('image', file_obj.file)
            form_data.append('csrf_token', CSRF_TOKEN)
            setStatus(file_obj, 'uploading')
            state.active_count++

            xhr.responseType = 'json'
            xhr.timeout = 30000
            xhr.addEventListener('load', () => {
              const res = xhr.response
              if (res?.status === 'success') setStatus(file_obj, 'done')
              else setStatus(file_obj, 'error', res?.code || 'UPLOAD_ERROR')
              state.active_count--
              updateButton()
              Uploader.processQueue()
            })
            xhr.addEventListener('error', () => {
              setStatus(file_obj, 'error', 'SERVER_CONNECT_ERROR')
              state.active_count--
              updateButton()
              Uploader.processQueue()
            })
            xhr.open('POST', config.endpoint)
            xhr.send(form_data)
          }

          Uploader.processQueue()
        } else {
          action_btn.textContent = button_label.delete
          action_btn.onclick = () => fadeOutAndRemove(file_obj)
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

      file_obj.el = card
      file_obj.barEl = bar
      file_obj.statusEl = status
      file_obj.updateButton = updateButton

      updateFileCard(file_obj)

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


  /*  DOM Events */
  const _container = document.querySelector('.js-uploader-filelist'),
        _drop_area = document.querySelector('.js-uploader-droparea'),
        _input = document.querySelector('.js-uploader-input'),
        _upload_btn = document.querySelector('.js-uploader-button')

  _drop_area.addEventListener('click', () => _input.click())

  _input.addEventListener('change', e => {
    Uploader.addFiles(e.target.files, { auto_start: false })
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
    Uploader.addFiles(e.dataTransfer.files, { auto_start: true })
  })

  _upload_btn.addEventListener('click', () => {
    Uploader.processQueue()
  })
}
