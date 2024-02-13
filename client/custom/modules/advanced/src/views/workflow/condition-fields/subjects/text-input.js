
define('advanced:views/workflow/condition-fields/subjects/text-input', ['view'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/condition-fields/subjects/text-input',

        data: function () {
            return {
                value: this.options.value,
                readOnly: this.options.readOnly,
            };
        },
    });
});
