$(() => {
  document.addEventListener('touchstart', function(event) {

    if (event.touches.length > 1) {
      event.preventDefault()  // 複数の指によるズームを防ぐ
    }
  }, { passive: false })

  let lastTouchEnd = 0

  document.addEventListener('touchend', function(event) {
    const now = (new Date()).getTime()
    if (now - lastTouchEnd <= 300) {  // ダブルタップを無効化
      event.preventDefault()

      // ズームを強制的に等倍に戻す
      document.body.style.transform = 'scale(1)'
      document.body.style.transformOrigin = '0 0'
    }
    lastTouchEnd = now
  }, { passive: false })
})