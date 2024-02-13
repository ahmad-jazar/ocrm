
define('advanced:views/workflow-log-record/fields/target', ['views/fields/link-parent'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.foreignScopeList = this.getMetadata().getScopeObjectList().sort((v1, v2) => {
                 return this.translate(v1, 'scopeNames').localeCompare(this.translate(v2, 'scopeNames'));
            });
        },
    });
});
