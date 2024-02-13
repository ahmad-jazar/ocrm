
define('advanced:dynamic-handlers/report-filter', [], function () {


    var DynamicHandler = function (recordView) {
        this.recordView = recordView;
        this.model = recordView.model;
    }

    _.extend(DynamicHandler.prototype, {

        onChangeEntityType: function (model, value, o) {
            if (!o.ui) return;

            this.model.set({
                reportId: null,
                reportName: null
            });
        }
    });

    return DynamicHandler;

});
