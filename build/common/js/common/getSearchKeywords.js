/**
 * 複合語ワードを分割する
 *
 * @param {*} str : 検索文字列
 * @returns array スペースの文字列を区切って配列にする
 */
Fn.getSearchKeywords = (str = '') => {
  if (typeof str !== 'string') return []

  return str
    .normalize('NFKC')   // 全角スペース・半角統一
    .trim()
    .split(/\s+/)
    .filter(Boolean)
}