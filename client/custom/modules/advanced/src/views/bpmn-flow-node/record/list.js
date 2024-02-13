
define('advanced:views/bpmn-flow-node/record/list', ['views/record/list'], function (Dep) {
    /**
     * @module module:advanced_views/bpmn-flow-node/record/list
     */

    /**
     * @class
     * @name Class
     * @memberOf module:advanced_views/bpmn-flow-node/record/list
     * @extends module:views/record/list.Class
     */
    return Dep.extend(/** @lends module:advanced_views/bpmn-flow-node/record/list.Class# */{

        actionInterruptFlowNode: function (data) {
            this.actionRejectFlowNode(data);
        },

        actionRejectFlowNode: function (data) {
            let id = data.id;

            this.confirm(this.translate('confirmation', 'messages'), () => {
                Espo.Ajax
                    .postRequest('BpmnProcess/action/rejectFlowNode', {id: id})
                    .then(() => {
                        this.collection.fetch().then(() => {
                            Espo.Ui.success(this.translate('Done'));

                            if (this.collection.parentModel) {
                                this.collection.parentModel.fetch();
                            }
                        });
                    });
                });
        },

        actionViewError: function (data) {
            let model = this.collection.get(data.id);

            if (!model) {
                return;
            }

            let nodeData = model.get('data') || {};

            this.createView('dialog', 'advanced:views/bpmn-flow-node/modals/view-error', {nodeData: nodeData})
                .then(view => {
                    view.render();
                });
        },
    });
});
