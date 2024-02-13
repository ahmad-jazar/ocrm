
define('advanced:views/bpmn-flowchart-element/record/edit',
['views/record/edit-small'], function (Dep) {

    return Dep.extend({

        setup: function () {
            this.dynamicLogicDefs = this.options.dynamicLogicDefs;
            Dep.prototype.setup.call(this);
        },
    });
});
