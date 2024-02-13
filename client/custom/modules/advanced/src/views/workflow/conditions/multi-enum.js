
define('advanced:views/workflow/conditions/multi-enum', ['advanced:views/workflow/conditions/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/base',

        defaultConditionData: {
            comparison: 'true',
        },

        comparisonList: [
            'true',
            'false',
            'changed',
            'notChanged',
        ],

        data: function () {
            return _.extend({
            }, Dep.prototype.data.call(this));
        },
    });
});
