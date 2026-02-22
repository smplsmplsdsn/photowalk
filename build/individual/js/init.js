const Photos = {}

Photos.ls = Fn.storageLS('photowalk') || {}

$(() => {
  $('.js-page').html(page_init)

  if (PARAM_EVENT_NAME != '') {
    $('.js-form-event input[name="id"]').val(PARAM_EVENT_NAME)
  } else if (Photos.ls.event_name) {
    $('.js-form-event input[name="id"]').val(Photos.ls.event_name)
  }
})