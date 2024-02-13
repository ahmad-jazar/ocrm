
define('advanced:views/bpmn-process/fields/start-element-id', 'views/fields/enum', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            if (this.model.has('startElementIdList')) {
                this.conrolElementIdList();
            }

            this.listenTo(this.model, 'change:startElementIdList', function () {
                this.conrolElementIdList();
            }, this);
        },

        conrolElementIdList: function () {
            var flowchartElementsDataHash = this.model.get('flowchartElementsDataHash') || {};

            var startElementIdList = this.model.get('startElementIdList') || [];
            this.translatedOptions = {};
            startElementIdList.forEach(function (id) {
                if (!(id in flowchartElementsDataHash)) return;
                this.translatedOptions[id] = flowchartElementsDataHash[id].text || id;

                var label = flowchartElementsDataHash[id].text || id;

                label = this.translate(flowchartElementsDataHash[id].type, 'elements', 'BpmnFlowchart') + ': ' + label;

                this.translatedOptions[id] = label;
            }, this);
        },

    });
});
