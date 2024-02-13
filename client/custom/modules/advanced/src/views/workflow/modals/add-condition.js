
define('advanced:views/workflow/modals/add-condition', ['views/modal'], function (Dep) {

    return Dep.extend({

        templateContent: '<div class="field" data-name="conditionFields">{{{field}}}</div>',

        backdrop: true,

        events: {
            'click a[data-action="addField"]': function (e) {
                this.trigger('add-field', $(e.currentTarget).data().name);
            }
        },

        setup: function () {
            this.header = this.translate('Add Condition', 'labels', 'Workflow');

            var scope = this.scope = this.options.scope;

            this.wait(true);

            this.getModelFactory().create('Workflow', model => {
                model.targetEntityType = scope;

                this.createView('field', 'advanced:views/workflow/fields/condition-fields', {
                    el: this.getSelector() + ' .field',
                    model: model,
                    mode: 'edit',
                    createdEntitiesData: this.options.createdEntitiesData,
                    defs: {
                        name: 'conditionFields',
                        params: {},
                    },
                }, view => {
                    this.listenTo(view, 'change', () => {
                        var list = model.get('conditionFields') || [];

                        if (!list.length) {
                            return;
                        }

                        this.trigger('add-field', list[0]);
                    });
                });

                this.wait(false);
            });
        },
    });
});

