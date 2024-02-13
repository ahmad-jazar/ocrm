
define('advanced:views/workflow/action-fields/add-field', ['view'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/action-fields/add-field',

        data: function () {
            return {
                fieldList: this.options.fieldList,
                scope: this.options.scope,
            };
        },
    });
});
