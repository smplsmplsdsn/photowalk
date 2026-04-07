/**
 * ISO形式などから年月日を取得する
 *
 * @param {string} iso : ex) '2100-01-01T00:20', '1775314800'
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
    case 'ymdhi':
      formatted = `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日 ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`
      break;
    case 'ymd':
    default:
      formatted = `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日`
  }

  return formatted
}