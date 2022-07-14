<!DOCTYPE html>
<html>
<head>
    <title>Kích hoạt tài khoản</title>
</head>
<body>
<h1>Kích hoạt tài khoản</h1>

<p>
    Vui lòng bấm vào liên kết bên dưới đây để kích hoạt tài khoản của bạn
    <a href="{{ $mailData['active_link'] }}" target="_blank">{{ $mailData['active_link'] }}</a>
    (hoặc sao chép và dán vào trình duyệt web)
</p>
<p>Liên kết này có giá trị tới {{ $mailData['expire_time'] }}</p>
<p>TRÂN TRỌNG</p>
</body>
</html>