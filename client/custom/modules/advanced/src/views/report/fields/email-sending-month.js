
define('advanced:views/report/fields/email-sending-month', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        setupOptions: function () {
            this.params.options = this.params.options.filter(item => item);
        },

        setupTranslation: function () {
            this.translatedOptions = {};

            let monthNames = this.translate('monthNames', 'lists');

            for (let i = 0; i < monthNames.length; i++) {
                this.translatedOptions[i + 1] = monthNames[i];
            }
        },
    });
});
