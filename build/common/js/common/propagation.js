/**
 * NOTICE: Evt.press 配下を無効にするためのオーバーレイ
 */
$(() => {
  $(document).on(Evt.release, '.js-propagation', (e) => {
    e.stopPropagation()
  })
})
