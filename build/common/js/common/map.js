const MAP = {}

// NOTICE: MAP.gmap は初期化処理で実行している

MAP.markers = []
MAP.z_index = 0

/**
 * マーカーを生成する
 */
MAP.createMarker = ({map, id, lat, lng, z_index, content, func}) => {
  const marker = new MAP.advaned_marker({
    map,
    position: { lat, lng },
    zIndex: z_index,
    content
  })

  marker.addListener("click", () => {

    if (func) {
      func(id)
    }
  })

  marker._meta = {
    id,
    lat,
    lng,
    z_index
  }

  MAP.markers.push({ id, marker })
}

/**
 * マーカーを表示する
 */
MAP.showMarker = (id) => {
  const item = MAP.markers.find(m => m.id === (+id))

  if (!item) {
    return
  }

  item.marker.map = MAP.gmap
}

/**
 * マーカーをすべて表示する
 */
MAP.showAllMarkers = () => {
  MAP.markers.forEach(obj => {
    obj.marker.map = MAP.gmap
  })
}

/**
 * マーカーを非表示にする
 */
MAP.hideMarker = (id) => {
  const item = MAP.markers.find(m => m.id === (+id))

  if (!item) {
    return
  }

  item.marker.map = null
}

/**
 * マーカーをすべて非表示にする
 */
MAP.hideAllMarkers = () => {
  MAP.markers.forEach(obj => {
    obj.marker.map = null
  })
}

/**
 * マーカーを削除する
 * @param {} id
 * @returns
 */
MAP.removeMarker = (id) => {
  const index = MAP.markers.findIndex(m => m.id === (+id))

  if (!index) {
    return
  }

  MAP.markers[index].marker.map = null
  MAP.markers.splice(index, 1)
}

/**
 * マーカーをすべて削除する
 */
MAP.removeAllMarkers = () => {
  MAP.hideAllMarkers()
  MAP.markers = []
}

/**
 * マーカーを大きく小さくさせる
 * @param {*} id
 */
MAP.animateMarker = (id) => {
  const item = MAP.markers.find(m => m.id === (+id))

  if (!item) {
    return
  }

  const marker = item.marker,
        el = marker.content

  MAP.z_index++

  marker.zIndex = MAP.z_index

  el.classList.remove('maps-marker-pop')
  void el.offsetWidth   // HACK: 再アニメーション用のリセットハック
  el.classList.add('maps-marker-pop')
}

/**
 * マーカーにクラスを付与する
 */
MAP.removeClassMarker = (id, class_name) => {
  const item = MAP.markers.find(m => m.id === (+id))

  if (!item) {
    return
  }

  item.marker.content.classList.add(class_name)
}

/**
 * マーカーにクラスを削除する
 */
MAP.removeClassMarker = (id, class_name) => {
  const item = MAP.markers.find(m => m.id === (+id))

  if (!item) {
    return
  }

  item.marker.content.classList.remove(class_name)
}

/**
 * すべてのマーカーにクラスを付与する
 */
MAP.addClassAllMarkers = (class_name) => {
  MAP.markers.forEach(obj => {
    obj.marker.content.classList.add(class_name)
  })
}

/**
 * すべてのマーカーにクラスを削除する
 */
MAP.removeClassAllMarkers = (class_name) => {
  MAP.markers.forEach(obj => {
    obj.marker.content.classList.remove(class_name)
  })
}

/**
 * 地図の中心点を取得する
 *
 * @returns {lat, lng}
 */
MAP.getCenter = () => {
  const c = MAP.gmap.getCenter()

  return {
    lat: c.lat(),
    lng: c.lng()
  }
}

/**
 * 中心点を移動する
 *
 * @param {string|number} lat
 * @param {string|number} lng
 */
MAP.panTo = (lat, lng) => {
  const pos = MAP.normalizeLatLng(lat, lng)

  if (pos) {
    MAP.gmap.panTo(pos)
  }
}

/**
 * 移動後の地図の中心点を取得する
 */
MAP.getCenterAfterChanged = (callback) => {
  MAP.gmap.addListener('idle', () => {
    const center = MAP.getCenter()
    callback(center.lat, center.lng)
  })
}

/**
 * 緯度経度から住所を取得する（日本限定）
 * @param {*} lat
 * @param {*} lng
 * @returns
 */
MAP.getAddressFromLatLng = (lat, lng) => {
  return new Promise((resolve, reject) => {
    const geocoder = new google.maps.Geocoder()

    geocoder.geocode(
      {
        location: { lat, lng }
      },
      (results, status) => {

        if (status === 'OK' && results[0]) {
          let str = results[0].formatted_address

          if (str.startsWith('日本')) {
            str = str.replace(/^日本、?/, '')   // 「日本、」を削除
            str = str.replace(/〒\d{3}-\d{4}\s*/, '')   // 郵便番号を削除
            str = str.split(/[ー−-]/)[0]    // 最初のハイフン系が出る前で切る
            str = str.trim()    // 末尾の空白を除去

            str = str.replace(/[０-９]/g, s => {
              return String.fromCharCode(s.charCodeAt(0) - 0xFEE0)
            })
          }
          resolve({status, str})
        } else {
          reject({status})
        }
      }
    )
  })
}

/**
 * 緯度経度の値が適切か判別し、適切な場合は数値を返却する
 * @param {string|number} lat: 緯度
 * @param {string|number} lng: 経度
 * @returns {object|null} {lat,lng}
 */
MAP.normalizeLatLng = (lat, lng) => {
  const _lat = Number(lat)
  const _lng = Number(lng)

  // 数値に変換できるか、有限な数値か（NaN / Infinity を弾く）判別する
  if (!Number.isFinite(_lat) || !Number.isFinite(_lng)) {
    return null
  }

  // 緯度・経度としての範囲か判別する
  if (_lat < -90 || _lat > 90 || _lng < -180 || _lng > 180) {
    return null
  }

  return {
    lat: _lat,
    lng: _lng
  }
}


