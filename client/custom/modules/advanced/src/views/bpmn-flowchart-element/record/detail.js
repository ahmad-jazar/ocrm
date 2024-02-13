
define('advanced:views/bpmn-flowchart-element/record/detail', ['views/record/detail-small'], function (Dep) {

    return Dep.extend({

        setup: function () {
            this.dynamicLogicDefs = this.options.dynamicLogicDefs;

            Dep.prototype.setup.call(this);

            if (!this.model.get('description')) {
                this.hideField('description');
                this.hidePanel('description');
            }

            if (!this.model.get('text')) {
                this.hideField('text');
                this.hidePanel('text');
            }

            var flowchartData = {
                list: this.model.get('dataList'),
                createdEntitiesData: this.model.flowchartCreatedEntitiesData,
            };

            this.model.set('flowchartData', flowchartData);
        },
    });
});
