
define('advanced:views/workflow/conditions/int', 'advanced:views/workflow/conditions/base', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/base',

        comparisonList: [
            'equals',
            'wasEqual',
            'notEquals',
            'wasNotEqual',
            'greaterThan',
            'lessThan',
            'greaterThanOrEquals',
            'lessThanOrEquals',
            'isEmpty',
            'notEmpty',
            'changed',
            'notChanged',
        ],

        defaultConditionData: {
            comparison: 'equals',
            subjectType: 'value',
        },

        fetchSubject: function () {
            var $subject = this.$el.find('[data-name="subject"]');

            delete this.conditionData.value;
            delete this.conditionData.field;

            if ($subject.length) {
                switch (this.conditionData.subjectType) {
                    case 'field':
                        this.conditionData.field = $subject.val();

                        break;

                    case 'value':
                        var value = $subject.val();

                        if (value === '') {
                            value = null;
                        } else {
                            value = parseInt(value)
                        }

                        this.conditionData.value = value;

                        break;
                }
            }
        },

        getSubjectValue: function () {
            return this.conditionData.value;
        }
    });
});
