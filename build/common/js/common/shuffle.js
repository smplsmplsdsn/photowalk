/**
 * 配列をシャッフルする
 *
 * @param {array} array
 * @returns
 */
Fn.shuffle = (array = []) => {

  // 元の配列を壊さないようコピー
  const ary = [...array]

  let i,
      j;

  for (i = ary.length; 1 < i; i--) {
    j = Math.floor(Math.random() * i);
    [ary[j], ary[i - 1]] = [ary[i - 1], ary[j]];
  }

  return ary;
}