/**
 * いいね、を更新する
 */
const setLikes = (param = {}) => {
  param.csrf_token = CSRF_TOKEN

  return $.ajax({
    url: './assets/api/likes.php',
    method: 'POST',
    dataType: 'json',
    data: param
  })
}