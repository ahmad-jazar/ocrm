
define('advanced:views/workflow/actions/trigger-workflow',
['advanced:views/workflow/actions/base', 'model'], function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow/actions/trigger-workflow',

        type: 'triggerWorkflow',

        defaultActionData: {
            execution: {
                type: 'immediately',
                field: false,
                shiftDays: 0,
                shiftUnit: 'days',
            }
        },

        data: function () {
            var data = Dep.prototype.data.call(this);
            data.targetTranslated = this.getTargetTranslated();

            return data;
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.createView('executionTime', 'advanced:views/workflow/action-fields/execution-time', {
                el: this.options.el + ' .execution-time-container',
                executionData: this.actionData.execution || {},
                entityType: this.entityType,
                readOnly: true,
            });

            var model = new Model();

            model.name = 'Workflow';
            model.set({
                workflowId: this.actionData.workflowId,
                workflowName: this.actionData.workflowName
            });

            this.createView('workflow', 'views/fields/link', {
                el: this.options.el + ' .field-workflow',
                model: model,
                mode: 'edit',
                foreignScope: 'Workflow',
                defs: {
                    name: 'workflow',
                    params: {
                        required: true,
                    },
                },
                readOnly: true,
            });
        },

        render: function (callback) {
            this.getView('executionTime').reRender();

            var workflowView = this.getView('workflow');
            workflowView.model.set({
                workflowId: this.actionData.workflowId,
                workflowName: this.actionData.workflowName
            });

            workflowView.reRender();

            Dep.prototype.render.call(this, callback);
        },

        getTargetTranslated: function () {
            return this.translateTargetItem(this.actionData.target);
        },
    });
});
