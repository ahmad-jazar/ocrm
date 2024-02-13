
define('advanced:views/bpmn-flowchart-element/record/gateway-exclusive-detail',
['advanced:views/bpmn-flowchart-element/record/detail'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            if (!this.isDivergent()) {
                this.hideField('flowsConditions');
                this.hidePanel('flowsConditions');
                this.hideField('defaultFlowId');
                this.hidePanel('divergent');
            } else {
                this.showPanel('divergent');
            }
        },

        isConvergent: function () {
            var flowchartDataList = this.model.dataHelper.getAllDataList();
            var id = this.model.id;

            var count = 0;

            flowchartDataList.forEach(item => {
                if (item.type === 'flow' && item.endId === id) {
                    count++;
                }
            });

            return count > 1;
        },

        isDivergent: function () {
            var flowchartDataList = this.model.dataHelper.getAllDataList();
            var id = this.model.id;

            var count = 0;

            flowchartDataList.forEach(item => {
                if (item.type === 'flow' && item.startId === id) {
                    count++;
                }
            });

            return count > 1;
        },
    });
});
