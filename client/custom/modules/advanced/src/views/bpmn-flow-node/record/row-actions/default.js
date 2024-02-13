
define('advanced:views/bpmn-flow-node/record/row-actions/default', ['views/record/row-actions/default'], function (Dep) {

    return Dep.extend({

        getActionList: function () {
            let list = [];

            let status = this.model.get('status');
            let elementType = this.model.get('elementType');

            if (['In Process'].includes(status)) {
                list.push({
                    action: 'interruptFlowNode',
                    html: this.translate('Interrupt', 'labels', 'BpmnProcess'),
                    data: {
                        id: this.model.id,
                    },
                });
            }

            if (!['Processed', 'Interrupted', 'Rejected', 'Failed'].includes(status)) {
                list.push({
                    action: 'rejectFlowNode',
                    html: this.translate('Reject', 'labels', 'BpmnProcess'),
                    data: {
                        id: this.model.id,
                    },
                });
            }

            if (
                ['eventStartError', 'eventIntermediateErrorBoundary'].includes(elementType) &&
                status === 'Processed'
            ) {
                list.push({
                    action: 'viewError',
                    html: this.translate('View Error', 'labels', 'BpmnProcess'),
                    data: {
                        id: this.model.id,
                    },
                });
            }

            return list;
        },
    });
});
