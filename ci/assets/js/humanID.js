const humanid = function () {

    return {

        formLogin: function () {
            let input = document.querySelector("#phone");
            var iti = window.intlTelInput(input, {
                preferredCountries: ["us", "id"],
                separateDialCode: true,
                initialCountry: ""
            });
            document.getElementById('dialcode').value = iti.getSelectedCountryData().dialCode;
            input.addEventListener("countrychange", function() {
                document.getElementById('dialcode').value = iti.getSelectedCountryData().dialCode;
            });
        },

        formLoginVeriy: function () {
            let timerOn = true;
            function timer(remaining) {
                var m = Math.floor(remaining / 60);
                var s = remaining % 60;
                m = m < 10 ? '0' + m : m;
                s = s < 10 ? '0' + s : s;
                if(success==1){
                    $('.timer-text').html(parseInt(s));
                }
                else{
                    $('.timer-text strong').html(m + ':' + s);
                }
                remaining -= 1;
                if(remaining >= 0 && timerOn) {
                    setTimeout(function() {
                        timer(remaining);
                    }, 1000);
                    return;
                }
                if(!timerOn) {
                    return;
                }
                location.href = $('.directed-link').val();
            }
            var setTime = (success==1) ? 5 : 120;
            timer(setTime);

            $('.humanid-input-otp').keyup(function(){
                var dataId = parseInt($(this).data('id'));
                var value = $(this).val();
                if(dataId >= 1 && dataId <= 4 && value){
                    if(dataId==4)
                    {
                        $('form').submit();
                    }
                    else{
                        $("input[name=code_"+ (dataId+1) +"]").focus();
                    }
                }
            });

            $('.directed-now').click(function(){
                location.href = $('.directed-link').val();
            });
        }
    };
}();