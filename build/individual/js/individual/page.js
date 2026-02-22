const page_loading = `
<div class="flex-center">
  <span class="animation-blinker">loading...</span>
</div>
`

const page_error = `
<div class="flex-center">
  <div class="error">
    <p class="js-error">
      <span class="ja">想定外のエラーが発生しました。<br>時間を置いてもう一度お試しください。</span>
      <span class="en">Something went wrong.<br>Please try again in a little while.</span>
    </p>
    <p><a href="/">はじめに戻る</a></p>
  </div>
</div>
`

const page_init = `
<section class="flex-center">
  <div class="init">
    <h1>
      <span class="ja">自薦＆他薦で決める一枚</span>
      <span class="en">Chosen — By You, By Them</span>
    </h1>
    <div class="init-excerpt">
      <div class="ja">
        <p>「誰が」「どの写真」を選んだか分からないから、完全に自分視点になれるのがイイ！</p>
        <p>さっそくイベントIDを入力してはじめましょう！</p>
      </div>
      <div class="en">
        <p>Because you don’t know who chose which photo, you can fully experience it from your own perspective — and that’s what makes it great!</p>
        <p>Enter the Event ID and get started right away!</p>
      </div>
    </div>
    <form class="js-form-event">
      <div>
        <input type="text" name="id" value="">
        <button type="submit">
          <span class="ja">スタート</span>
          <span class="en">START</span>
        </button>
      </div>
      <p class="form-error js-form-error" style="visibility:hidden;"></p>
    </form>
  </div>
</section>
`

const page_account = `
<section class="flex-center">
  <section class="account js-account" data-flow="1">
    <p class="bar2"><a class="bar2-link-back" onclick="location.href='./';">終了する</a></p>
    <hgroup>
      <h2 class="js-account-title"><span class="ja"></span><span class="en"></span></h2>
      <p class="js-account-time"></p>
    </hgroup>
    <div class="account-excerpt">
      <p class="js-account-excerpt" style="margin-bottom:10px;"><span class="ja"></span><span class="en"></span></p>

      <div class="ja">
        <p>選出方法ですが、自薦＆他薦で選出します。<br>フォトウォーカーごとに「おっ！」となった写真を選んで、一人ずつの一枚を決めましょう！</p>
        <p>投票IDはありますか？</p>
      </div>
      <div class="en">
        <p>For the selection method, we’ll accept both self-nominations and nominations by others.<br>For each photowalker, choose the photos that really catch your eye (“Wow!” photos) and decide on one photo per person.</p>
        <p>Do you have your voting ID?</p>
      </div>
    </div>
    <div class="flex-button">
      <button type="button" class="js-account-firsttime">
        <span class="ja">はじめて</span>
        <span class="en">First Time</span>
      </button>
      <button type="button" class="js-account-returning">
        <span class="ja">あります！</span>
        <span class="en">Returning</span>
      </button>
    </div>
  </section>

  <section class="account js-account" data-flow="2-1" style="display:none;">
    <p class="bar2"><a class="bar2-link-back js-link-account">戻る</a></p>
    <p>
      <span class="ja">あなたの投票IDを作成します。<br>再発行はできませんので、作成後はキャプチャ撮るとかどこかにメモしておいてくださいね。</span>
      <span class="en">We will create your voting ID.<br>It cannot be reissued, so please take a screenshot or note it down somewhere after it’s created.</span>
    </p>
    <p>
      <button type="button" class="js-account-create">
        <span class="ja">作成する</span>
        <span class="en">Create</span>
      </button>
    </p>
  </section>

  <section class="account js-account" data-flow="2-2" style="display:none;">
    <p class="bar2">
      <a class="bar2-link-back js-link-account">
        <span class="ja">戻る</span>
        <span class="en">Back</span>
      </a>
    </p>
    <form class="js-form-account">
      <h2>
        <span class="ja">投票ID</span>
        <span class="en">Vote ID</span>
      </h2>
      <div>
        <input type="text" name="display_name" value="">
        <button type="submit">
          <span class="ja">写真を見る</span>
          <span class="en">View Photos</span>
        </button>
      </div>
      <p class="form-error js-form-error" style="visibility:hidden;margin-bottom:10px;">投票IDが一致しません。<br>ID名を確かめてください。</p>
    </form>
  </section>
</section>
`

const page_list = `
<section class="flex-center">
  <p class="bar2">
    <a class="bar2-link-back" onclick="location.href='./';">
      <span class="ja">終了する</span>
      <span class="en">Done</span>
    </a>
  </p>
  <div class="list">
    <h2>
      <span class="ja">フォトウォーカー</span>
      <span class="en">Photowalkers</span>
    </h2>
    <ul class="js-photowalkers-list"></ul>
    <p class="ja">ランダム未投票順</p>
  </div>
</section>
`

const page_photos = `
<section class="photos js-photos" data-type="confirm" data-bg="white">
  <nav class="bar bar--top">
    <button type="button" class="icon-back js-photos-back"><span></span></button>
    <div>
      <button type="button" class="js-photos-layout">
        <span class="icon-column-and-one">
          <span class="icon-column">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
          </span>
          <span class="icon-one">
            <span></span>
          </span>
        </span>
      </button>
      <button type="button" class="icon-bg js-photos-bg">
        <span class="icon-bg-inner">
          <span></span>
          <span></span>
        </span>
      </button>
    </div>
  </nav>
  <div class="photos-inner">
    <p class="photos-list-excerpt" data-type="confirm">
      <span class="ja">まずは「おっ！」となった写真をタップしてください。<br>右上のアイコンで表示切り替えもできます。</span>
      <span class="en">Choose the photo that caught your eye.</span>
    </p>
    <p class="photos-list-excerpt js-photos-list-excerpt" data-type="submit">
      <span class="ja">続いて、5枚に絞り込んでから投票してください。</span>
      <span class="en">If there are more than five images, please limit them to five.</span>
    </p>
    <ul class="photos-list js-photos-list" data-layout="column" data-bg="white"></ul>
  </div>
  <nav class="bar bar--bottom-confirm">
    <button type="button" class="photos-selected js-photos-selected">
      <span class="ja">選択した画像を確認する »</span>
      <span class="en">NEXT</span>
    </button>
  </nav>
  <nav class="bar bar--bottom-submit">
    <span>
      <span class="js-photos-selected-num">0</span> / 5
    </span>
    <form class="js-form-submit">
      <button type="submit" class="photos-selected js-photos-submit">
        <span class="ja">投票する</span>
        <span class="en">Vote</span>
      </button>
    </form>
  </nav>

  <div class="photos-toast js-photos-toast">5枚まで!!</div>
</section>
`

const page_complete = `
<section class="flex-center">
  <div class="complete">
    <p>
      <span class="ja">ありがとうございます！<br>投票を受け付けました。</span>
      <span class="en">Thank you!</span>
    </p>
    <p>
      <button type="button" class="js-link-complete">
        <span class="ja">リストに戻る</span>
        <span class="en">Back to List</span>
      </button>
    </p>
  </div>
</section>
`