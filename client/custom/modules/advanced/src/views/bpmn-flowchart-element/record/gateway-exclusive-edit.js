
define('advanced:views/bpmn-flowchart-element/record/gateway-exclusive-edit',
[
    'advanced:views/bpmn-flowchart-element/record/edit',
    'advanced:views/bpmn-flowchart-element/record/gateway-exclusive-detail'
],
function (Dep, Detail) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            if (!Detail.prototype.isDivergent.call(this)) {
                this.hideField('flowsConditions');
                this.hidePanel('flowsConditions');
                this.hideField('defaultFlowId');
                this.hidePanel('divergent');
            } else {
                this.showPanel('divergent');
            }
        },
    });
});
