/*==============================================================*/
// Klev Contact Form JS
/*==============================================================*/
(function ($) {
    "use strict"; // Start of use strict
    $("#contactForm").validator().on("submit", function (event) {
        if (event.isDefaultPrevented()) {
            // handle the invalid form...
            formError();
            submitMSG(false, "Did you fill in the form properly?");
        } else {
            // everything looks good!
            event.preventDefault();
            submitForm();
        }
    });

    function submitForm(){
        // Initiate Variables With Form Content
        var name = $("#name").val();
        var email = $("#email").val();
        var phone_number = $("#phone_number").val();
        var subject = $("#subject").val();
        var message = $("#message").val();

        $.ajax({
            type: "POST",
            url: "https://script.google.com/macros/s/AKfycbz8QMx2QbRE_I5pDLvb_AU-jUL4vCTneYqkOQxhkc0o87BdtgO5T0A50XnHNC3VRo9u/exec",
            data: {
                name: name,
                email: email,
                phone_number: phone_number,
                subject: subject,
                message: message
            },
            success : function(text){
                if (text == "success"){
                    formSuccess();
                } else {
                    formError();
                    submitMSG(false, text);
                }
            },
            error: function() {
                formError();
                submitMSG(false, "Something went wrong. Please try again later.");
            }
        });
    }

    function formSuccess(){
        // Reset form
        $("#contactForm")[0].reset();

        // Show success message
        submitMSG(true, "✅ Message Submitted!");

        // Disable submit button to prevent follow-ups
        $("#contactForm button[type=submit]")
            .prop("disabled", true)
            .text("Message Sent");
    }

    function formError(){
        $("#contactForm").removeClass().addClass('shake animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
            $(this).removeClass();
        });
    }

    function submitMSG(valid, msg){
        var msgClasses = valid
            ? "h4 text-left tada animated text-success"
            : "h4 text-left text-danger";

        $("#msgSubmit").removeClass().addClass(msgClasses).text(msg);
    }
}(jQuery)); // End of use strict
