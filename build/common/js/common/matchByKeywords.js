/**
 * keywords が str に含まれているか判別する
 * @param {string} str : 検索対象の全文字列
 * @param {array} keywords : 検索ワードの配列
 * @param {string} mode : ANDもしくはOR
 * @returns boolean
 */
Fn.matchByKeywords = (str = '', keywords = [], mode = 'AND') => {
  if (!Array.isArray(keywords) || keywords.length === 0) return true

  const target = str || '',
        m = (mode === 'OR') ? 'OR' : 'AND'

  return m === 'AND'
    ? keywords.every(k => target.includes(Fn.textNormalizeForSearch(k)))
    : keywords.some(k => target.includes(Fn.textNormalizeForSearch(k)))
}