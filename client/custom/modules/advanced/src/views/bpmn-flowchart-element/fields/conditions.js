
define('advanced:views/bpmn-flowchart-element/fields/conditions',
['views/fields/base', 'model'], function (Dep, Model) {

    return Dep.extend({

        detailTemplate: 'advanced:bpmn-flowchart-element/fields/conditions/detail',
        editTemplate: 'advanced:bpmn-flowchart-element/fields/conditions/detail',

        setup: function () {
            Dep.prototype.setup.call(this);

            this.conditionsModel = new Model();

            this.conditionsModel.set({
                conditionsAll: this.model.get('conditionsAll') || [],
                conditionsAny: this.model.get('conditionsAny') || [],
                conditionsFormula: this.model.get('conditionsFormula') || null,
            });

            var isChangedDisabled = true;
            var flowchartCreatedEntitiesData = this.model.flowchartCreatedEntitiesData;

            if (this.model.elementType === 'eventStartConditional') {
                flowchartCreatedEntitiesData = null;
                isChangedDisabled = false;
            }

            this.createView('conditions', 'advanced:views/workflow/record/conditions', {
                entityType: this.model.targetEntityType,
                el: this.getSelector() + ' > .conditions-container',
                readOnly: this.mode !== 'edit',
                model: this.conditionsModel,
                flowchartCreatedEntitiesData: flowchartCreatedEntitiesData,
                isChangedDisabled: isChangedDisabled
            });
        },

        fetch: function () {
            var conditionsData = this.getView('conditions').fetch();

            return {
                'conditionsAll': conditionsData.all,
                'conditionsAny': conditionsData.any,
                'conditionsFormula': conditionsData.formula,
            };
        },
    });
});
