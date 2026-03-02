/**
 * イベント情報を作成・更新する
 */
const setEventInfo = (data) => {
  data.append('csrf_token', CSRF_TOKEN)

  return $.ajax({
    url: './assets/api/set_event_info.php',
    method: 'POST',
    dataType: 'json',
    data,
    processData: false,
    contentType: false
  })
}