
define('advanced:views/fields/foreign-enum', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        getValueForDisplay: function () {
            var a = this.name.split('_');

            var link = a[0];
            var field = a[1];

            var foreignScope = this.getMetadata().get('entityDefs.' + this.model.name + '.links.' + link + '.entity');

            return this.getLanguage().translateOption(this.model.get(this.name), field, foreignScope);
        },
    });
});
