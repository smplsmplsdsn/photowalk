Module.uploaderList = {
  _list_uploaded_list: $('.js-uploaded-list'),

  add(row, is_uploaded) {

    if (is_uploaded) {
      this._list_uploaded_list.prepend(`
        <li>
          <img src="./assets/photo.php?filename=${row.public_id}/${row.filename}">
        </li>
      `).show()
    }
  }
}