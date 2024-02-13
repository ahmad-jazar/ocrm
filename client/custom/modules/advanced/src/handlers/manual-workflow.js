
define('advanced:handlers/manual-workflow', ['dynamic-logic'], function (DynamicLogic) {
/**
 * @module advanced_handlers/manual-workflow
 */

    /**
     * @typedef module:advanced_handlers/manual-workflow~Item
     * @type Object
     * @property {string} id
     * @property {'Button'|'Dropdown-Item'} elementType
     * @property {string} label
     * @property {'read'|'edit'|'admin'} accessRequired
     * @property {?{'conditionGroup': Object[]}} dynamicLogic
     */

    /**
     * @class
     * @name Class
     * @memberOf module:advanced_handlers/manual-workflow
     */
    let Handler = function (view) {
        /** @type {module:views/detail.Class} */
        this.view = view;
    };

    _.extend(Handler.prototype, /** @lends module:advanced_handlers/manual-workflow.Class# */{

        process: function () {
            let allWorkflows = this.view.getHelper().getAppParam('manualWorkflows') || {};
            /** @type {module:advanced_handlers/manual-workflow~Item[]} */
            let workflowList = allWorkflows[this.view.scope] || [];

            if (!workflowList.length) {
                return;
            }
            /** @type {module:dynamic-logic.Class}*/
            let dynamicLogic = new DynamicLogic({}, this.view);

            let applyDynamicLogic = (id, conditionGroup) => {
                let name = 'runWorkflow_' + id;

                dynamicLogic.checkConditionGroup(conditionGroup) ?
                    this.view.showHeaderActionItem(name) :
                    this.view.hideHeaderActionItem(name);
            };

            workflowList.forEach(item => {
                let type = item.elementType === 'Button' ?
                    'buttons' :
                    'dropdown';

                let toBeginning = false;

                /** @type {module:views/main~MenuItem} */
                let o = {
                    text: item.label,
                    acl: item.accessRequired === 'edit' ? 'edit' : 'read',
                    name: 'runWorkflow_' + item.id,
                    action: 'runWorkflow',
                    data: {
                        id: item.id,
                        handler: 'advanced:handlers/manual-workflow-action',
                    },
                };

                this.view.addMenuItem(type, o, toBeginning);

                if (item.dynamicLogic) {
                    let conditionGroup = item.dynamicLogic.conditionGroup;

                    applyDynamicLogic(item.id, conditionGroup);
                    this.listenTo(this.view.model, 'sync', () => applyDynamicLogic(item.id, conditionGroup));
                }
            });
        },
    });

    _.extend(Handler.prototype, Backbone.Events);

    return Handler;
});
