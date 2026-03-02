/**
 * カウントダウン
 *
 * @param {*} end_time : e.g. new Date().getTime()
 * @param {*} $element : jQuery要素
 * @param {function|null} : カウントダウン終了した際に処理を実行する場合
 * @returns
 */
Fn.doContDown = (end_time, $element, func) => {
  let interval_id

  const update = () => {
    const now = Date.now()

    let diff = end_time - now

    if (diff <= 0) {
      $element.text("00:00:00")

      if (interval_id) {
        clearInterval(interval_id)
      }

      if (func) {
        func()
      }
      return
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24)),
          hours = Math.floor((diff / (1000 * 60 * 60)) % 24),
          minutes = Math.floor((diff / (1000 * 60)) % 60),
          seconds = Math.floor((diff / 1000) % 60)

    let display = ""

    if (days > 1) {
      display += `${days}DAYS `
    } else if (days === 1) {
      display += `${days}DAY `
    }

    display += `${hours.toString().padStart(2,'0')}:${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}`
    $element.text(display)
  }

  update()
  interval_id = setInterval(update, 1000)
  return interval_id
}
