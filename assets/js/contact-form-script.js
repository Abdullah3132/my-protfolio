(function ($) {
  "use strict";

  // Run after DOM is ready so the form exists
  $(function () {
    var $form = $("#contactForm");
    var $btn  = $form.find('button[type="submit"]');
    var $msg  = $("#msgSubmit");

    $form.on("submit", function (e) {
      e.preventDefault(); // stop navigation to script.googleusercontent.com

      // Use native HTML5 validation if present
      if (this.checkValidity && !this.checkValidity()) {
        this.reportValidity();
        return;
      }

      // UX: lock the button to prevent follow-ups
      $btn.prop("disabled", true).text("Sending...");

      $.ajax({
        type: "POST",
        url: $form.attr("action"),
        data: $form.serialize(),          // sends all fields (name, email, etc.)
        success: function (resp) {
          // Handle both JSON response and "success" string
          var data = null;
          try { data = (typeof resp === "string") ? JSON.parse(resp) : resp; } catch (_) {}
          var ok = (data && data.success === true) || resp === "success";
          var msgText = (data && data.message) ? data.message : "Message Submitted!";

          if (ok) {
            $form[0].reset();
            $msg.removeClass().addClass("h4 text-left tada animated text-success").text("✅ " + msgText);
            $btn.text("Message Sent");     // keep disabled → no follow-up submits
          } else {
            $msg.removeClass().addClass("h4 text-left text-danger").text("Something went wrong. Please try again.");
            $btn.prop("disabled", false).text("Send Me Message");
          }
        },
        error: function () {
          $msg.removeClass().addClass("h4 text-left text-danger").text("Network error. Please try again later.");
          $btn.prop("disabled", false).text("Send Me Message");
        }
      });
    });
  });
})(jQuery);
