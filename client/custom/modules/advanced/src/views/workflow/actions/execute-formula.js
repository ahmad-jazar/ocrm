
define('advanced:views/workflow/actions/execute-formula',
    ['advanced:views/workflow/actions/base', 'model'], function (Dep, Model) {

    return Dep.extend({

        type: 'executeFormula',

        template: 'advanced:workflow/actions/execute-formula',

        defaultActionData: {
            formula: null,
        },

        afterRender: function () {
            this.$formulaField = this.$el.find('.field[data-name="formula"]');
            this.renderFormula();
        },

    });
});
