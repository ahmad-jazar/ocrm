
define('advanced:views/workflow/fields/flowchart', 'views/fields/link', function (Dep) {

    return Dep.extend({

        selectPrimaryFilterName: 'active',

        createDisabled: true,

        setup: function () {
            Dep.prototype.setup.call(this);

            this.targetEntityType = this.options.targetEntityType;

            this.listenTo(this.model, 'change-target-entity-type', function (targetEntityType) {
                this.targetEntityType = targetEntityType;
            });
        },

        select: function (model) {
            var hash = model.get('elementsDataHash') || {};

            var translation = {};

            (model.get('eventStartAllIdList') || []).forEach(function (id) {
                var item = hash[id];
                if (!item) return;

                var label = item.text || id;
                label = this.translate(item.type, 'elements', 'BpmnFlowchart') + ': ' + label;

                translation[id] = label;
            }, this);

            this.model.set('startElementNames', translation);

            this.model.set('startElementIdList', model.get('eventStartAllIdList'));

            Dep.prototype.select.call(this, model);
        },

        getSelectFilters: function () {
            if (!this.targetEntityType) return;
            return {
                targetType: {
                    type: 'in',
                    value: [this.targetEntityType]
                }
            };
        },
    });
});
