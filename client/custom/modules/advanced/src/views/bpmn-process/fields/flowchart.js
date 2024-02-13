
define('advanced:views/bpmn-process/fields/flowchart', 'views/fields/link', function (Dep) {

    return Dep.extend({

        selectPrimaryFilterName: 'isManuallyStartable',

        createDisabled: true,

        select: function (model) {
            this.model.set('targetType', model.get('targetType'), {ui: true});
            this.model.set('flowchartElementsDataHash', model.get('elementsDataHash'));
            this.model.set('startElementIdList', model.get('eventStartAllIdList'));
            Dep.prototype.select.call(this, model);
        },

    });
});