
define('advanced:views/workflow/conditions/attachment-multiple', 'advanced:views/workflow/conditions/link-multiple', function (Dep) {

    return Dep.extend({

        defaultConditionData: {
            comparison: 'notEmpty'
        },

        comparisonList: [
            'notEmpty',
            'isEmpty',
            'changed',
            'notChanged',
        ],

    });
});
