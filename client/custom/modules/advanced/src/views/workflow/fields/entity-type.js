
define('advanced:views/workflow/fields/entity-type', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        entityListToIgnore: [],

        setup: function () {
            var scopes = this.getMetadata().get('scopes');
            var entityListToIgnore = this.getMetadata().get('entityDefs.Workflow.entityListToIgnore') || [];
            var forcedSupportEntityList = this.getMetadata().get('entityDefs.Workflow.forcedSupportEntityList') || [];

            this.params.options = Object.keys(scopes)
                .filter(scope => {
                    if (~entityListToIgnore.indexOf(scope)) {
                        return;
                    }

                    var defs = scopes[scope];

                    return (defs.entity && (defs.tab || defs.object || defs.workflow ||
                        ~forcedSupportEntityList.indexOf(scope)));
                })
                .sort((v1, v2) => {
                    return this.translate(v1, 'scopeNamesPlural')
                        .localeCompare(this.translate(v2, 'scopeNamesPlural'));
                });

            this.params.options.unshift('');

            this.params.translation = 'Global.scopeNames';

            Dep.prototype.setup.call(this);
        },
    });
});
