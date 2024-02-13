
define('advanced:views/bpmn-flowchart-element/fields/call-activity-flowchart', ['views/fields/link'], function (Dep) {

    return Dep.extend({

        selectPrimaryFilterName: 'activeHasNoneStartEvent',

        createDisabled: true,

        getSelectFilters: function () {
            var entityType = this.model.elementHelper.getEntityTypeFromTarget(this.model.get('target'));

            if (!entityType) {
                return;
            }

            var data = {};

            if (entityType) {
                data.targetType = {
                    type: 'in',
                    value: [entityType],
                };
            }

            return data;
        },
    });
});
