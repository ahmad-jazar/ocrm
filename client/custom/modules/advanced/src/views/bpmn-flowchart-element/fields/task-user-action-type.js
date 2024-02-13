
define('advanced:views/bpmn-flowchart-element/fields/task-user-action-type', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        setupOptions: function () {
            Dep.prototype.setupOptions.call(this);
            var list = this.getMetadata().get(['entityDefs', 'BpmnUserTask', 'fields', 'actionType', 'options']) || [];
            this.params.options = Espo.Utils.clone(list);
        },
    });
});
