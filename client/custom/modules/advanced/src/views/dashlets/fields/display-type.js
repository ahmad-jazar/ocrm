
define('advanced:views/dashlets/fields/display-type', 'views/fields/enum', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.reportTypeField = 'type';
            this.displayOnlyTotalField = 'displayOnlyCount';

            if (this.model.entityType === 'ReportPanel') {
                this.reportTypeField = 'reportType';
                this.displayOnlyTotalField = 'displayOnlyTotal';
            }

            this.controlOptions();

            this.listenTo(this.model, 'change:' + this.reportTypeField, function () {
                this.controlOptions();

                if (this.model.entityType === 'ReportPanel' && this.model.isNew()) {
                    var reportType = this.model.get('reportType');

                    var displayType = '';

                    if (reportType === 'Grid' || reportType === 'JointGrid') {
                        displayType = 'Chart';
                    }
                    else if (reportType === 'List') {
                        displayType = 'List';
                    }

                    this.model.set('displayType', displayType);
                }
            }, this);
        },

        fetch: function () {
            var data = Dep.prototype.fetch.call(this);

            var value = data[this.name];

            if (value === 'List') {
                data.displayTotal = false;
                data[this.displayOnlyTotalField] = false;
            }

            if (value === 'Chart') {
                data.displayTotal = false;
                data[this.displayOnlyTotalField] = false;
            }

            if (value === 'Chart-Total') {
                data.displayTotal = true;
                data[this.displayOnlyTotalField] = false;
            }

            if (value === 'Total') {
                data.displayTotal = true;
                data[this.displayOnlyTotalField] = true;
            }

            return data;
        },

        controlOptions: function () {
            var type = this.model.get(this.reportTypeField);

            if (type === 'List') {
                this.setOptionList([
                    'List',
                    'Total',
                ]);

                return;
            }

            if (type === 'Grid' || type === 'JointGrid') {
                this.setOptionList([
                    'Chart',
                    'Chart-Total',
                    'Total',
                    'Table',
                ]);

                return;
            }

            this.setOptionList(['']);
        },

    });
});
