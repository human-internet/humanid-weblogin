const humanid = function () {

    return {

        formLogin: function (phoneNumber) {
            let input = document.querySelector("#phoneDisplay");
            var iti = window.intlTelInput(input, {
                preferredCountries: ["us","id"],
                separateDialCode: true,
                initialCountry: ""
            });
            if(phoneNumber != null || phoneNumber != ''){
                iti.setNumber(phoneNumber);
            }
            var dialCode = $('#dialcode');
            var phone = $('#phone');
            var phoneDisplay = $('#phoneDisplay');
            dialCode.val(iti.getSelectedCountryData().dialCode);
            input.addEventListener("countrychange", function() {
                dialCode.val(iti.getSelectedCountryData().dialCode);
            });
            phoneDisplay.focus();
            phoneDisplay.keyup(function(e) {
                if ((e.keyCode > 47 && e.keyCode < 58) || (e.keyCode < 106 && e.keyCode > 95)) {
                    var length = this.value.length;
                    if(length > 7){
                        if(length > 12){
                            this.value = this.value.replace(/(\d{4})\-?/g, '$1-');
                        }
                        else{
                            this.value = this.value.replace(/(\d{4})\-?/g, '$1-');
                        }
                    }
                    else{
                        this.value = this.value.replace(/(\d{3})\-?/g, '$1-');
                    }
                    phone.val(this.value.replace(/[^0-9]/g, ''));
                    return true;
                }
                this.value = this.value.replace(/[^\-0-9]/g, '');
                phone.val(this.value.replace(/[^0-9]/g, ''));
            });
            setTimeout(function() {
                $('.humanid-text-info-danger').hide();
            }, 5000);
        },

        formLoginVeriy: function (success, failAttemptLimit) {
            setTimeout(function() {
                $('.humanid-text-info-danger').hide();
            }, 5000);
            function isInt(value) {
                return !isNaN(value) && (function(x) { return (x | 0) === x; })(parseFloat(value))
            }
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
                $('#remaining').val(remaining);
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

            $('.humanid-input-otp').keyup(function(e){
                var value = $(this).val();
                var dataId = parseInt($(this).data('id'));
                if (isInt(value))
                {
                    if(dataId >= 1 && dataId <= 4 && value){
                        if(dataId==4)
                        {
                            $('form').submit();
                        }
                        else{
                            $("input[name=code_"+ (dataId+1) +"]").focus();
                        }
                    }
                }
                else{
                    if(e.keyCode == 8){
                        if(dataId >= 1 && dataId <= 4){
                            if(dataId==1)
                            {
                                $("input[name=code_1]").val('').focus();
                            }
                            else{
                                $("input[name=code_"+ (dataId - 1) +"]").val('').focus();
                            }
                        }
                    }
                    $(this).val('');
                }
            });

            $('.humanid-input-otp').keydown(function() {
                //alert($(this).val());
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