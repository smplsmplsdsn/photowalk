// touchstart, mousedown, pointerdown
Evt.press = (() => {
  if (window.PointerEvent) return 'pointerdown'
  if ('ontouchstart' in window) return 'touchstart'
  return 'mousedown'
})()

// touchend, mouseup, pointerup
Evt.release = (() => {
  if (window.PointerEvent) return 'pointerup'
  if ('ontouchstart' in window) return 'touchend'
  return 'mouseup'
})()