if (typeof window.kit === "undefined") {
    window.kit = {};
}
if (typeof window.kit.feedbackform === "undefined") {
    window.kit.feedbackform = {};
}


$(document).ready(function () {

    if (typeof window.__validatorMessages === "object") {
        $.extend($.validator.messages, window.__validatorMessages);
    }

    //convert tag div to form on AJAX_MODE=Y
    if ($('.js-div-to-form-convert').length > 0) {
        $('.js-div-to-form-convert').each(function () {
            const form = $('<form></form>');
            const div = $(this);

            div.removeClass('js-div-to-form-convert')
                .each(function () {
                    $.each(this.attributes, function () {
                        // this.attributes is not a plain object, but an array
                        // of attribute nodes, which contain both the name and value
                        if (this.specified) {
                            console.log(this.name, this.value);
                            form.attr(this.name, this.value)
                        }
                    });
                    form.html(div.html())
                })
                .replaceWith(form);
            form.on('submit', function (event) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            })

        });
    }


    $('.kit-feedbackform__container').each(function () {
        const container = $(this);
        const dialog = container.find('.kit-feedbackform__container__dialog');


        /**
         * open dialog
         */
        container.find('.js-kit-feedbackform__container-show-button').on('click', function () {
            const container = $(this).parents('.kit-feedbackform__container:first');
            const dialog = container.find('.kit-feedbackform__container__dialog');
            dialog.find('.kit-feedbackform__container__errors .kit-feedbackform__container__errors__item').remove();
            dialog.removeClass('hidden');


            /**
             * close main dialog
             */
            dialog.find('.js-kit-feedbackform__dialog__close, .js-kit-feedbackform__dialog__cancel-button')
                .off('click')
                .on('click', function () {
                    const closeButton = $(this);
                    const container = closeButton.parents('.kit-feedbackform__container:first');
                    const dialog = container.find('.kit-feedbackform__container__dialog');
                    dialog.addClass('hidden');
                });
        });


        /**
         * close info dialog
         */
        container.find('.js-kit-feedbackform__dialog__close').on('click', function () {
            $(this).parents('.kit-feedbackform__container-succsess:first').addClass('hidden');
        });

        /**
         * Ajax send request
         */
        dialog.find('.ajax .js-kit-feedbackform__dialog__send-button').on('click', function () {
            const el = $(this);
            const container = el.parents('.kit-feedbackform__container:first');
            const dialog = container.find('.kit-feedbackform__container__dialog');

            const form = dialog.find('form');
            let formData = new FormData(form.get(0));

            const errorContainer = container.find('.kit-feedbackform__container__errors');
            if (errorContainer) {
                errorContainer.html();
            }
            let url = form.prop("action");

            if (!form.valid()) {
                return;
            }

            $.ajax({
                type: 'POST',
                //contentType: "application/json; charset=utf-8",
                //dataType: "json",
                url: url,
                processData: false,
                contentType: false,
                data: formData,
                success: function (data) {
                    if (data.errors) {
                        // show errors
                        errorContainer.find('.kit-feedbackform__container__errors__item').remove();
                        for (const error of data.errors) {
                            if (errorContainer) {
                                errorContainer.append(`<label class="kit-feedbackform__container__errors__item">${error.message}</label>`);
                            } else {
                                console.log(error.message);
                            }

                        }

                    } else {
                        //data.data
                        dialog.addClass('hidden');
                        container.find('.kit-feedbackform__container-succsess').removeClass('hidden');
                        container.find('.kit-feedbackform__container-succsess .kit-feedbackform__container-succsess__close').on('click',function(){
                            container.find('.kit-feedbackform__container-succsess').addClass('hidden');
                        });
                        dialog.find('input[name="AGREE_PROCESSING"]').prop('checked', false);
                        dialog.find('input[type="file"]').val('');

                    }

                },
                fail: function () {
                    callback(true, null);
                }

            });
        });
    });

});