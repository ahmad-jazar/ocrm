
define('advanced:views/report-panel/fields/entity-type', ['views/fields/entity-type'], function (Dep) {

    return Dep.extend({

        setupOptions: function () {
            var scopes = this.scopesMetadataDefs = this.getMetadata().get('scopes');

            this.params.options = Object.keys(scopes).filter(scope => {
                if (this.checkAvailability(scope)) {
                    return true;
                }
            });

            this.params.options.push('Team');

            this.params.options.sort((v1, v2) => {
                 return this.translate(v1, 'scopeNames').localeCompare(this.translate(v2, 'scopeNames'));
            });

            this.params.options.unshift('');
        }
    });
});
