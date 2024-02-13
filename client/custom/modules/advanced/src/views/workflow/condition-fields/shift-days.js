
define('advanced:views/workflow/condition-fields/shift-days', ['view'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/condition-fields/shift-days',

        data: function () {
            return {
                shiftDaysOperator: this.shiftDaysOperator,
                value: this.value,
                readOnly: this.readOnly,
            };
        },

        setup: function () {
            this.value = this.options.value;
            this.readOnly = this.options.readOnly;

            if (this.value < 0) {
                this.shiftDaysOperator = 'minus';
                this.value = (-1) * this.value;
            } else {
                this.shiftDaysOperator = 'plus';
            }
        },
    });
});
