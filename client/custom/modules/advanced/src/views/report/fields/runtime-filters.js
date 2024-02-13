
define('advanced:views/report/fields/runtime-filters',
['views/fields/multi-enum', 'advanced:views/report/fields/filters'], function (Dep, Filters) {

    return Dep.extend({

        setupOptions: function () {
            Dep.prototype.setupOptions.call(this);

            this.params.options = Filters.prototype.getFilterList.call(this);

            Filters.prototype.setupTranslatedOptions.call(this);
        },
    });
});
