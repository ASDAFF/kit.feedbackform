"use strict";

if (typeof window.kit === "undefined") {
    window.kit = {};
}
if (typeof window.kit.feedbackform === "undefined") {
    window.kit.feedbackform = {};
}
$(document).ready(function () {
    /**
     * open dialog
     */
    $('.kit-feedbackform__container__dialog').each(function () {

        var dialog = $(this);

        var form = dialog.find('form');

        var fields = form.data('validatefields');

        dialog.find('.js-kit-feedbackform__dialog__close, .js-kit-feedbackform__dialog__cancel-button').on('click', function () {
            window.kit.feedbackform.closeDialog(this);
        });
    });

    window.kit.feedbackform.closeDialog = function (context) {
        var container = $(context).parents('.kit-feedbackform__container:first');
        var dialog = container.find('.kit-feedbackform__container__dialog:first');
        dialog.addClass('hidden');
    };

    window.kit.feedbackform.closeSuccessDialog = function (context) {
        var container = $(context).parents('.kit-feedbackform__container:first');
        var dialogSuccsess = container.find('.kit-feedbackform__container-succsess:first');
        var dialog = container.find('.kit-feedbackform__container__dialog:first');
        dialogSuccsess.addClass('hidden');
        dialog.removeClass('hidden');
    };
});
//# sourceMappingURL=script.js.map
