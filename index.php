<?php
session_start(); // 启动会话

// 设置允许访问的目录（请根据需要修改）
$allowed_directory = __DIR__ . '/files'; // 假设要访问的目录是当前目录下的 files 文件夹

// 检查目录是否存在
if (!is_dir($allowed_directory)) {
    die("目录不存在。");
}

// 定义上传文件所需的密码
$upload_password = '********';

// 可选：设置上传文件大小限制（单位：字节）
$max_file_size = 10 * 1024 * 1024; //10MB

// 处理上传请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_password'])) {
        $entered_password = isset($_POST['upload_password']) ? trim($_POST['upload_password']) : '';

        if ($entered_password === $upload_password) {
            // 处理文件上传
            if (isset($_FILES['uploaded_file'])) {
                $file = $_FILES['uploaded_file'];

                // 检查文件上传是否有错误
                if ($file['error'] === UPLOAD_ERR_OK) {
                    // 检查文件大小
                    if ($file['size'] > $max_file_size) {
                        echo "<p style='color: red;'>文件大小超过限制（10MB）。</p>";
                        exit;
                    }

                    $file_name = basename($file['name']);
                    $file_tmp = $file['tmp_name'];
                    $file_path = $allowed_directory . '/' . $file_name;

                    // 可选：限制文件类型
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'zip', 'rar'];
                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    if (in_array($file_extension, $allowed_types)) {
                        // 移动文件到目标目录
                        if (move_uploaded_file($file_tmp, $file_path)) {
                            echo "<p style='color: green;'>文件上传成功！</p>";
                        } else {
                            echo "<p style='color: red;'>文件上传失败。</p>";
                        }
                    } else {
                        echo "<p style='color: red;'>不允许上传该类型的文件。</p>";
                    }
                } else {
                    echo "<p style='color: red;'>文件上传出错。错误代码：" . $file['error'] . "</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>上传密码错误。</p>";
        }
    }
}

// 处理下载请求
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // 获取文件名并去除路径信息
    $file_path = $allowed_directory . '/' . $file;

    // 验证文件路径是否在允许的目录中
    $real_allowed_path = realpath($allowed_directory);
    $real_file_path = realpath($file_path);

    if ($real_file_path === false || strpos($real_file_path, $real_allowed_path) !== 0) {
        echo "文件不存在。";
        exit;
    }

    // 检查文件是否存在
    if (file_exists($file_path)) {
        // 可选：限制文件类型
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'zip', 'rar'];
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_types)) {
            // 设置下载头
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            echo "不允许下载该类型的文件。";
        }
    } else {
        echo "文件不存在。";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>文件管理器</title>
    <link rel="icon" href="lkx1.ico" type="image/x-icon"> <!-- 添加网页图标 -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            background-image: url('d.png'); /* 添加背景图片 */
            background-size: cover; /* 背景图片覆盖整个页面 */
            background-repeat: no-repeat; /* 不重复 */
            background-position: center; /* 居中 */
            text-align: center;
            padding-top: 50px;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8); /* 降低透明度，使背景图片更清晰 */
            padding: 20px;
            margin: auto;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f9f9f9;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        form {
            margin-top: 20px;
        }
        input[type="password"], input[type="file"] {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>文件管理器</h1>

    <!-- 文件上传表单 -->
    <form method="post" enctype="multipart/form-data">
        <label for="upload_password">上传密码：</label><br><br>
        <input type="password" id="upload_password" name="upload_password" required><br><br>
        <label for="uploaded_file">选择要上传的文件(jpg,jpeg,png,gif,pdf,txt,zip,rar)：</label><br><br>
        <input type="file" id="uploaded_file" name="uploaded_file" required><br><br>
        <input type="submit" value="上传">
    </form>

    <!-- 文件列表表格 -->
    <table>
        <tr>
            <th>文件名</th>
            <th>操作</th>
        </tr>
        <?php
        // 打开目录
        $dir = opendir($allowed_directory);
        $files = [];

        // 读取目录内容
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $files[] = $file;
            }
        }
        closedir($dir);

        // 显示文件列表
        foreach ($files as $file) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($file) . "</td>";
            echo "<td><a href='?file=" . urlencode($file) . "'>下载</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

</body>
</html>