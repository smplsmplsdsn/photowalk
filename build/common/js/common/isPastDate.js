/**
 * 日付比較
 * NOTICE: 年月日での比較、時分は含まない
 * 第一引数は第二引数より過去の場合は、true、未来の場合は false
 * @param {string} compare_date_a e.g. 2025-1-1 | today
 * @param {string} compare_date_b e.g. 2025-1-2 | today
 * @param {boolean|null} is_include_today : 今日を含める場合 true
 * @returns {boolean}
 */
Fn.isPastDate = (compare_date_a, compare_date_b, is_include_today = false) => {

  if (compare_date_a === 'today') {
    compare_date_a = null
  }

  if (compare_date_b === 'today') {
    compare_date_b = null
  }

  let a = compare_date_a? new Date(compare_date_a.replace(/-/g, '/')) : new Date(),
      b = compare_date_b? new Date(compare_date_b.replace(/-/g, '/')) : new Date()

  a = a.setHours(0, 0, 0, 0)
  b = b.setHours(0, 0, 0, 0)

  return (is_include_today) ? a <= b :  a < b
}
