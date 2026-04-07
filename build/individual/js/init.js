const Photos = {}

Photos.likes = {}
Photos.ls = Fn.storageLS('photowalk') || {}

if (typeof PARAM_EVENT_ID === 'undefined') {
  PARAM_EVENT_ID = ''
}

$(() => {
  $('.js-page').html(page_init)

  if (PARAM_EVENT_ID != '') {
    $('.js-form-event input[name="id"]').val(PARAM_EVENT_ID)
  } else if (Photos.ls.event_id) {
    $('.js-form-event input[name="id"]').val(Photos.ls.event_id)
  }

  // 終了リンク
  $(document).on('click', '.js-link-end', () => {
    const event_id = Fn.getParam('event_id'),
          href = (event_id != '')? `./report.php?event_id=${event_id}`: `./`

    location.href = href
    return false
  })
})

// 右クリック時で画像表示時対策として、CSRF対策用のセッションを再取得する
document.addEventListener('contextmenu', async e => {
  const res = await fetch('/assets/api/csrf_token_for_img.php')
  const data = await res.json()
})

Fn.setLang()