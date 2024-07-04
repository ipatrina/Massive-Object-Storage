<?php

	// NextBucket
	// Version: 1.0.2
	// Date: 2023.05

	include 'config.php';

	function createObject($object, $bucket, $cell, $length, $tag) {
		return putObject(getUUID(), $object, $bucket, $cell, $length, $tag);
	}

	function deleteObject($object) {
		return deleteObjectById(getObjectId($object));
	}

	function deleteObjectById($id) {
		global $sql_connection;
		global $table;
		$sql_statement = mysqli_prepare($sql_connection, "DELETE FROM ".$table." WHERE id = ?");
		if (!$sql_statement) {
			errorLog(mysqli_error($sql_connection));
			return -1;
		}
		mysqli_stmt_bind_param($sql_statement, 's', $id);
		mysqli_stmt_execute($sql_statement);
		$sql_affected_rows = mysqli_stmt_affected_rows($sql_statement);
		mysqli_stmt_close($sql_statement);
		return $sql_affected_rows;
	}

	function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}
		return (substr($haystack, -$length) === $needle);
	}

	function errorLog($errorMessage) {
		print $errorMessage;
	}

	function getObject($object) {
		return getObjectById(getObjectId($object));
	}

	function getObjectById($id) {
		global $sql_connection;
		global $table;
		$sql_statement = mysqli_prepare($sql_connection, "SELECT object,bucket,cell,length,tag FROM ".$table." WHERE id = ?");
		if (!$sql_statement) {
			errorLog(mysqli_error($sql_connection));
			return [];
		}
		mysqli_stmt_bind_param($sql_statement, 's', $id);
		mysqli_stmt_execute($sql_statement);
		mysqli_stmt_bind_result($sql_statement, $object, $bucket, $cell, $length, $tag);
		if (mysqli_stmt_fetch($sql_statement)) {
			mysqli_stmt_close($sql_statement);
			return array("id"=>$id, "object"=>$object, "bucket"=>$bucket, "cell"=>$cell, "length"=>$length, "tag"=>$tag);
		}
		else {
			mysqli_stmt_close($sql_statement);
			return [];
		}
	}
    function getObjectsByIds($ids) {
        global $sql_connection;
        global $table;
        if(empty($ids)){
            return [];
        }
        $ids_placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('s', count($ids));
        $sql_statement = mysqli_prepare($sql_connection, "SELECT id, object, bucket, cell, length, tag FROM ".$table." WHERE id IN ($ids_placeholders)");
        if (!$sql_statement) {
            errorLog(mysqli_error($sql_connection));
            return [];
        }
        mysqli_stmt_bind_param($sql_statement, $types, ...$ids);
        mysqli_stmt_execute($sql_statement);
        mysqli_stmt_bind_result($sql_statement, $id, $object, $bucket, $cell, $length, $tag);
        $results = [];
        while (mysqli_stmt_fetch($sql_statement)) {
            $results[$id] = array("id"=>$id, "object"=>$object, "bucket"=>$bucket, "cell"=>$cell, "length"=>$length, "tag"=>$tag);
        }
        mysqli_stmt_close($sql_statement);
        return $results;
    }
	function getObjectId($object) {
		global $sql_connection;
		global $table;
		$sql_statement = mysqli_prepare($sql_connection, "SELECT id FROM ".$table." WHERE object = ?");
		if (!$sql_statement) {
			errorLog(mysqli_error($sql_connection));
			return -1;
		}
		mysqli_stmt_bind_param($sql_statement, 's', $object);
		mysqli_stmt_execute($sql_statement);
		mysqli_stmt_bind_result($sql_statement, $id);
		if (mysqli_stmt_fetch($sql_statement)) {
			mysqli_stmt_close($sql_statement);
			return $id;
		}
		else {
			mysqli_stmt_close($sql_statement);
			return 0;
		}
	}

	function getUUID() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%012x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, time());
	}

	function listObject($key, $pathless = false) {
		global $sql_connection;
		global $table;
		$idList = [];
		$searchString1 = $key."%";
		$searchString2 = $key."%/%";
		$searchString3 = $key."%/";
		$searchString4 = $key."%/%/";
		if ($pathless) {
			$sql_statement = mysqli_prepare($sql_connection, "SELECT id FROM ".$table." WHERE object LIKE ? ORDER BY object");
			if (!$sql_statement) {
				errorLog(mysqli_error($sql_connection));
				return [];
			}
			mysqli_stmt_bind_param($sql_statement, 's', $searchString1);
			mysqli_stmt_execute($sql_statement);
			mysqli_stmt_bind_result($sql_statement, $id);
			while (mysqli_stmt_fetch($sql_statement)) {
				array_push($idList, $id);
			}
			mysqli_stmt_close($sql_statement);
		}
		else {
			for ($i = 1; $i <= 2; $i++) {
				$sql_statement = mysqli_prepare($sql_connection, "SELECT id FROM ".$table." WHERE object LIKE ? AND object NOT LIKE ? ORDER BY object");
				if (!$sql_statement) {
					errorLog(mysqli_error($sql_connection));
					return [];
				}
				if ($i == 1) {
					mysqli_stmt_bind_param($sql_statement, 'ss', $searchString3, $searchString4);
				}
				else {
					mysqli_stmt_bind_param($sql_statement, 'ss', $searchString1, $searchString2);
				}
				mysqli_stmt_execute($sql_statement);
				mysqli_stmt_bind_result($sql_statement, $id);
				while (mysqli_stmt_fetch($sql_statement)) {
					array_push($idList, $id);
				}
				mysqli_stmt_close($sql_statement);
			}
		}
		return $idList;
	}

	function putObject($id, $object, $bucket, $cell, $length, $tag) {
		global $sql_connection;
		global $table;
		if (getObjectId($object)) {
			return 0;
		}
		$sql_statement = mysqli_prepare($sql_connection, "INSERT INTO ".$table." (id,object,bucket,cell,length,tag) VALUES (?,?,?,?,?,?)");
		if (!$sql_statement) {
			errorLog(mysqli_error($sql_connection));
			return -1;
		}
		mysqli_stmt_bind_param($sql_statement, 'ssssis', $id, $object, $bucket, $cell, $length, $tag);
		mysqli_stmt_execute($sql_statement);
		$sql_affected_rows = mysqli_stmt_affected_rows($sql_statement);
		mysqli_stmt_close($sql_statement);
		return $sql_affected_rows;
	}

	function startsWith($haystack, $needle)	{
		$length = strlen($needle);
 		return (substr($haystack, 0, $length) === $needle);
	}

	function updateObject($object, $column, $value) {
		updateObjectById(getObjectId($object), $column, $value);
	}

	function updateObjectById($id, $column, $value) {
		global $sql_connection;
		global $table;
		if ($column == "object" && getObjectId($value)) {
			return 0;
		}
		$sql_statement = mysqli_prepare($sql_connection, "UPDATE ".$table." SET ".$column." = ? WHERE id = ?");
		if (!$sql_statement) {
			errorLog(mysqli_error($sql_connection));
			return -1;
		}
		mysqli_stmt_bind_param($sql_statement, 'ss', $value, $id);
		mysqli_stmt_execute($sql_statement);
		$sql_affected_rows = mysqli_stmt_affected_rows($sql_statement);
		mysqli_stmt_close($sql_statement);
		return $sql_affected_rows;
	}

?>