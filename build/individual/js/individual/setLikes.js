/**
 * いいね、を更新する
 */
const setLikes = (param = {}) => {
  param.csrf_token = CSRF_TOKEN

  console.log(param)

  return $.ajax({
    url: './assets/api/likes.php',
    method: 'POST',
    dataType: 'json',
    data: param
  })
}