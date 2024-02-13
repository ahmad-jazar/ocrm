
define('advanced:views/bpmn-process/fields/target',
['views/fields/link-parent'], function (Dep) {

    return Dep.extend({

        setup: function () {
            this.params.entityList = ['BpmnProcess'];

            Dep.prototype.setup.call(this);

            if (this.model.isNew() && this.mode !== 'search') {
                this.setupForeignScope();

                this.listenTo(this.model, 'change:targetType', () => {
                    this.setupForeignScope();
                    this.reRender();
                });
            } else {
                var scopes = this.getMetadata().get('scopes');
                var entityListToIgnore = this.getMetadata().get('entityDefs.Workflow.entityListToIgnore') || [];

                this.foreignScopeList = Object.keys(scopes)
                    .filter(scope => {
                        if (~entityListToIgnore.indexOf(scope)) {
                            return;
                        }

                        var defs = scopes[scope];

                        return (defs.entity && (defs.tab || defs.object || defs.workflow));
                    })
                    .sort((v1, v2) => {
                        return this.translate(v1, 'scopeNamesPlural')
                            .localeCompare(this.translate(v2, 'scopeNamesPlural'));
                    });
            }
        },

        setupForeignScope: function () {
            if (this.model.get('targetType')) {
                this.foreignScopeList = [this.model.get('targetType')];
            } else {
                this.foreignScopeList = ['BpmnProcess'];
            }
        },
    });
});
