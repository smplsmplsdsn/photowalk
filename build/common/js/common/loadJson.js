/**
 * 個別にJSONファイルを呼び出す場合
 *
 * @param {string} file: ファイルパス
 * @returns array|null
 *
 * 使いどころ
 * レンダリング後に、非同期でjsonデータを取得したい場合に使う
 * 初期読み込み時に取得したい場合は、loadMultipleJson を使う
 *
 * 使い方 1 即時関数
;(async () => {
  const userData = await Fn.loadJson('sample.json')
  console.log(userData)
})()
 *
 * 使い方 2 fetcb
Fn.loadJson('sample.json').then(userData => {
  console.log(userData)
})
 *
 * 使い方 3 type="module"
<script type="module">
  const userData = await Fn.loadJson('sample.json')
  console.log(userData)
</script>
 */
Fn.loadJson = async (file) => {
  try {
    const res = await fetch(file)

    if (!res.ok) {
      throw new Error('HTTPエラー: ' + res.status)
    }

    return await res.json()
  } catch (err) {
    console.error('JSON取得失敗:', err)
    return null
  }
}