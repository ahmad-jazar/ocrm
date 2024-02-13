
define('advanced:views/bpmn-flowchart-element/fields/task-script-formula', ['views/fields/formula'], function (Dep) {

    return Dep.extend({

        setup: function () {
            this.params.targetEntityType = this.model.targetEntityType;
            Dep.prototype.setup.call(this);
        },
    });
});
