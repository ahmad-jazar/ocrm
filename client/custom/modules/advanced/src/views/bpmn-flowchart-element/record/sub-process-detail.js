
define(
    'advanced:views/bpmn-flowchart-element/record/sub-process-detail',
    ['advanced:views/bpmn-flowchart-element/record/detail'],
    function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);
        },
    });
});
