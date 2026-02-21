// NOTE: Photos を有効にするために $() している
$(() => {

  // 初期表示
  $(document).on('submit', '.js-form-event', function () {
    const _form = $(this),
          _error = $('.js-form-error', _form),
          _input = $('[name="id"]', _form),
          val = _input.val()

    _error.html('').css({
      'visibility': 'hidden'
    })

    async function ajax() {
      try {
        const d = await getEventInfo(val)

        // ガード
        if (d.status != 'success') {
          _error.html(d.message).css({
            'visibility': 'visible'
          })
          return false
        }

        Photos.event_name = val
        Photos.photowalkers = d.photowalkers

        Photos.ls.event_name = val
        Fn.storageLS('photowalk', Photos.ls)

        $('.js-page').html(page_account).hide()
        $('.js-account-title').html(d.title)
        $('.js-account-time').html(d.date)
        $('.js-account-excerpt').html(d.excerpt)
        $('.js-page').show()

        if (Photos.ls.uid) {
          $('.js-form-account input[name="display_name"]').val(Photos.ls.uid)
        }
      } catch (error) {
        $('.js-page').html(page_error)
        return false
      }
    }
    ajax()

    return false
  })

  // アカウント登録（はじめて）
  $(document).on('click', '.js-account-firsttime', () => {
    $('.js-account[data-flow="1"]').hide()
    $('.js-account[data-flow="2-1"]').show()
  })

  // アカウント登録（2回目以降）
  $(document).on('click', '.js-account-returning', () => {
    $('.js-account[data-flow="1"]').hide()
    $('.js-account[data-flow="2-2"]').show()
  })

  // アカウント登録（もどる）
  $(document).on('click', '.js-link-account', () => {
    $('.js-account').hide()
    $('.js-account[data-flow="1"]').show()
  })

  // アカウント作成
  $(document).on('click', '.js-account-create', function () {

    $(this).replaceWith(`
      <button class="js-account-create-number">
        <span class="animation-blinker" style="font-size:14px;">
          <span class="ja">作成中...</span>
          <span class="en">Creating...</span>
        </span>
      </button>
    `)

    async function ajax() {
      try {
        const d = await getCreateUser()

        // ガード
        if (d.status != 'success') {
          $('.js-page').html(page_error)
          return false
        }

        Photos.handle = d.uid

        Photos.ls.uid = d.uid
        Fn.storageLS('photowalk', Photos.ls)

        $('.js-account-create-number').replaceWith(`
          <p><strong>${d.uid}</strong></p>
          <p><button class="js-account-next" style="width:5em;">OK</button></p>
        `)
        $('.js-account[data-flow="1"]').hide()
        $('.js-account[data-flow="2-1"]').show()
      } catch (error) {
        $('.js-page').html(page_error)
        return false
      }
    }
    ajax()
  })

  Photos.showList = () => {
    const photowalkers = (Photos.is_list_fixed)? Photos.photowalkers : Fn.shuffle(Photos.photowalkers, true)
    let html_photowalkers_list = ``,
        i,
        key

    Photos.is_list_fixed = false

    $('.js-page').html(page_list).hide()

    for (i = 0; i < photowalkers.length; i++) {
      const d = photowalkers[i]

      html_photowalkers_list += `
        <li>
          <a class="js-link-photowalker" data-name="${d.name}"><span>${d.name}</span></a>
        </li>
      `
    }

    $('.js-photowalkers-list').html(html_photowalkers_list)

    if (Photos.likes) {

      for (key in Photos.likes) {
        const tgt = $(`.js-link-photowalker[data-name="${key}"]`).closest('li')

        tgt.addClass('selected')
        $('.js-photowalkers-list').append(tgt)
      }
    }

    $('.js-page').show()
  }

  $(document).on('click', '.js-account-next', Photos.showList)


  // ログイン（2回目以降）
  $(document).on('submit', '.js-form-account', function () {
    const _form = $(this),
          _error = $('.js-form-error', _form),
          _input = $('[name="display_name"]', _form),
          val = _input.val()

    _error.css({
      'visibility': 'hidden'
    })

    // 有効値か判別する
    async function ajax() {
      try {
        const d = await getUser(val)

        // ガード
        if (d.status != 'success') {
          _error.html(d.message).css({
            'visibility': 'visible'
          })
          return false
        }

        Photos.handle = d.user.handle
        Photos.likes = []

        d.likes.forEach(item => {
          const key = item.photowalker

          if (!Photos.likes[key]) {
            Photos.likes[key] = []
          }

          Photos.likes[key].push(item.filename)
        })

        Photos.showList()

        Photos.ls.uid = d.user.handle
        Fn.storageLS('photowalk', Photos.ls)
      } catch (error) {
        $('.js-page').html(page_error)
        return false
      }
    }
    ajax()

    return false
  })

  // リスト
  $(document).on('click', '.js-link-photowalker', function () {
    const photowalker = $(this).attr('data-name'),
          images = Photos.photowalkers.find(item => item.name === photowalker)?.images || [],
          images_rand = Fn.shuffle(images)

    let html_images = '',
        i

    $('.js-page').html(page_photos).hide()

    for (i = 0; i < images_rand.length; i++) {
      html_images += `<li data-filename="${images_rand[i]}"><img src="./assets/photo.php?filename=${Photos.event_name}/${photowalker}/${images_rand[i]}" loading="lazy"></li>`
    }

    $('.js-photos-list').html(html_images)

    Photos.selected_photowaker = photowalker

    if (Photos.likes && Photos.likes[photowalker]) {

      for (i = 0; i < Photos.likes[photowalker].length; i++) {
        $(`.js-photos-list li[data-filename="${Photos.likes[photowalker][i]}"]`).addClass('selected')
      }

      $('.js-photos-selected-num').html($(`.js-photos-list li.selected`).length)
    }

    if ($('.js-photos-list li.selected').length === 0) {
      $('.js-photos-selected, .js-photos-submit').css({
        opacity: 0.3
      })
    }

    $('.js-page').show()
  })

  // 画像リスト
  $(document).on('click', '.js-photos-list li', function () {
    const _this = $(this)

    if (_this.hasClass('selected')) {
      _this.addClass('temp')
      _this.removeClass('selected')
    } else {
      _this.removeClass('temp')
      _this.addClass('selected')
    }

    const selected_num = $('.js-photos-list li.selected').length,
          _button = $('.js-photos-selected, .js-photos-submit')

    $('.js-photos-selected-num').html(selected_num)

    if (selected_num > 0) {
      _button.css({
        opacity: 1
      })
    } else {
      _button.css({
        opacity: 0.3
      })
    }
  })

  // 画像表示レイアウト
  $(document).on('click', '.js-photos-layout', function () {
    const layout = ($('.js-photos-list').attr('data-layout') === 'one')? 'column' : 'one'

    $('.js-photos-list').attr('data-layout', layout)
  })

  // 画像背景
  $(document).on('click', '.js-photos-bg', () => {
    const _photos = $('.js-photos'),
          bg_photos = (_photos.attr('data-bg') === 'white') ? 'black' : 'white'

    _photos.attr('data-bg', bg_photos)
  })

  // 画像 次へ
  $(document).on('click', '.js-photos-selected', () => {

    // ガード
    if ($('.js-photos-list li.selected').length === 0) {
      return false
    }

    if ($('.js-photos-list li.selected').length > 5) {
      $('.js-photos-list-excerpt').addClass('show')
    } else {
      $('.js-photos-list-excerpt').removeClass('show')
    }

    $('.js-photos-list li.temp').removeClass('temp')
    $('.js-photos').attr('data-type', 'submit')
    window.scrollTo(0, 1)
  })

  // 画像 決定
  $(document).on('submit', '.js-form-submit', () => {
    let toast

    // ガード
    if ($('.js-photos-list li.selected').length === 0) {
      return false
    }

    // ガード
    if ($('.js-photos-list li.selected').length > 5) {

      $('.js-photos-toast').addClass('show').on('click', function () {
        $(this).removeClass('show')
      })

      if (toast) clearTimeout(toast)
      toast = setTimeout(() => {
        $('.js-photos-toast').removeClass('show')
      }, 3000)
      return false
    }

    async function ajax() {
      const param = {}

      param.event_name = Photos.event_name
      param.uid = Photos.handle
      param.photowalker = Photos.selected_photowaker
      param.images = []

      $('.js-photos-list li.selected').each(function () {
        param.images.push($(this).attr('data-filename'))
      })

      $('.js-page').html(page_loading)

      try {
        const d = await setLikes(param)

        // ガード
        if (d.status != 'success') {
          $('.js-page').html(page_error)
          return false
        }

        Photos.likes[param.photowalker] = param.images

        $('.js-page').html(page_complete)
      } catch (error) {
        $('.js-page').html(page_error)
        console.error('error', error)
        return false
      }
    }
    ajax()

    return false
  })

  // 画像もどる
  $(document).on('click', '.js-photos-back', () => {
    const type = $('.js-photos').attr('data-type')

    switch (type) {
      case 'confirm':
        Photos.is_list_fixed = true
        Photos.showList()
        break
      case 'submit':
        $('.js-photos').attr('data-type', 'confirm')
        $('.js-photos-list li').removeClass('temp')
        break
      // default なし
    }
  })

  // 完了
  $(document).on('click', '.js-link-complete', Photos.showList)
})

