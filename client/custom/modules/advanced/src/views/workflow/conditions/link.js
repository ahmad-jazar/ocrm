
define('advanced:views/workflow/conditions/link', ['advanced:views/workflow/conditions/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/base',

        defaultConditionData: {
            comparison: 'notEmpty',
        },

        comparisonList: [
            'notEmpty',
            'isEmpty',
            'equals',
            'notEquals',
            'changed',
            'notChanged',
        ],

        setupComparisonList: function () {
            Dep.prototype.setupComparisonList.call(this)

            if (this.fieldType === 'image' || this.fieldType === 'file') {
                var comparisonList = [];

                Espo.Utils.clone(this.comparisonList).forEach(item => {
                    if (~['equals', 'notEquals', 'wasEqual', 'wasNotEqual'].indexOf(item)) {
                        return;
                    }

                    comparisonList.push(item);
                });

                this.comparisonList = comparisonList;
            }
        },

        data: function () {
            return _.extend({
            }, Dep.prototype.data.call(this));
        },

        getSubjectInputViewName: function (subjectType) {
            return 'advanced:views/workflow/condition-fields/subjects/link';
        },
    });
});
