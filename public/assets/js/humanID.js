const humanid = function () {

    return {

        formLogin: function () {
            let input = document.querySelector("#phoneDisplay");
            var iti = window.intlTelInput(input, {
                preferredCountries: ["us", "id"],
                separateDialCode: true,
                initialCountry: ""
            });
            var countryCode = $('#dialcode');
            var phone = $('#phone');
            countryCode.val(iti.getSelectedCountryData().dialCode);
            input.addEventListener("countrychange", function() {
                countryCode.val(iti.getSelectedCountryData().dialCode);
            });

            $('#phoneDisplay').keyup(function(e) {
                if ((e.keyCode > 47 && e.keyCode < 58) || (e.keyCode < 106 && e.keyCode > 95)) {
                    this.value = this.value.replace(/(\d{3})\-?/g, '$1-');
                    phone.val(this.value.replace(/[^0-9]/g, ''));
                    return true;
                }
                this.value = this.value.replace(/[^\-0-9]/g, '');
                phone.val(this.value.replace(/[^0-9]/g, ''));
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
                if(success==1){
                    location.href = $('.directed-link').val();
                }
                else{
                    $('.verify-area').hide();
                    $('.resend-area').show();
                }
            }
            var setTime = parseInt(failAttemptLimit);
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
            
            $('.resend-otp').click(function(){
                location.href = $('.directed-link').val();
            });
        }
    };
}();