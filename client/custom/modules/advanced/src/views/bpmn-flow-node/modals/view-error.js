
define('advanced:views/bpmn-flow-node/modals/view-error', ['views/modal', 'model'], function (Dep, Model) {
    /**
     * @module module:advanced_views/bpmn-flow-node/modals/view-error
     */

    /**
     * @class
     * @name Class
     * @memberOf module:advanced_views/bpmn-flow-node/modals/view-error
     * @extends module:views/modal.Class
     */
    return Dep.extend(/** @lends module:advanced_views/bpmn-flow-node/modals/view-error.Class# */{

        templateContent: `<div class="record no-side-margin">{{{record}}}</div>`,

        className: 'dialog dialog-record',
        backdrop: true,

        setup: function () {
            this.headerText = this.translate('View Error', 'labels', 'BpmnProcess');

            /** @type {module:model.Class} */
            let model = new Model();
            model.name = 'Dummy';

            model.set({
                code: this.options.nodeData.code || null,
                message: this.options.nodeData.message || null,
            })

            this.createView('record', 'views/record/detail', {
                readOnly: true,
                bottomView: null,
                sideView: null,
                buttonsDisabled: true,
                scope: 'Dummy',
                model: model,
                el: this.getSelector() + ' .record',
                detailLayout: [
                    {
                        rows: [
                            [
                                {
                                    name: 'code',
                                    view: 'views/fields/varchar',
                                    customLabel: this.translate('errorCode', 'fields', 'BpmnFlowchartElement'),
                                },
                                false
                            ],
                            [
                                {
                                    name: 'message',
                                    view: 'views/fields/varchar',
                                    customLabel: this.translate('Error Message', 'labels', 'BpmnFlowchartElement'),
                                }
                            ]
                        ]
                    }
                ],
            });
        },
    });
});
