
define('advanced:views/bpmn-process/fields/target-type',
['advanced:views/bpmn-flowchart/fields/entity-type'], function (Dep) {

    return Dep.extend({

        readOnly: true,
    });
});
