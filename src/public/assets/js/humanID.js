const humanid = function () {

  return {
    countdownFormSubmit: function (duration, display, target) {
      var timeleft = duration;
      var downloadTimer = setInterval(function() {
        if(timeleft <= 0){
          clearInterval(downloadTimer);
          $(target).submit();
        } else {
          $(display).text(timeleft);
        }
        timeleft -= 1;
      }, 1000);
    },

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

      const inputs = document.querySelectorAll('.humanid-input-otp');
      for (let i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener('keyup', function (event) {
          let inputId = Number(inputs[i].getAttribute('data-id'));

          if (event.key === "Backspace") {
            inputs[i].value = '';
            if (i !== 0) {
              inputs[i - 1].focus();
            }
          }

          if (/\d/i.test(inputs[i].value)) {
            if (i !== inputs.length - 1) {
              inputs[i + 1].focus();
            }
            event.preventDefault();
          }

          if (inputId === inputs.length) {
            const inputFull = [];
            inputs[i].blur();
            for (let index = 0; index < inputs.length; index++) {
              if (inputs[index].value.length > 0) {
                inputFull.push(true);
              }
            }
            if (inputFull.length === inputs.length) {
              $('form').submit();
            }
          }
        });
        inputs[i].addEventListener('paste', async function (event) {
          const paste = (event.clipboardData || window.clipboardData).getData('text');
          console.log("Paste: " + paste);
          const setTimeoutPromise = new Promise((resolve) => {
            setTimeout(() => {
              for (let index = 0; index < inputs.length; index++) {
                inputs[index].value = '';
                if (paste[index]) {
                  inputs[index].value = paste[index];
                }
              }
              resolve(true);
            }, 1);
          });
          setTimeoutPromise.then(function () {
            if (paste.length === inputs.length) {
              $('form').submit();
            }
          });
        })
      }

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
      const closeButton = $('#humanid-modal-close-button');
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
      closeButton.click(function () {
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
