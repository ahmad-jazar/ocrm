
define('advanced:views/fields/foreign-link', ['views/fields/link'], function (Dep) {

    return Dep.extend({

        setup: function () {
            var a = this.name.split('_');
            var link = a[0];
            var field = a[1];

            var linkEntityType = this.getMetadata().get(['entityDefs', this.model.name, 'links', link, 'entity']);
            this.foreignScope = this.getMetadata().get(['entityDefs', linkEntityType, 'links', field, 'entity']);

            Dep.prototype.setup.call(this)
        }
    });
});
