/**
 * イベント情報を取得する
 *
 * NOTE:
 *  getEventInfoWithImages : DB + 画像情報
 *  getEventInfo           : DBのみ
 */
const getEventInfoWithImages = (val = '') => {
  return $.ajax({
    url: './assets/api/event_info.php',
    method: 'POST',
    dataType: 'json',
    data: {
      event_name: val,
      csrf_token: CSRF_TOKEN
    }
  })
}