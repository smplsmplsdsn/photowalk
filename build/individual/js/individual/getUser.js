/**
 * 投票できるユーザーか判別する
 *
 * @param {string} val : ユーザーID
 */
const getUser = (val = '') => {
  return $.ajax({
    url: './assets/api/get_user.php',
    method: 'POST',
    dataType: 'json',
    data: {
      uid: val,
      event_id: Photos.event_id,
      csrf_token: CSRF_TOKEN
    }
  })
}