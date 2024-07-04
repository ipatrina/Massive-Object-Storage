<?php

	// Massive Object Storage
	// Version: 1.0.3
	// Date: 2023.04

	include 'nextbucket.php';

	function createBucket($bizid, $type, $remark) {
		if (strlen($remark) < 1) {
			$remark = $bizid;
		}
		return createObject("bucket".$bizid."/", $type, "", "0", "remark=".$remark);
	}

	function createDirectory($bizid, $key, $tag) {
		$key = str_replace("\\", "/", $key);
		if (startsWith($key, "/") || !endsWith($key, "/") || strlen($key) < 2) {
			return 0;
		}
		$index = 0;
		while (true) {
			$pos = strpos($key, "/", $index);
			if ($pos === false) {
				break;
			}
			createObject("bucket".$bizid."/".substr($key, 0, $pos)."/", "", "", "0", $tag);
			$index = $pos + 1;
		}
		return 1;
	}

	function createFile($bizid, $key, $bucket, $cell, $length, $tag) {
		$key = str_replace("\\", "/", $key);
		return createObject("bucket".$bizid."/".$key, $bucket, $cell, $length, $tag);
	}

	function deleteFile($bizid, $key) {
		if (strlen($key) == 0 || endsWith($key, "/")) {
			return 0;
		}
		return deleteObject("bucket".$bizid."/".$key);
	}

	function deleteBucket($bizid, $confirm) {
		if ($confirm == "confirm") {
			$idList = listObject("bucket".$bizid."/", true);
			foreach ($idList as $id) {
				deleteObjectById($id);
			}
			return 1;
		}
		else {
			return 0;
		}
	}

	function deleteDirectory($bizid, $key) {
		if (strlen($key) < 2 || !endsWith($key, "/")) {
			return 0;
		}
		$idList = listObject("bucket".$bizid."/".$key, true);
		foreach ($idList as $id) {
			deleteObjectById($id);
		}
		return 1;
	}

	function encodeUrl($url) {
		return str_replace(['%2F', '%3A', '%3D', '%3F'], ['/', ':', '=', '?'], rawurlencode($url));
	}

	function formatBytes($bytes, $precision = 2) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow]; 
	}

	function getBucket($bizid) {
		$bucket = getObject("bucket".$bizid."/");
		if (empty($bucket)) {
			return [];
		}
		parse_str($bucket["tag"], $tag);
		$name = isset($tag["remark"]) ? $tag["remark"] : "";
    $type = isset($bucket["bucket"]) ? $bucket["bucket"] : "";
    $ak = isset($tag["accesskey"]) ? $tag["accesskey"] : "";
    $auth = isset($tag["auth"]) ? $tag["auth"] : "";
		if (strlen($tag["remark"]) < 1) {
			return [];	
		}
		else {
			return array("name"=>$name, "type"=>$type, "ak"=>$ak, "auth"=>$auth);
		}
	}

	function getQueryString($parameter) {
		if (isset($_GET[$parameter])) {
			return $_GET[$parameter];
		}
		elseif (isset($_POST[$parameter])) {
			return $_POST[$parameter];
		}
		else {
			return "";
		}
	}

	function listDirectory($bizid, $key, $pathless = false) {
		if (!endsWith($key, "/") && strlen($key) > 0) {
			return 0;
		}
		return listObject("bucket".$bizid."/".$key, $pathless);
	}

	function moveFile($bizid, $src, $dest) {
		if (strlen($src) == 0 || strlen($dest) == 0) {
			return 0;
		}
		$bucket = "bucket".$bizid."/";
		$src = $bucket.$src;
		$dest = $bucket.$dest;
		if (strpos($src, "//") !== false || strpos($dest, "//") !== false || endsWith($src, "/") || endsWith($dest, "/") || getObjectId($dest)) {
			return 0;
		}
		createDirectory($bizid, substr($dest, strlen($bucket), strrpos($dest, "/") + 1 - strlen($bucket)), "");
		return updateObject($src, "object", $dest);
	}

	function moveDirectory($bizid, $src, $dest) {
		if (strlen($src) == 0 || strlen($dest) == 0) {
			return 0;
		}
		$bucket = "bucket".$bizid."/";
		$src = $bucket.$src;
		$dest = $bucket.$dest;
		if (strpos($src, "//") !== false || strpos($dest, "//") !== false || !endsWith($src, "/") || !endsWith($dest, "/") || getObjectId($dest) || startsWith($dest, $src)) {
			return 0;
		}
		createDirectory($bizid, substr($dest, strlen($bucket), strrpos(substr($dest, 0, strlen($dest) - 1), "/") + 1 - strlen($bucket)), "");
		$idList = listObject($src, true);
		foreach ($idList as $id) {
			$file = getObjectById($id);
			if (count($file) > 0) {
				updateObjectById($id, "object", $dest.substr($file["object"], strlen($src)));
			}
		}
		return 1;
	}

?>