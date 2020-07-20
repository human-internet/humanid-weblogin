const humanid = function () {

    return {

        checkFormTel: function () {
            let input = document.querySelector("#phone");
            window.intlTelInput(input, {
                separateDialCode: true
            });
        }

    };

}();