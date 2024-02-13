
define('advanced:views/bpmn-process/record/edit-quick',
['views/record/edit-small', 'advanced:views/bpmn-process/record/edit'] , function (Dep, Edit) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);
            Edit.prototype.setupFlowchartDependency.call(this);
        },
    });
});
