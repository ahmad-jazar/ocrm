
define('advanced:views/workflow/action-modals/update-process-entity', [
        'advanced:views/workflow/action-modals/update-entity',
        'advanced:views/workflow/action-modals/create-entity',
        'model'
    ], function (Dep, CreateEntity, Model) {

    return Dep.extend({

        template: 'advanced:workflow/action-modals/update-entity',

        ignoreFieldList: [
            'endedAt',
            'status',
            'targetType',
            'target',
            'flowchartData',
            'flowchart',
            'flowchartElementsDataHash',
            'flowchartVisualization',
            'createdEntitiesData',
            'variables',
            'modifiedAt',
            'endedAt',
            'createdBy',
            'modifiedBy',
            'createdAt',
            'workflowId',
            'startElementId',
            'rootProcess',
            'parentProcessFlowNode',
            'parentProcess',
        ],

        setupScope: function (callback) {
            this.scope = 'BpmnProcess';

            this.getModelFactory().create(this.scope, (model) => {
                this.model = model;

                (this.actionData.fieldList || []).forEach(field => {
                    let attributes = (this.actionData.fields[field] || {}).attributes || {};

                    model.set(attributes, {silent: true});
                });

                callback();
            });
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.hasFormulaAvailable = !!this.getMetadata().get('app.formula.functionList');

            this.wait(true);

            this.setupScope(() => {
                this.createView('addField', 'advanced:views/workflow/action-fields/add-field', {
                    el: this.options.el + ' .add-field-container',
                    scope: this.scope,
                    fieldList: this.getFieldList(),
                });

                this.wait(false);
            });
        },

        getFieldList: function () {
            let fieldDefs = this.getMetadata().get('entityDefs.' + this.scope + '.fields') || {};

            return Object.keys(fieldDefs)
                .filter(field => {
                    if (fieldDefs[field].disabled) {
                        return false;
                    }

                    if (~this.ignoreFieldList.indexOf(field)) {
                        return;
                    }

                    return true;
                })
                .sort((v1, v2) => {
                    return this.translate(v1, 'fields', this.scope)
                        .localeCompare(this.translate(v2, 'fields', this.scope));
                });
        },
    });
});
