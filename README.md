# Massive Object Storage

Massive Object Storage (MOS) based on the NextBucket framework provides a modern system framework for mass file storage indexing systems. (GUI is not included in the framework.)

The project architecture was designed by developer *leililei*, the chief architect of NextBucket and MOS.

Massive Object Storage (MOS) 基于 NextBucket 框架，提供现代化海量文件存储的索引系统框架。（框架内不包含GUI。）

![MOS preview](https://thumbs2.imgbox.com/32/c2/DZ0LGdt2_t.png)


# Operate environment / 运行环境

- PHP 8 and above.

- MariaDB 10 or MySQL 5 and above.

---

- PHP 8 及以上版本。

- MariaDB 10 或 MySQL 5 及以上版本。

# Getting started / 从这里开始

Edit "config.php" and create data table with SQL commands before programming.

**Example codes** "MyProgram.php"

```
<?php
    include 'mos.php';
    createBucket("10001", "http", "MyBucketName");
    createDirectory("10001", "path/to/directory/", "");
    createFile("10001", "path/to/file", "http", "http://www.example.com/downloads/target_file.bin", 1048576, "MyFileTag");
    $objectList = listDirectory("10001", "path/to/", true);
    $objectDetails = getObjectById($objectList[0]);
    deleteBucket("10001", "confirm");
?>
```


# Changelog / 更新日志

**1.0.3 (2023/04/17)**

1.修复存储桶默认不授权任何操作时可能导致出现告警的问题。

---

**1.0.2 (2023/04/16)**

1.修复在PHP 8环境下调用getBucket函数可能导致出现警告信息的问题。

2.修复在PHP 8环境下，当数据库连接失败时，会在警告信息中打印数据库凭据的问题。

---

**1.0.1 (2023/03/07)**

1.修复文件名中存在的一些特殊字符无法被正确转义的问题。

---

**1.0.0 (2022/07/06)**

Massive Object Storage (MOS) 为您提供基于PHP的现代化海量文件存储的索引系统框架。
