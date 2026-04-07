Fn.setLang = () => {
  const lang = (navigator.language.startsWith('ja'))? 'ja' : 'en'

  $('html').attr('lang', lang)
}