
define('advanced:views/workflow/fields/workflow', ['views/fields/link'], function (Dep) {

    return Dep.extend({

        createDisabled: true,

        getSelectFilters: function () {
            var data = {
                'type': {
                    type: 'in',
                    value: ['sequential'],
                },
            };

            if (this.options.entityType) {
                data.entityType = {
                    type: 'in',
                    value: [this.options.entityType],
                };
            }

            return data;
        },
    });
});
