"use strict";

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

if (typeof window.interlabs === "undefined") {
    window.interlabs = {};
}
if (typeof window.interlabs.feedbackform === "undefined") {
    window.interlabs.feedbackform = {};
}

$(document).ready(function () {

    if (_typeof(window.__validatorMessages) === "object") {
        $.extend($.validator.messages, window.__validatorMessages);
    }

    //convert tag div to form on AJAX_MODE=Y
    if ($('.js-div-to-form-convert').length > 0) {
        $('.js-div-to-form-convert').each(function () {
            var form = $('<form></form>');
            var div = $(this);

            div.removeClass('js-div-to-form-convert').each(function () {
                $.each(this.attributes, function () {
                    // this.attributes is not a plain object, but an array
                    // of attribute nodes, which contain both the name and value
                    if (this.specified) {
                        console.log(this.name, this.value);
                        form.attr(this.name, this.value);
                    }
                });
                form.html(div.html());
            }).replaceWith(form);
            form.on('submit', function (event) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            });
        });
    }

    $('.interlabs-feedbackform__container').each(function () {
        var container = $(this);
        var dialog = container.find('.interlabs-feedbackform__container__dialog');

        /**
         * open dialog
         */
        container.find('.js-interlabs-feedbackform__container-show-button').on('click', function () {
            var container = $(this).parents('.interlabs-feedbackform__container:first');
            var dialog = container.find('.interlabs-feedbackform__container__dialog');
            dialog.find('.interlabs-feedbackform__container__errors .interlabs-feedbackform__container__errors__item').remove();
            dialog.removeClass('hidden');

            /**
             * close main dialog
             */
            dialog.find('.js-interlabs-feedbackform__dialog__close, .js-interlabs-feedbackform__dialog__cancel-button').off('click').on('click', function () {
                var closeButton = $(this);
                var container = closeButton.parents('.interlabs-feedbackform__container:first');
                var dialog = container.find('.interlabs-feedbackform__container__dialog');
                dialog.addClass('hidden');
            });
        });

        /**
         * close info dialog
         */
        container.find('.js-interlabs-feedbackform__dialog__close').on('click', function () {
            $(this).parents('.interlabs-feedbackform__container-succsess:first').addClass('hidden');
        });

        /**
         * Ajax send request
         */
        dialog.find('.ajax .js-interlabs-feedbackform__dialog__send-button').on('click', function () {
            var el = $(this);
            var container = el.parents('.interlabs-feedbackform__container:first');
            var dialog = container.find('.interlabs-feedbackform__container__dialog');

            var form = dialog.find('form');
            var formData = new FormData(form.get(0));

            var errorContainer = container.find('.interlabs-feedbackform__container__errors');
            if (errorContainer) {
                errorContainer.html();
            }
            var url = form.prop("action");

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
                success: function success(data) {
                    if (data.errors) {
                        // show errors
                        errorContainer.find('.interlabs-feedbackform__container__errors__item').remove();
                        var _iteratorNormalCompletion = true;
                        var _didIteratorError = false;
                        var _iteratorError = undefined;

                        try {
                            for (var _iterator = data.errors[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                                var error = _step.value;

                                if (errorContainer) {
                                    errorContainer.append("<label class=\"interlabs-feedbackform__container__errors__item\">" + error.message + "</label>");
                                } else {
                                    console.log(error.message);
                                }
                            }
                        } catch (err) {
                            _didIteratorError = true;
                            _iteratorError = err;
                        } finally {
                            try {
                                if (!_iteratorNormalCompletion && _iterator.return) {
                                    _iterator.return();
                                }
                            } finally {
                                if (_didIteratorError) {
                                    throw _iteratorError;
                                }
                            }
                        }
                    } else {
                        //data.data
                        dialog.addClass('hidden');
                        container.find('.interlabs-feedbackform__container-succsess').removeClass('hidden');
                        container.find('.interlabs-feedbackform__container-succsess .interlabs-feedbackform__container-succsess__close').on('click', function () {
                            container.find('.interlabs-feedbackform__container-succsess').addClass('hidden');
                        });
                        dialog.find('input[name="AGREE_PROCESSING"]').prop('checked', false);
                        dialog.find('input[type="file"]').val('');
                        dialog.find('input[type="text"]').val('');
                    }
                },
                fail: function fail() {
                    callback(true, null);
                }

            });
        });
    });
});
//# sourceMappingURL=script.js.map
