<?php

header('Content-Type: text/plain; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Đọc danh sách tên, họ, số điện thoại
$list_usernames = file('dict/ten_trai.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$list_ho = file('dict/ho.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$list_dau_so = file('dict/dau_so_viet_nam.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (!$list_usernames || count($list_usernames) === 0) {
    die('Error: Username list is empty');
}
if (!$list_ho || count($list_ho) === 0) {
    die('Error: Ho list is empty');
}
if (!$list_dau_so || count($list_dau_so) === 0) {
    die('Error: Dau so list is empty');
}

// Cấu hình
$min_length = 4;
$max_length = 11;

// Đọc ID hiện tại
$id_file = 'id/id_user.txt';
$used_usernames_file = 'id/used_usernames.txt';
$used_emails_file = 'id/used_emails.txt';
$used_phones_file = 'id/used_phones.txt';
$id_user = 0;

if (file_exists($id_file)) {
    $id_user = (int)file_get_contents($id_file);
}

// Đọc danh sách đã tạo
$used_usernames = [];
$used_emails = [];
$used_phones = [];

if (file_exists($used_usernames_file)) {
    $used_usernames = file($used_usernames_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}
if (file_exists($used_emails_file)) {
    $used_emails = file($used_emails_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}
if (file_exists($used_phones_file)) {
    $used_phones = file($used_phones_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

$used_usernames_set = array_flip($used_usernames);
$used_emails_set = array_flip($used_emails);
$used_phones_set = array_flip($used_phones);

// Tạo username, email, phone unique
$max_attempts = 10000;
$attempt = 0;
$current_username = '';
$email = '';
$so_dien_thoai = '';
$base_name = '';

while ($attempt < $max_attempts) {
    // Lấy username theo ID (vòng lặp qua danh sách)
    $base_name = $list_usernames[$id_user % count($list_usernames)];
    
    // Xử lý username: loại bỏ khoảng trắng, viết thường, thêm số ngẫu nhiên
    $base_username = trim($base_name);
    $base_username = preg_replace('/\s+/', '', $base_username);
    $base_username = strtolower($base_username);
    $current_username = $base_username . rand(100, 999);
    
    // Kiểm tra độ dài
    $username_length = strlen($current_username);
    if ($username_length < $min_length || $username_length > $max_length) {
        $id_user++;
        $attempt++;
        continue;
    }
    
    // Kiểm tra username trùng lặp
    if (isset($used_usernames_set[$current_username])) {
        $attempt++;
        continue;
    }
    
    // Tạo email
    $email_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
    $domain = $email_domains[array_rand($email_domains)];
    $email = $current_username . '@' . $domain;
    
    // Kiểm tra email trùng lặp
    if (isset($used_emails_set[$email])) {
        $attempt++;
        continue;
    }
    
    // Tạo số điện thoại
    $dau_so = trim($list_dau_so[array_rand($list_dau_so)]);
    $so_dien_thoai = $dau_so . rand(1000000, 9999999);
    
    // Kiểm tra phone trùng lặp
    if (isset($used_phones_set[$so_dien_thoai])) {
        $attempt++;
        continue;
    }
    
    // Nếu cả 3 đều unique thì thoát
    break;
}

if ($attempt >= $max_attempts) {
    die('Error: Cannot generate unique data');
}

// Lưu username, email, phone đã tạo
file_put_contents($used_usernames_file, $current_username . PHP_EOL, FILE_APPEND);
file_put_contents($used_emails_file, $email . PHP_EOL, FILE_APPEND);
file_put_contents($used_phones_file, $so_dien_thoai . PHP_EOL, FILE_APPEND);

// Tăng ID và lưu lại
$id_user++;
file_put_contents($id_file, $id_user);

// Tạo họ tên
$ho = trim($list_ho[array_rand($list_ho)]);
$ten = trim($base_name);
$ho_ten = $ho . ' ' . $ten;

// Tạo mật khẩu (6-16 ký tự kết hợp số và chữ)
$password_length = rand(6, 16);
$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$password = '';
for ($i = 0; $i < $password_length; $i++) {
    $password .= $characters[rand(0, strlen($characters) - 1)];
}
// Đảm bảo có ít nhất 1 số và 1 chữ
if (!preg_match('/[0-9]/', $password)) {
    $password[rand(0, $password_length - 1)] = rand(0, 9);
}
if (!preg_match('/[a-zA-Z]/', $password)) {
    $password[rand(0, $password_length - 1)] = chr(rand(97, 122));
}

// Chuẩn bị kết quả
$result = [
    'username' => $current_username,
    'ho_ten' => $ho_ten,
    'email' => $email,
    'so_dien_thoai' => $so_dien_thoai,
    'password' => $password
];

// Lưu đầy đủ thông tin vào file dạng text (username|ho_ten|email|so_dien_thoai|password)
$all_data_file = 'id/all_users_data.txt';
$data_line = $current_username . '|' . $ho_ten . '|' . $email . '|' . $so_dien_thoai . '|' . $password . PHP_EOL;
file_put_contents($all_data_file, $data_line, FILE_APPEND);

// Xuất kết quả dạng multi line (chỉ giá trị)
echo $current_username . PHP_EOL;
echo $password . PHP_EOL;
echo $so_dien_thoai . PHP_EOL;
echo $ho_ten;