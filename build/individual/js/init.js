const Photos = {}

Photos.ls = Fn.storageLS('photowalk') || {}

$(() => {
  $('.js-page').html(page_init)

  if (Photos.ls.event_name) {
    $('.js-form-event input[name="id"]').val(Photos.ls.event_name)
  }
})