<?php
date_default_timezone_set('Asia/Tokyo');
header('Content-Type: application/json; charset=utf-8');
session_start();

include_once( __DIR__ . '/../../../functions/common/getPDO.php');
include_once( __DIR__ . '/../../../functions/api/uploaderInsertImage.php');
include_once( __DIR__ . '/../../../functions/api/uploaderProcessImageWithFallback.php');
include_once( __DIR__ . '/../../../functions/api/uploader.php');
