
define('advanced:views/report/fields/email-sending-day', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        setupOptions: function () {
            this.params.options = this.params.options.filter(item => item);
        },
    });
});
