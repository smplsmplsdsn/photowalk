/**
 * イベント情報を取得する
 *
 * NOTE:
 *  getEventInfoWithImages : DB + 画像情報
 *  getEventInfo           : DBのみ
 */
const getEventInfo = (val = '') => {
  return $.ajax({
    url: './assets/api/event_info_db_only.php',
    method: 'POST',
    dataType: 'json',
    data: {
      event_id: val,
      csrf_token: CSRF_TOKEN
    }
  })
}