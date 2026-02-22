<?php
include_once(__DIR__ . '/../functions/init.php');
ini_set('display_errors', $is_https ? 0 : 1);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$event_id = $_GET['event_id'] ?? '';
$event_id_temp = $_GET['event_id_temp'] ?? '';

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>登録画面</title>
  <meta name="description" content="">
  <link rel="stylesheet" href="/assets/css/common.min.css?<?php echo filemtime('./assets/css/common.min.css'); ?>">
  <link rel="stylesheet" href="/assets/css/register.css?<?php echo filemtime('./assets/css/register.css'); ?>">
</head>
<body>
  <div class="flex-center">
    <form class="js-form-register" method="post">
      <h1>
        <span class="ja">イベント情報</span>
        <span class="en">Event information</span>
      </h1>
      <dl>
        <?php if ($event_id === ''): ?>
          <div>
            <dt>
              <span class="ja">イベントID</span>
              <span class="en">Event ID</span>
            </dt>
            <dd><input type="text" name="event_id" value="<?= $event_id_temp ?>"></dd>
          </div>
        <?php else: ?>
          <input type="hidden" name="event_id" value="<?= $event_id ?>">
        <?php endif; ?>

        <div>
          <dt>
            <span class="ja">イベント名（日本語）</span>
            <span class="en">Event Name (Japanese)</span>
          </dt>
          <dd><input type="text" name="title_ja" value=""></dd>
        </div>
        <div>
          <dt>
            <span class="ja">イベント名（英語）</span>
            <span class="en">Event Name (English)</span>
          </dt>
          <dd><input type="text" name="title_en" value=""></dd>
        </div>
        <div>
          <dt>
            <span class="ja">イベント概要（日本語）</span>
            <span class="en">Event Description (Japanese)</span>
          </dt>
          <dd><textarea type="text" name="excerpt_ja"></textarea>
        </div>
        <div>
          <dt>
            <span class="ja">イベント概要（英語）</span>
            <span class="en">Event Description (English)</span>
          </dt>
          <dd><textarea type="text" name="excerpt_en"></textarea>
        </div>
        <div>
          <dt>
            <span class="ja">イベント開催日</span>
            <span class="en">Event Date</span>
          </dt>
          <dd><input type="date" name="event_date" value="<?= $today ?>"></dd>
        </div>
        <div>
          <dt>
            <span class="ja">結果発表日</span>
            <span class="en">Results Announcement Date</span>
          </dt>
          <dd>
            <input type="date" name="vote_counting_date_at" value="<?= $today ?>">
            <input type="time" name="vote_counting_time_at" value="00:00">
          </dd>
        </div>
        <div>
          <dt>
            <span class="ja">ステータス</span>
            <span class="en">Status</span>
          </dt>
          <dd class="status_label">
            <label>
              <input type="radio" name="status" value="1" checked>
              <span>
                <span class="ja">公開</span>
                <span class="en">Public</span>
              </span>
            </label>
            <label>
              <input type="radio" name="status" value="0">
              <span>
                <span class="ja">限定公開</span>
                <span class="en">Private</span>
              </span>
            </label>
          </dd>
        </div>
      </dl>
      <div class="sticky">
        <p class="message-success js-success" style="display:none;"></p>
        <p class="message-error js-error" style="display:none;">
          <span class="ja">登録・更新できませんでした。</span>
          <span class="en">Failed to register or update.</span>
        </p>
        <p style="margin:auto;">
          <button type="submit">
            <?php if ($event_id === ''): ?>
            <span class="ja">作成する</span>
            <span class="en">Create</span>
            <?php else: ?>
            <span class="ja">更新する</span>
            <span class="en">Update</span>
            <?php endif; ?>
          </button>
        </p>
      </div>
    </form>
  </div>
  <script src="/assets/js/jquery-4.0.0.min.js"></script>
  <script>
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?>'
    const PARAM_EVENT_ID = '<?= $event_id ?>'
  </script>
  <script src="/assets/js/common.min.js?<?php echo filemtime('./assets/js/common.min.js'); ?>"></script>
  <script>
    $(() => {

      // 更新用：デフォルト値をセットする
      if (PARAM_EVENT_ID != '') {

        async function ajax() {
          $('.js-error').hide()

          try {
            const d = await getEventInfo(PARAM_EVENT_ID)

            // ガード
            if (d.status != 'success') {
              location.href = './register.php?event_id_temp=' + PARAM_EVENT_ID
              return false
            }

            const data = d.data
            const [date_part, time_part] = data.vote_counting_at.split(' ')

            $('[name="event_id"]').val(data.event_id)
            $('[name="title_ja"]').val(data.title_ja)
            $('[name="title_en"]').val(data.title_en)
            $('[name="excerpt_ja"]').val(data.excerpt_ja)
            $('[name="excerpt_en"]').val(data.excerpt_en)
            $('[name="event_date"]').val(data.event_date)
            $('[name="vote_counting_date_at"]').val(date_part)
            $('[name="vote_counting_time_at"]').val(time_part.slice(0,5))
            $(`[name="status"][value="${data.status}"]`).prop('checked', true)
          } catch (error) {
            location.href = './register.php'
            return false
          }
        }
        ajax()
      }

      // 登録用
      $('.js-form-register').on('submit', () => {

        // 更新時は、event_id の値は変更しないようにする
        // NOTICE: FormDataを使うため、disabled は使用してはいけない
        if (PARAM_EVENT_ID != '') {
          $('input[name="event_id"]').val(PARAM_EVENT_ID)
        }

        async function ajax() {
          $('.js-error').hide()

          try {
            const form = document.querySelector('.js-form-register'),
                  data = new FormData(form)

            data.append('create', (PARAM_EVENT_ID != '')? 'update' : 'new')

            const d = await setEventInfo(data)

            // ガード
            if (d.status != 'success') {
              $('.js-error').html(d.message).show()
              return false
            }

            if (PARAM_EVENT_ID === '') {
              location.href = './register.php?event_id=' + $('input[name="event_id"]').val()
            } else {
              $('.js-success').html(d.message).show()
            }
          } catch (error) {
            $('.js-error').html(`SYSTEM ERROR`).show()
            return false
          }
        }

        ajax()
        return false
      })
    })
  </script>
</body>
</html>
