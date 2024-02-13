
define('advanced:views/bpmn-flowchart-element/fields/message-replied-to', 'views/fields/enum', function (Dep) {

    return Dep.extend({

        fetchEmptyValueAsNull: true,

        setupOptions: function () {
            var list = [''];
            this.translatedOptions = {
                '': this.translate('None'),
            };

            var flowchartCreatedEntitiesData = this.model.flowchartCreatedEntitiesData || {};

            for (var id in flowchartCreatedEntitiesData) {
                var item = flowchartCreatedEntitiesData[id];
                if (item.entityType !== 'Email') continue;

                this.translatedOptions[id] = item.text || id;
                list.push(id);
            }

            this.params.options = list;
        },
    });
});