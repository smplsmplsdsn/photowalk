/**
 * 現在地を取得する
 * ex) const location = await getGeoLocation(10, 10)
 *
 * @param number max_retries 取得回数
 * @param target_accuracy 精度（m）
 * @returns object lat,lng,accuracy|error_message
 */
const getGeoLocation = async (max_retries = 5, target_accuracy = 10) => {

  // 初期化
  let attempts = 0,
      position = {
        error_message: '現在地を取得できませんでした'
      }

  const getPosition = () => {
    return new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(
        position => {
          resolve({
            lat: position.coords.latitude,
            lng: position.coords.longitude,
            accuracy: position.coords.accuracy
          })
        },
        error => {
          switch (error.code) {
            case error.PERMISSION_DENIED:
              reject('位置情報のアクセスが拒否されました。')
              break
            case error.POSITION_UNAVAILABLE:
              reject('位置情報が利用できません。')
              break
            case error.TIMEOUT:
              reject('位置情報の取得がタイムアウトしました。')
              break
            default:
              reject('位置情報の取得に失敗しました。')
              break
          }
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0
        }
      )
    })
  }

  // リトライ処理
  while (attempts < max_retries) {
    try {
      position = await getPosition()

      if (position.accuracy <= target_accuracy) {
        // 精度が目標以内なら位置情報を返す
        return position
      } else {
        // console.warn(`再試行します (${attempts + 1}/${max_retries})`)
        attempts++
      }
    } catch (error) {
      return position || { error_message: error }
    }
  }

  return position
}