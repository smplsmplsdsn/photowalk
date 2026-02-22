const Photos = {}

Photos.ls = Fn.storageLS('photowalk') || {}

$(() => {
  $('.js-page').html(page_init)

  if (PARAM_EVENT_ID != '') {
    $('.js-form-event input[name="id"]').val(PARAM_EVENT_ID)
  } else if (Photos.ls.event_id) {
    $('.js-form-event input[name="id"]').val(Photos.ls.event_id)
  }
})