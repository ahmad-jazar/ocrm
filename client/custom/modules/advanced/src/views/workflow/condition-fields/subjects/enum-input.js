
define('advanced:views/workflow/condition-fields/subjects/enum-input',
['view', 'advanced:workflow-helper'], function (Dep, Helper) {

    return Dep.extend({

        template: 'advanced:workflow/condition-fields/subjects/enum-input',

        data: function () {
            return {
                readOnly: this.options.readOnly
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.field = this.options.field;
            this.entityType = this.options.entityType;
            this.conditionData = this.options.conditionData || {};

            this.wait(true);

            var helper = new Helper(this.getMetadata());

            var entityType = helper.getComplexFieldEntityType(this.field, this.entityType);
            var field = helper.getComplexFieldFieldPart(this.field);

            this.realField = field;

            this.getModelFactory().create(entityType, (model) => {
                model.set(this.realField, this.conditionData.value);

                var viewName = this.getMetadata().get('entityDefs.' + entityType + '.fields.' + field + '.view') ||
                    'views/fields/enum';

                this.createView('field', viewName, {
                    el: this.options.el + ' .field-container',
                    mode: 'edit',
                    model: model,
                    readOnly: this.options.readOnly,
                    defs: {
                        name: this.realField
                    }
                }, (view) => {
                    if (!this.options.readOnly && view.readOnly) {
                        view.readOnlyLocked = false
                        view.readOnly = false;
                        view.setMode('edit');
                        view.reRender();
                    }

                    this.wait(false);
                });
            });
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            this.$el.find('select').addClass('input-sm');
            this.$el.find('.selectize-control').addClass('input-sm');
        },

        fetch: function () {
            var view = this.getView('field');
            var data = view.fetch();

            return {
                value: data[this.realField]
            };
        },
    });
});
