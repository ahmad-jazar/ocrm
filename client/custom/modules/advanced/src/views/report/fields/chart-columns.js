
define('advanced:views/report/fields/chart-columns', ['advanced:views/report/fields/columns'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.params.options = Espo.Utils.clone(this.model.get('columns') || []);
        },
    });
});
