
define('advanced:start-process-action-handler', ['action-handler'], function (Dep) {

    return Dep.extend({

        init: function () {
            if (~(this.view.getHelper().getAppParam('flowchartEntityTypeList') || []).indexOf(this.view.model.entityType)) {
                this.view.showHeaderActionItem('startProcessGlobal');
            }
        },

        actionStartProcessGlobal: function () {
            var viewName = 'views/modals/select-records';
            this.view.createView('startProcessDialog', viewName, {
                scope: 'BpmnFlowchart',
                primaryFilterName: 'isManuallyStartable',
                createButton: false,
                filters: {
                    targetType: {
                        type: 'in',
                        value: [this.view.model.entityType],
                        data: {
                            type: 'anyOf',
                            valueList: [this.view.model.entityType]
                        },
                    },
                },
            }).then(
                function (view) {
                    view.render();

                    this.view.listenToOnce(view, 'select', function (m) {
                        var attributes = {
                            flowchartName: m.get('name'),
                            flowchartId: m.id,
                            targetType: this.view.model.entityType,
                            targetName: this.view.model.get('name'),
                            targetId: this.view.model.id,
                            startElementIdList: m.get('eventStartAllIdList'),
                            flowchartElementsDataHash: m.get('elementsDataHash'),
                        };

                        var router = this.view.getRouter();

                        var returnUrl = router.getCurrentUrl();
                        router.navigate('#BpmnProcess/create', {trigger: false});
                        router.dispatch('BpmnProcess', 'create', {
                            attributes: attributes,
                            returnUrl: returnUrl,
                        });

                    }.bind(this));

                }.bind(this)
            );
        },

    });
});
