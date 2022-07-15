<!DOCTYPE html>
<html>
<head>
    <title>Kích hoạt tài khoản</title>
</head>
<body>

<p>Chào <b>{{$mailData['fullname']}}</b></p>
<p>
    Chúng tôi nhận được yêu cầu thay đổi mật khẩu cho tài khoản {{$mailData['app_name']}} của bạn.
</p>
<p>
    Vui lòng bấm vào liên kết bên dưới đây:
    <a href="{{ $mailData['active_link'] }}" target="_blank">{{ $mailData['active_link'] }}</a>
    &nbsp; (hoặc sao chép và dán vào trình duyệt web) để thay đổi mật khẩu tài khoản của bạn

</p>
<p>Liên kết này có giá trị tới {{ $mailData['expire_time'] }}</p>
<p>TRÂN TRỌNG</p>
</body>
</html>