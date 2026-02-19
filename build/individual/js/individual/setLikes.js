/**
 * いいね、を更新する
 */
const setLikes = (param = {}) => {
  return $.ajax({
    url: './assets/api/likes.php',
    method: 'POST',
    dataType: 'json',
    data: param
  })
}