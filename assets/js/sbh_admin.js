jQuery(document).ready(function ($) {
  $("#registersbh").on("click", function () {
    $("#sbh_error_merchant").text("");
    var contentTypeHeader = "application/json";
    var sbhKey = $("#woocommerce_ttckvsbh_ttckvsbh_key").val();
    var authorizationHeader = "Bearer " + sbhKey;
    var partner_code = $("#woocommerce_ttckvsbh_ttckvsbh_code").val();
    var partner_merchant_id = window.location.hostname;
    var bin_code = document.getElementById("ttckvsbh_bank_name_0").value;
    var bank_account_number = document.getElementById("ttckvsbh_account_number_0").value;
    var bank_holder_name = document.getElementById("ttckvsbh_account_name_0").value;

    var requestData = {
      partner_code: partner_code,
      partner_merchant_id: partner_merchant_id,
      bin_code: bin_code,
      bank_account_number: bank_account_number,
      bank_holder_name: bank_holder_name,
    };

    // Chuyển đổi đối tượng JavaScript thành chuỗi JSON
    $.ajax({
      url: "https://api.finan.cc/api/v1/partner/create-merchant-bank-account",
      method: "POST",
      headers: {
        Authorization: authorizationHeader,
        "Content-Type": contentTypeHeader,
      },
      data: JSON.stringify(requestData), // Gán chuỗi JSON vào data
      success: function (response) {
        if (response.return_code == 1) {
          $("#woocommerce_ttckvsbh_ttckvsbh_merchant_bank_account_id").val(
            response.data.merchant_bank_account_id
          );
          $("#sbh_error_merchant").text("Kích hoạt mã thành công!");
        } else {
          $("#sbh_error_merchant").text(response.return_message);
        }
      },
      error: function (error) {
        $("#sbh_error_merchant").text(error.responseText);
      },
    });
  });

  $("#registerhook").on("click", function () {
    $("#sbh_errorhook").text("");
    // Lấy các giá trị từ các trường dữ liệu trong trang cài đặt
    var contentTypeHeader = "application/json";
    var sbhKey = $("#woocommerce_ttckvsbh_ttckvsbh_key").val();
    var authorizationHeader = "Bearer " + sbhKey;
    var requestData = {
      api_key: siteData.api_key,
      client_id: siteData.client_id,
      url: window.location.origin + '/wc-api/ttckvsbh',
    };

    var jsonData = JSON.stringify(requestData);
    $.ajax({
      url: "https://api.finan.cc/api/v1/partner/create-webhook",
      method: "POST",
      headers: {
        Authorization: authorizationHeader,
        "Content-Type": contentTypeHeader,
      },
      data: jsonData, // Gán chuỗi JSON vào data
      success: function () {
        $("#woocommerce_ttckvsbh_ttckvsbh_hook_status").val("Kích hoạt webhook thành công!")
        $("#sbh_errorhook").text("Kích hoạt webhook thành công!");
      },
      error: function () {
        $("#woocommerce_ttckvsbh_ttckvsbh_hook_status").val("Kích hoạt webhook lỗi, hãy thử lại!")
        $("#sbh_errorhook").text("Kích hoạt webhook lỗi, hãy thử lại!");
      },
    });
  });
});