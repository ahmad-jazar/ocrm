
define('advanced:views/bpmn-flowchart-element/fields/target-id-expression', 'views/fields/formula', function (Dep) {

    return Dep.extend({

        height: 15,

        setup: function () {
            this.params.targetEntityType = this.model.targetEntityType;

            Dep.prototype.setup.call(this);
        },

    });
});
