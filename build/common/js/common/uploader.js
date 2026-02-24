Fn.uploader = () => {
  const config = {
    concurrency: 3,
    endpoint: '/upload.php'
  }

  const STATUS_LABEL = {
    waiting: '待機中',
    uploading: 'アップロード中',
    done: 'アップロード完了しました',
    error: 'エラーが発生しました'
  }

  const Uploader = (() => {

    const state = {
      files: [],
      activeCount: 0
    }

    const addFiles = (fileList, { autoStart = false } = {}) => {

      Array.from(fileList).forEach(file => {
        state.files.push({
          id: crypto.randomUUID(),
          file,
          status: 'waiting',
          progress: 0
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

      xhr.addEventListener('load', () => {

        console.log('STATUS:', xhr.status)
        console.log('RESPONSE:', xhr.responseText)

        try {
          const json = JSON.parse(xhr.responseText)
          console.log('JSON:', json)
        } catch (e) {
          console.warn('JSON parse error')
        }

        if (xhr.status === 200) {
          fileObj.status = 'done'
        } else {
          fileObj.status = 'error'
        }

        state.activeCount--
        render()
        processQueue()
      })

      xhr.addEventListener('error', () => {
        fileObj.status = 'error'
        state.activeCount--
        render()
        processQueue()
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
        status.textContent = STATUS_LABEL[fileObj.status] || fileObj.status

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
