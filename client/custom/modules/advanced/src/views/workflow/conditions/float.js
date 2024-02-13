
define('advanced:views/workflow/conditions/float', 'advanced:views/workflow/conditions/int', function (Dep) {

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

        setup: function () {
            Dep.prototype.setup.call(this);

            if (this.getPreferences().has('decimalMark')) {
                this.decimalMark = this.getPreferences().get('decimalMark');
            } else {
                if (this.getConfig().has('decimalMark')) {
                    this.decimalMark = this.getConfig().get('decimalMark');
                }
            }
            if (this.getPreferences().has('thousandSeparator')) {
                this.thousandSeparator = this.getPreferences().get('thousandSeparator');
            } else {
                if (this.getConfig().has('thousandSeparator')) {
                    this.thousandSeparator = this.getConfig().get('thousandSeparator');
                }
            }
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

                        value = (value !== '') ? value : null;
                        if (value !== null) {
                            value = value.split(this.thousandSeparator).join('');
                            value = value.split(this.decimalMark).join('.');
                            value = parseFloat(value);
                        }

                        this.conditionData.value = value;

                        break;
                }
            }
        },

        getSubjectValue: function () {
            var value = this.conditionData.value;

            if (typeof value === 'undefined') {
                return '';
            }

            return this.formatNumber(value);
        },

        formatNumber: function (value) {
            if (value !== null) {
                var parts = value.toString().split(".");

                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.thousandSeparator);

                return parts.join(this.decimalMark);
            }

            return '';
        },
    });
});
