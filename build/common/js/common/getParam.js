const getParam = (p) => {
  const params = new URLSearchParams(location.search)
  const raw = params.get(p)
  return raw ? decodeURIComponent(raw) : ''
}