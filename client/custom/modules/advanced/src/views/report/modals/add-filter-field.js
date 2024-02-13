

define('advanced:views/report/modals/add-filter-field', ['views/modal'], function (Dep) {

    return Dep.extend({

        templateContent: '<div class="field" data-name="filters">{{{field}}}</div>',

        backdrop: true,

        events: {
            'click a[data-action="addField"]': function (e) {
                this.trigger('add-field', $(e.currentTarget).data().name);
            }
        },

        data: function () {
            return {};
        },

        setup: function () {
            this.header = this.translate('Add Field');

            var scope = this.scope = this.options.scope;

            this.wait(true);

            this.getModelFactory().create('Report', model => {
                model.set('entityType', scope);

                this.createView('field', 'advanced:views/report/fields/filters', {
                    el: this.getSelector() + ' .field',
                    model: model,
                    mode: 'edit',
                    defs: {
                        name: 'filters',
                        params: {},
                    },
                }, view =>{
                    this.listenTo(view, 'change', () => {
                        var list = model.get('filters') || [];

                        if (!list.length) {
                            return;
                        }

                        this.trigger('add-field', list[0]);
                    });
                });

                this.wait(false);
            });
        }
    });
});
