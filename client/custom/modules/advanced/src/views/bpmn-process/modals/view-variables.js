
define('advanced:views/bpmn-process/modals/view-variables', ['views/modal'], function (Dep) {

    return Dep.extend({

        templateContent: `<div class="record no-side-margin">{{{record}}}</div>`,

        className: 'dialog dialog-record',
        backdrop: true,

        setup: function () {
            this.headerText = this.translate('variables', 'fields', 'BpmnProcess');

            this.createView('record', 'views/record/detail', {
                readOnly: true,
                isWide: true,
                bottomView: null,
                sideView: null,
                buttonsDisabled: true,
                scope: this.model.entityType,
                model: this.model,
                el: this.getSelector() + ' .record',
                detailLayout: [
                    {
                        rows: [
                            [
                                {
                                    name: 'variables',
                                    noLabel: true,
                                }
                            ]
                        ]
                    }
                ],
            });
        },
    });
});
