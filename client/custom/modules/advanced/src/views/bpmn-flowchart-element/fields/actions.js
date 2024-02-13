
define('advanced:views/bpmn-flowchart-element/fields/actions', ['views/fields/base', 'model'], function (Dep, Model) {

    return Dep.extend({

        detailTemplate: 'advanced:bpmn-flowchart-element/fields/actions/detail',
        editTemplate: 'advanced:bpmn-flowchart-element/fields/actions/detail',

        setup: function () {
            Dep.prototype.setup.call(this);

            var model = new Model;
            model.set('entityType', this.model.targetEntityType);

            var actionList = this.model.get('actionList') || [];
            model.set('actions', actionList);

            var actionTypeList = Espo.Utils.clone(
                this.getMetadata().get(['clientDefs', 'BpmnFlowchart', 'elements', 'task', 'fields', 'actions', 'actionTypeList'])
            );

            this.createView('actions', 'advanced:views/workflow/record/actions', {
                entityType: this.model.targetEntityType,
                el: this.getSelector() + ' > .actions-container',
                readOnly: this.mode !== 'edit',
                model: model,
                actionTypeList: actionTypeList,
                flowchartElementId: this.model.id,
                flowchartCreatedEntitiesData: this.model.flowchartCreatedEntitiesData,
            });
        },

        events: {

        },

        data: function () {
            var data = {};
            data.isEditMode = this.mode === 'edit';

            return data;
        },

        fetch: function () {
            var actionList = this.getView('actions').fetch();

            return {
                actionList: actionList
            };
        },
    });
});
