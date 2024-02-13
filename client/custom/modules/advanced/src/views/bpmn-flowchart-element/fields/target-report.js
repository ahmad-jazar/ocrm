
define('advanced:views/bpmn-flowchart-element/fields/target-report', ['views/fields/link'], function (Dep) {

    return Dep.extend({

        selectPrimaryFilterName: 'list',
        createDisabled: true,

        getSelectFilters: function () {
            var entityType = this.model.targetEntityType;

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
