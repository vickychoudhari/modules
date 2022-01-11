/**
 * @file JS file to perform authentication and registration for miniOrange
 *       Authentication service.
 */
(function($) {
    var form_names = [ 'mo_auth_authenticate_user',
        'mo_auth_test_email_verification',
        'mo_auth_configure_qrcode_authentication',
        'mo_auth_test_qrcode_authentication',
        'mo_auth_inline_registration',
    ];

    $(document).ready(function() {
        var formIds = document.getElementsByName("form_id");
        var txId_data = document.getElementsByName("txId");
        var url_data = document.getElementsByName("url");
        for (var i = 0; i < formIds.length; i++) {
            if ($.inArray(formIds[i].value, form_names) != -1) {
                var str = formIds[i].value;
                var txId = txId_data[i].value;
                var url = url_data[i].value;
                str = str.replace(/_/g, "-");
                //console.log(str);

                getAuthStatus(str, txId, url);
            }
        }
  });

  function getAuthStatus(formId, txId_value, url_value) {
      var txId = txId_value;
      var jsonString = "{\"txId\":\"" + txId + "\"}";
      var url = url_value;

      $.ajax({
      url : url,
      type : "POST",
      dataType : "json",
      data : jsonString,
      contentType : "application/json; charset=utf-8",
      success : function(result) {
        var response = JSON.parse(JSON.stringify(result));

        if (response.status != 'IN_PROGRESS') {
          document.getElementById(formId).submit();
        } else {
          setTimeout(getAuthStatus, 1000, formId, txId, url);
        }
      }
    });
  }
}(jQuery));