
define('advanced:views/workflow/conditions/enum', ['advanced:views/workflow/conditions/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/enum',

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
        ],

        data: function () {
            return _.extend({
            }, Dep.prototype.data.call(this));
        },

        getSubjectInputViewName: function (subjectType) {
            return 'advanced:views/workflow/condition-fields/subjects/enum-input';
        },
    });
});
