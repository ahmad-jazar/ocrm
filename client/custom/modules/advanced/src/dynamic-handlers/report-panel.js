
define('advanced:dynamic-handlers/report-panel', [], function () {


    var DynamicHandler = function (recordView) {
        this.recordView = recordView;
        this.model = recordView.model;
    }

    _.extend(DynamicHandler.prototype, {

        init: function () {
            this.controlReportType();
            this.controlReportId();
            this.controlEntityType();
            this.controlType();
            this.controlTotal();
        },

        onChange: function () {
            this.controlTotal();
        },

        onChangeEntityType: function (model, value, o) {
            if (!o.ui) return;

            this.model.set({
                reportId: null,
                reportName: null,
                dynamicLogicVisible: null
            });

            this.controlEntityType();
        },

        onChangeReportId: function (model, value, o) {
            this.controlReportId();
        },

        onChangeReportType: function (model, value, o) {
            this.controlReportType();
        },

        onChangeType: function (model, value, o) {
            this.controlType();
        },

        controlEntityType: function () {
            if (!this.model.get('entityType')) {
                this.recordView.hideField('dynamicLogicVisible');
            } else {
                this.recordView.showField('dynamicLogicVisible');
            }
        },

        controlReportType: function () {
            if (this.model.get('reportType') === 'Grid') {
                this.recordView.showField('displayTotal');
                this.recordView.showField('column');
            } else if (this.model.get('reportType') === 'JointGrid') {
                this.recordView.showField('displayTotal');
                this.recordView.hideField('column');
            } else {
                this.recordView.hideField('displayTotal');
                this.recordView.hideField('column');
            }
        },

        controlReportId: function () {
            if (this.model.get('reportId')) {
                this.recordView.showField('reportType');
            } else {
                this.recordView.hideField('reportType');
            }
        },

        controlType: function () {
            if (this.model.get('type') === 'bottom') {
                this.recordView.showField('order');
            } else {
                this.recordView.hideField('order');
            }
        },

        controlTotal: function () {
            if (
                this.model.get('reportId') &&
                (this.model.get('displayTotal') || this.model.get('displayOnlyTotal'))
            ) {
                this.recordView.showField('useSiMultiplier');
            } else {
                this.recordView.hideField('useSiMultiplier');
            }
        },
    });

    return DynamicHandler;

});
