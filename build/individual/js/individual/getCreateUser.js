/**
 * イベント情報を取得する
 */
const getCreateUser = () => {
  return $.ajax({
    url: './assets/api/create_user.php',
    method: 'POST',
    dataType: 'json',
    data: {
      csrf_token: CSRF_TOKEN
    }
  })
}