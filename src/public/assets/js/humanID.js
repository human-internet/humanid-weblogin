const humanid = function () {

    return {

        formLogin: function (phoneNumber, priorityCountry) {
            let input = document.querySelector("#phoneDisplay");
            if (priorityCountry == null || priorityCountry == '') {
                priorityCountry = ["us"];
            }
            var iti = window.intlTelInput(input, {
                preferredCountries: priorityCountry,
                separateDialCode: true,
                initialCountry: "",
                excludeCountries: ["af"]
            });
            if (phoneNumber != null || phoneNumber != '') {
                iti.setNumber(phoneNumber);
            }
            var dialCode = $('#dialcode');
            var phone = $('#phone');
            var phoneDisplay = $('#phoneDisplay');
            dialCode.val(iti.getSelectedCountryData().dialCode);
            input.addEventListener("countrychange", function () {
                dialCode.val(iti.getSelectedCountryData().dialCode);
            });
            phoneDisplay.focus();
            phoneDisplay.keyup(function (e) {
                var valDisplay = this.value.replace(/[^\-0-9]/g, '');
                var valPhone = valDisplay.replace(/[^0-9]/g, '');
                var length = valPhone.length;
                phone.val(valPhone);
                if (length > 3 && length <= 7) {
                    if (length == 4)
                        valDisplay = valPhone.replace(/(\d{3})(\d{1})/, "$1-$2");
                    else if (length == 5)
                        valDisplay = valPhone.replace(/(\d{3})(\d{2})/, "$1-$2");
                    else if (length == 6)
                        valDisplay = valPhone.replace(/(\d{3})(\d{3})/, "$1-$2");
                    else
                        valDisplay = valPhone.replace(/(\d{3})(\d{4})/, "$1-$2");
                } else if (length > 7 && length <= 10) {
                    if (length == 8)
                        valDisplay = valPhone.replace(/(\d{3})(\d{3})(\d{2})/, "$1-$2-$3");
                    else if (length == 9)
                        valDisplay = valPhone.replace(/(\d{3})(\d{3})(\d{3})/, "$1-$2-$3");
                    else
                        valDisplay = valPhone.replace(/(\d{3})(\d{3})(\d{4})/, "$1-$2-$3");
                } else if (length > 10) {
                    if (length == 11)
                        valDisplay = valPhone.replace(/(\d{3})(\d{4})(\d{4})/, "$1-$2-$3");
                    else if (length == 12)
                        valDisplay = valPhone.replace(/(\d{3})(\d{4})(\d{4})(\d{1})/, "$1-$2-$3-$4");
                    else if (length == 13)
                        valDisplay = valPhone.replace(/(\d{3})(\d{4})(\d{4})(\d{2})/, "$1-$2-$3-$4");
                    else
                        valDisplay = valPhone.replace(/(\d{3})(\d{4})(\d{4})(\d{3})/, "$1-$2-$3-$4");
                }
                this.value = valDisplay;
            });
            setTimeout(function () {
                $('.humanid-text-info-danger').hide();
            }, 5000);
        },

        formLoginVeriy: function (success, failAttemptLimit) {
            var hasRedirected = false;
            setTimeout(function () {
                $('.humanid-text-info-danger').hide();
            }, 5000);

            function isInt(value) {
                return !isNaN(value) && (function (x) {
                    return (x | 0) === x;
                })(parseFloat(value))
            }

            let timerOn = true;

            function timer(remaining) {
                var m = Math.floor(remaining / 60);
                var s = remaining % 60;
                m = m < 10 ? '0' + m : m;
                s = s < 10 ? '0' + s : s;
                if (success == 1) {
                    $('.timer-text').html(parseInt(s));
                } else {
                    $('.timer-text strong').html(m + ':' + s);
                }
                remaining -= 1;
                $('#remaining').val(remaining);
                if (remaining >= 0 && timerOn) {
                    setTimeout(function () {
                        timer(remaining);
                    }, 1000);
                    return;
                }
                if (!timerOn) {
                    return;
                }
                if (success == 1) {
                    if (hasRedirected == false) {
                        hasRedirected = true;
                        $('.directed-now').prop("disabled", true);
                        location.href = $('.directed-link').val();
                    }
                } else {
                    $('.verify-area').hide();
                    $('.resend-area').show();
                }
            }

            var setTime = parseInt(failAttemptLimit);
            timer(setTime);

            $('.humanid-input-otp').keyup(function (e) {
                var value = $(this).val();
                var dataId = parseInt($(this).data('id'));
                if (isInt(value)) {
                    if (dataId >= 1 && dataId <= 4 && value) {
                        if (dataId == 4) {
                            $('form').submit();
                        } else {
                            $("input[name=code_" + (dataId + 1) + "]").focus();
                        }
                    }
                } else {
                    if (e.keyCode == 8) {
                        if (dataId >= 1 && dataId <= 4) {
                            if (dataId == 1) {
                                $("input[name=code_1]").val('').focus();
                            } else {
                                $("input[name=code_" + (dataId - 1) + "]").val('').focus();
                            }
                        }
                    }
                    $(this).val('');
                }
            });

            $('.directed-now').click(function () {
                if (hasRedirected == false) {
                    hasRedirected = true;
                    $('.directed-now').prop("disabled", true);
                    location.href = $('.directed-link').val();
                }
            });

            $('.resend-otp').click(function () {
                location.href = $('.directed-link').val();
            });
        },

        modal: function () {
            const overlay = $('.humanid-modal__overlay');
            const closeModal = $('.humanid-modal__modal__close');
            const target = $('[data-target]');
            target.click(function () {
                const modalTarget = $(this).data('target');
                overlay.addClass('active');
                $(`#${modalTarget}`).addClass('active');
            })
            closeModal.click(function () {
                const modalTargetClose = $(this).data('close');
                overlay.removeClass('active');
                $(`#${modalTargetClose}`).removeClass('active');
            })
        },

        setEmail: function () {
            const overlay = $('.humanid-modal__overlay');
            $('#close-popup').click(function () {
                const modalTargetClose = $(this).data('close');
                overlay.removeClass('active');
                $(`#${modalTargetClose}`).removeClass('active');
            })
        }
    };
}();
