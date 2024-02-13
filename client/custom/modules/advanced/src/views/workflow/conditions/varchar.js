
define('advanced:views/workflow/conditions/varchar', ['advanced:views/workflow/conditions/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/base',

        defaultConditionData: {
            comparison: 'equals',
            subjectType: 'value',
        },

        comparisonList: [
            'equals',
            'wasEqual',
            'notEquals',
            'wasNotEqual',
            'isEmpty',
            'notEmpty',
            'changed',
            'notChanged',
            'contains',
            'notContains',
        ],

        data: function () {
            return _.extend({
            }, Dep.prototype.data.call(this));
        },
    });
});
