
define('advanced:views/bpmn-process/fields/flowchart-visualization',
['advanced:views/bpmn-flowchart/fields/flowchart'], function (Dep) {

    return Dep.extend({

        dataAttribute: 'flowchartData',
    });
});
