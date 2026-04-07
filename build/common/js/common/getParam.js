// TODO artlistで使ってるので、artlist で Fn.getParam に移行したら削除
const getParam = (p) => {
  const params = new URLSearchParams(location.search)
  const raw = params.get(p)
  return raw ? decodeURIComponent(raw) : ''
}

Fn.getParam = (p) => {
  const params = new URLSearchParams(location.search)
  const raw = params.get(p)
  return raw ? decodeURIComponent(raw) : ''
}