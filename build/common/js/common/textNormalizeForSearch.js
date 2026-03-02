/**
 * 検索用 正規化
 */
Fn.textNormalizeForSearch = (str = '') => {
  return (typeof str !== 'string')? '' : str
    // 全角・半角統一
    .normalize('NFKC')

    // 英字小文字化
    .toLowerCase()

    // カタカナ → ひらがな
    .replace(/[\u30a1-\u30f6]/g, ch =>
      String.fromCharCode(ch.charCodeAt(0) - 0x60)
    )

    // 長音除去（検索用）
    .replace(/ー/g, '')

    // スペース除去
    .replace(/\s+/g, '')

    // ヴァ行の揺れ吸収
    .replace(/ゔぁ/g, 'ば')
    .replace(/ゔぃ/g, 'び')
    .replace(/ゔぇ/g, 'べ')
    .replace(/ゔぉ/g, 'ぼ')
    .replace(/ゔ/g, 'ぶ')

    // 全角記号を消す
    .replace(/[・,，.．]/g, '')
}