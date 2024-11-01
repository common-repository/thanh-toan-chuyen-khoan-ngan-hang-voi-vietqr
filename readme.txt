=== Thanh toán chuyển khoản ngân hàng với VietQRPro từ Sổ Bán Hàng ===
 - Author: SBH Team
 - Contributors: orwell23
 - Tags: vietqr, payment gateway, chuyen khoan, thanh toan, ngan hang
 - Requires WooCommerce at least: 6.0
 - Stable Tag: 1.0.7
 - Version: 1.0.7
 - Tested up to: 6.5
 - Requires at least: 4.7
 - Requires PHP: 7.0
 - Author URI: https://sobanhang.com
 - Plugin URI: https://sobanhang.com/plugin-wordpress/
 - License: GPLv3.0
 - License URI: http://www.gnu.org/licenses/gpl-3.0.html

Kết nối ngân hàng VN vào WooCommerce. Xác nhận đơn hàng qua VietQR. Sử dụng VietQRPro của Sổ Bán Hàng. Đơn giản để bắt đầu!!!

== Description ==
**Lưu ý**: Đọc kỹ hướng dẫn và mô tả bên dưới trước khi sử dụng. Link tham khảo về Plugin [tại đây](https://sobanhang.larksuite.com/wiki/Qm1Fwcc7hiQ1sfkgSS2uqGYnsbe)

**Mẹo** : Plugin này hoàn toàn miễn phí.

== Screenshots ==
1. Thanh toán chuyển khoản với VietQR
2. Mã QR hiển thị khi thanh toán, đi kèm các thông tin chuyển khoản nhanh chóng
3. Hướng dẫn cài đặt rõ ràng
4. Nhấn kích hoạt sau khi nhập mã
5. Nhấn kích hoạt webhook
6. Dễ dàng nhận biến động số dư qua Lark/Telegram


== Installation ==

Bước 1: Tải Plugin
Truy cập vào trang web của plugin: Đầu tiên, hãy truy cập vào trang web nơi plugin được lưu trữ. Bạn có thể tìm thấy plugin này trên trang chủ của Sổ Bán Hàng hoặc trên trang WordPress.org.
Tải plugin: Tìm nút tải về (thường là "Download" hoặc "Tải Xuống") và nhấn vào đó để tải file plugin dạng .zip.

Bước 2: Cài Đặt Plugin Trên WordPress
Đăng nhập vào trang quản trị WordPress: Mở trình duyệt của bạn và nhập địa chỉ của trang quản trị WordPress (thường là yourwebsite.com/wp-admin).
Vào mục "Plugins": Trong bảng điều khiển, tìm và nhấp vào mục "Plugins" trên thanh bên trái.
Thêm Plugin mới: Nhấn vào "Add New" hoặc "Thêm Mới".
Tải plugin lên: Chọn "Upload Plugin" ở đầu trang, sau đó nhấn vào "Choose File" và chọn file .zip của plugin mà bạn đã tải về.
Cài đặt và kích hoạt: Sau khi đã chọn file, nhấn "Install Now" để cài đặt plugin, rồi nhấn "Activate Plugin" để kích hoạt nó.

Bước 3: Cấu Hình Plugin
Tìm cài đặt của plugin: Sau khi kích hoạt, tìm plugin trong danh sách các plugin đã cài đặt và chọn "Settings" hoặc tương tự để vào phần cấu hình.
Nhập thông tin cần thiết: Cung cấp các thông tin cần thiết như thông tin tài khoản ngân hàng, thông tin đối tác từ SoBanHang, và các cấu hình khác theo yêu cầu của plugin.
Lưu cấu hình: Sau khi đã điền đủ thông tin, nhấn "Save Changes" để lưu cấu hình của bạn.

Bước 4: Kiểm tra và sử dụng
Kiểm tra plugin: Vào trang thanh toán của website để xem plugin đã hoạt động chính xác chưa.
Sử dụng plugin: Nếu mọi thứ hoạt động tốt, bạn đã sẵn sàng sử dụng plugin để nhận thanh toán qua chuyển khoản ngân hàng với VietQR.

Lưu ý
Đảm bảo rằng bạn luôn cập nhật WordPress và các plugin lên phiên bản mới nhất để tăng cường bảo mật.
Nếu gặp vấn đề, hãy tham khảo phần hỗ trợ trên trang của plugin hoặc tìm kiếm sự giúp đỡ từ cộng đồng WordPress.
Chúc bạn cài đặt và sử dụng thành công plugin "Thanh toán chuyển khoản ngân hàng với VietQRPro từ Sổ Bán Hàng"!

## Sử Dụng Dịch Vụ SoBanHang

Plugin sử dụng dịch vụ do SoBanHang cung cấp để xử lý thanh toán. Điều này cần thiết cho chức năng cốt lõi của plugin hoạt động đúng cách.

### Các Dịch Vụ Được Sử Dụng:

- **Tạo Webhook**: Plugin của chúng tôi sử dụng API của SoBanHang để tạo webhook cho quá trình xử lý thanh toán. Thông tin chi tiết có thể tìm thấy tại đây: [SoBanHang Webhook API](https://sobanhang.larksuite.com/wiki/BmQwwm72ZijKpNku8mcuNIy7s4d?from=from_copylink)

- **Tạo Tài Khoản Ngân Hàng Thương Mại**: Chúng tôi cũng sử dụng API của SoBanHang để tạo tài khoản ngân hàng cho thương mại. Chi tiết tại đây: [SoBanHang Merchant Bank API](https://sobanhang.larksuite.com/wiki/BmQwwm72ZijKpNku8mcuNIy7s4d?from=from_copylink)

- **Tạo Đơn Hàng Thanh Toán QR Code**: Để tạo đơn hàng thanh toán bằng mã QR, plugin của chúng tôi sử dụng API của SoBanHang. Thông tin chi tiết tại: [SoBanHang QR Code API](https://sobanhang.larksuite.com/wiki/BmQwwm72ZijKpNku8mcuNIy7s4d?from=from_copylink)

- **Hiển Thị Mã QRCode**: Plugin cung cấp khả năng hiển thị mã QR từ dữ liệu text cho các giao dịch thanh toán. Điều này giúp việc thanh toán trở nên nhanh chóng và tiện lợi hơn cho cả người mua và người bán. Thông tin chi tiết tại: [SoBanHang QR Code](https://sobanhang.larksuite.com/wiki/BmQwwm72ZijKpNku8mcuNIy7s4d?from=from_copylink)

- **Domain sử dụng**: Dịch vụ sử dụng 3 domain của SoBanHang sau đây: sobanhang.com, api.finan.cc, fin.finan.vn. Tất cả đều đảm bảo phù hợp với Điều Khoản Sự Dụng và Chính Sách Bảo Mật của SoBanHang

## Sử Dụng Dịch Vụ Bên Ngoài

Ngoài các dịch vụ của SoBanHang, plugin cũng tích hợp với Lark và Telegram để gửi thông báo. Điều này cung cấp thêm lựa chọn thông báo cho người dùng của chúng tôi.

### Tích hợp với Lark:

- **Gửi Thông Báo qua Lark**: Khi có các giao dịch hoặc sự kiện quan trọng, plugin sẽ gửi thông báo qua Lark. Điều này giúp người dùng theo dõi giao dịch một cách thuận tiện hơn.

### Tích hợp với Telegram:

- **Gửi Thông Báo qua Telegram**: Tương tự như với Lark, plugin cũng hỗ trợ gửi thông báo qua Telegram. Điều này mang đến sự linh hoạt và tiện lợi cho người dùng trong việc nhận thông báo.

### Điều Khoản Sử Dụng và Chính Sách Bảo Mật:

Vui lòng xem xét Điều Khoản Sử Dụng và Chính Sách Bảo Mật của SoBanHang để hiểu cách dữ liệu của bạn được xử lý:
- [Điều Khoản Sử Dụng](https://sobanhang.com/dieu-khoan-dich-vu/)
- [Chính Sách Bảo Mật](https://sobanhang.com/chinh-sach-bao-mat/)
- Lark: [Chính Sách Bảo Mật](https://www.larksuite.com/vi_vn/privacy-policy), [Điều Khoản Sử Dụng](https://www.larksuite.com/vi_vn/user-terms-of-service)

Chúng tôi cam kết cung cấp sự minh bạch và đảm bảo an toàn thông tin cho người dùng khi tích hợp với các dịch vụ này.


== Frequently Asked Questions ==
Email - hotro@sobanhang.com

== Upgrade Notice ==
= version 1.0.7 =
* Cập nhật tối ưu phần tải mã QR về điện thoại

== Changelog ==
= 2024.05.02 - version 1.0.7 =
* Update: Update QRCode download

= 2024.04.26 - version 1.0.6 =
* Update: Update to new WooCommerce version

= 2023.12.21 - version 1.0.5 =
* Update: Optimize code

= 2023.10.14 - version 1.0.4 =
* Update: Add URL config for payment success page

= 2023.09.30 - version 1.0.3 =
* Update: Payment processing is more flexible
* New feature: Add notification option to Telegram, Lark

= 2023.09.29 - version 1.0.2 =
* Update: Enhanced order status selection for displaying successful payment page

= 2023.09.28 - version 1.0.1 =
* Update: Enhanced email sending

= 2023.09.17 - version 1.0.0 =
* Initial release
