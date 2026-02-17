/**
 * ISO形式から年月日を取得する
 *
 * @param {string} iso : ex) '2100-01-01T00:20'
 * @param {string} ptn : 'ymd|md|d'
 * @returns
 */
Fn.getYmdJa = (iso, ptn = 'ymd') => {
  const date = new Date(iso)

  switch (ptn) {
    case 'd':
      formatted = `${date.getDate()}日`
      break
    case 'md':
      formatted = `${date.getMonth() + 1}月${date.getDate()}日`
      break
    case 'ymd':
    default:
      formatted = `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日`
  }

  return formatted
}