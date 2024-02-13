
define('advanced:views/report/fields/entity-type', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        setup: function () {
            var scopes = this.getMetadata().get('scopes');
            var entityListAllowed = this.getMetadata().get('entityDefs.Report.entityListAllowed') || [];
            var entityListToIgnore = this.getMetadata().get('entityDefs.Report.entityListToIgnore') || [];

            this.params.options = Object.keys(scopes).filter(scope => {
                if (~entityListToIgnore.indexOf(scope)) {
                    return;
                }

                var defs = scopes[scope];

                return (defs.entity && (defs.tab || defs.object) || ~entityListAllowed.indexOf(scope));
            }).sort((v1, v2) => {
                 return this.translate(v1, 'scopeNamesPlural').localeCompare(this.translate(v2, 'scopeNamesPlural'));
            });

            this.params.translation = 'Global.scopeNames';

            Dep.prototype.setup.call(this);
        },
    });
});
