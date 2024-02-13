
define('advanced:views/bpmn-process/record/panels/flow-nodes', ['views/record/panels/relationship'], function (Dep) {

    return Dep.extend({

        fetchOnModelAfterRelate: true,
    });
});
