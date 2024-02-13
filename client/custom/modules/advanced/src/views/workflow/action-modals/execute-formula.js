
define('advanced:views/workflow/action-modals/execute-formula',
    ['advanced:views/workflow/action-modals/base', 'model'], function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow/action-modals/execute-formula',

        setup: function () {
            Dep.prototype.setup.call(this);

            var model = new Model;

            model.set('formula', this.actionData.formula || null);

            this.createView('formula', 'views/fields/formula', {
                name: 'formula',
                model: model,
                mode: this.readOnly ? 'detail' : 'edit',
                height: 200,
                el: this.getSelector() + ' .field[data-name="formula"]',
                inlineEditDisabled: true,
                targetEntityType: this.entityType,
            });
        },

        fetch: function () {
            var formulaView = this.getView('formula');

            this.actionData.formula = formulaView.fetch().formula;

            return true;
        },

    });
});
