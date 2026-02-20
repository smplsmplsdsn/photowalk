/**
 * 配列をシャッフルする
 *
 * @param {array} array
 * @param {boolean} is_replace
 * @returns
 */
Fn.shuffle = (array = [], is_replace) => {

  // 元の配列を差し替えるか判別する
  const ary = (is_replace) ? array : [...array]

  let i,
      j;

  for (i = ary.length; 1 < i; i--) {
    j = Math.floor(Math.random() * i);
    [ary[j], ary[i - 1]] = [ary[i - 1], ary[j]];
  }

  return ary;
}