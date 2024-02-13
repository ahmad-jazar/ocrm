
define('advanced:views/report-filter/fields/report', ['views/fields/link'], function (Dep) {

    return Dep.extend({

        selectPrimaryFilterName: 'list',

        createDisabled: true,

        getSelectFilters: function () {
            var entityType = this.model.get('entityType');

            if (!entityType) {
                return;
            }

            return {
                entityType: {
                    type: 'equals',
                    value: [entityType],
                }
            };
        },
    });
});
