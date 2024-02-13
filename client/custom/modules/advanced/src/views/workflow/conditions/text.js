
define('advanced:views/workflow/conditions/text', 'advanced:views/workflow/conditions/base', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/base',

        defaultConditionData: {
            comparison: 'notEmpty'
        },

        comparisonList: [
            'notEmpty',
            'isEmpty',
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
