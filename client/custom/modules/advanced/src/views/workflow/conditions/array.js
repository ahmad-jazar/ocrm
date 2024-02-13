
define('advanced:views/workflow/conditions/array', ['advanced:views/workflow/conditions/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/enum',

        defaultConditionData: {
            comparison: 'notEmpty',
            subjectType: 'value',
        },

        comparisonList: [
            'notEmpty',
            'isEmpty',
            'has',
            'notHas',
            'changed',
            'notChanged',
        ],

        data: function () {
            return _.extend({
            }, Dep.prototype.data.call(this));
        },

        getSubjectInputViewName: function (subjectType) {
            var optionList = this.getMetadata()
                .get(['entityDefs', this.options.actualEntityType, 'fields', this.options.actualField, 'options']) ||
                [];

            if (optionList.length) {
                return 'advanced:views/workflow/condition-fields/subjects/enum-input';
            } else {
                return 'advanced:views/workflow/condition-fields/subjects/text-input';
            }
        },
    });
});
