/**
 * まとめてJSONファイルを呼び出す場合
 * 前提： Fn.loadJson() があること
 *
 * @param {array} files: ファイルパスの配列
 * @returns array filesで渡された配列数分の返却、返却値はjsonデータ|null
 */
Fn.loadJsonMultiple = async (files) => {
  const results = await Promise.all(
    files.map(file => Fn.loadJson(file))
  )
  return results
}