const Photos = {}

Photos.ls = Fn.storageLS('photowalk') || {}

// GETパラメータにイベントIDがある場合は更新する
if (PARAM_EVENT_NAME != '') {
  Photos.ls.event_name = PARAM_EVENT_NAME
  Fn.storageLS('photowalk', Photos.ls)
}

$(() => {
  $('.js-page').html(page_init)

  if (Photos.ls.event_name) {
    $('.js-form-event input[name="id"]').val(Photos.ls.event_name)
  }
})