
define('advanced:views/bpmn-flowchart-element/fields/default-flow-id', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        data: function () {
            var data = Dep.prototype.data.call(this);
            data.isNotEmpty = true;

            return data;
        },

        setupOptions: function () {
            Dep.prototype.setupOptions.call(this);

            var flowchartDataList = this.model.dataHelper.getAllDataList();
            var id = this.model.get('id');

            this.translatedOptions = {};

            var flowIdList = [];

            flowchartDataList.forEach(function (item) {
                if (item.type !== 'flow') {
                    return;
                }

                if (item.startId === id && item.endId) {
                    var endItem = this.getElementData(item.endId);

                    if (!endItem) {
                        return;
                    }

                    flowIdList.push(item.id);

                    this.translatedOptions[item.id] = this.translate(endItem.type, 'elements', 'BpmnFlowchart') +
                        ': ' + (endItem.text || endItem.id);
                }
            }, this);

            this.translatedOptions[''] = this.translate('None');
            this.params.options = flowIdList;
            this.params.options.unshift('');
        },

        getValueForDisplay: function () {
            var value = Dep.prototype.getValueForDisplay.call(this);

            if (!value) {
                value = '';
            }

            return value;
        },

        getElementData: function (id) {
            return this.model.dataHelper.getElementData(id);
        },

        fetch: function () {
            var data = Dep.prototype.fetch.call(this);

            if (data[this.name] === '') {
                data[this.name] = null;
            }

            return data;
        },
    });
});
