/**
 * 距離(km)を m / km 表示にフォーマットする
 *
 * @param {number|string} distance : 単位がkmの距離（数字のみ）
 * @returns string
 */
Fn.formatDistanceKm = (distance = 0) => {
  let v = Number(distance)

  if (!Number.isFinite(v)) {
    return '---'
  }

  if (Math.abs(v) < 1e-9) {
    v = 0
  }

  return (v < 1)
    ? Math.max(0, Math.round(v * 1000)) + 'm'
    : Math.round(v * 10) / 10 + 'km'
}
