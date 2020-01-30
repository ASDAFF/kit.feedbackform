if (typeof window.interlabs === "undefined") {
    window.interlabs = {};
}
if (typeof window.interlabs.feedbackform === "undefined") {
    window.interlabs.feedbackform = {};
}
$(document).ready(function () {
    /**
     * open dialog
     */
    $('.interlabs-feedbackform__container__dialog').each(function () {

        const dialog = $(this);

        const form = dialog.find('form');

        const fields = form.data('validatefields');

        dialog.find('.js-interlabs-feedbackform__dialog__close, .js-interlabs-feedbackform__dialog__cancel-button').on('click', function () {
            window.interlabs.feedbackform.closeDialog(this);
        });
    });

    window.interlabs.feedbackform.closeDialog = function (context) {
        const container = $(context).parents('.interlabs-feedbackform__container:first');
        const dialog = container.find('.interlabs-feedbackform__container__dialog:first');
        dialog.addClass('hidden');
    };


    window.interlabs.feedbackform.closeSuccessDialog = function (context) {
        const container = $(context).parents('.interlabs-feedbackform__container:first');
        const dialogSuccsess = container.find('.interlabs-feedbackform__container-succsess:first');
        const dialog = container.find('.interlabs-feedbackform__container__dialog:first');
        dialogSuccsess.addClass('hidden');
        dialog.removeClass('hidden');
    }


});