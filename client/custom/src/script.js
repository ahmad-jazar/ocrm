(function (define) {
    define("custom:views/report/modals/sub-report", ["exports", "advanced:views/report/modals/sub-report", 'advanced:report-helper'], function (_exports, Dep, ReportHelper) {

        Dep.prototype.setup = function () {
            this.buttonList = [
                {
                    name: 'cancel',
                    label: 'Close',
                }
            ];

            let result = this.options.result;

            let reportHelper = new ReportHelper(
                this.getMetadata(),
                this.getLanguage(),
                this.getDateTime(),
                this.getConfig(),
                this.getPreferences()
            );

            let groupValue = this.options.groupValue;

            let name = this.options.reportName;

            if (!name && this.model) {
                name = this.model.get('name');
            }

            let groupIndex = this.options.groupIndex || 0;

            this.headerHtml = Handlebars.Utils.escapeExpression(name);

            if (result.groupByList.length) {
                this.headerHtml += ': ' + reportHelper.formatGroup(result.groupByList[groupIndex], groupValue, result);
            }

            if (this.options.groupValue2 !== undefined) {
                this.headerHtml += ', ' +
                    reportHelper.formatGroup(result.groupByList[1], this.options.groupValue2, result);
            }

            if (this.options.result.isJoint && this.options.column) {
                let label = this.options.result.columnSubReportLabelMap[this.options.column];

                this.headerHtml += ', ' + Handlebars.Utils.escapeExpression(label);
            }

            this.header = this.headerHtml;

            let reportId = this.options.reportId || this.model.id;

            this.wait(true);

            this.createView('list', 'advanced:views/record/list-for-report', {
                el: this.options.el + ' .list-container',
                collection: this.collection,
                type: 'listSmall',
                reportId: reportId,
                groupValue: groupValue,
                groupIndex: groupIndex,
                groupValue2: this.options.groupValue2,
                skipBuildRows: true,
            }, view => {
                view.getSelectAttributeList(selectAttributeList => {
                    if (selectAttributeList) {
                        selectAttributeList.push('name');
                        this.collection.data.select = selectAttributeList.join(',');
                    }

                    this.listenToOnce(view, 'after:build-rows', () => {
                        this.wait(false);
                    });

                    this.collection.fetch();
                });
            });
        }
    });

    define("custom:views/site/master", ["exports", "views/site/master"], function (_exports, Dep) {

        const _afterRender = Dep.prototype.afterRender;
        Dep.prototype.afterRender = function () {
            _afterRender.call(this);

            if (!this.getUser().get('changePasswordFlag')) {
                return;
            }

            this.createView('dialog', 'custom:views/modals/change-password-required', {}, (view) => {
                view.render();
            });
        }
    });

    define("custom:views/fields/password", ["exports", "views/fields/password"], function (_exports, Dep) {
        const _validateConfirm = Dep.prototype.validateConfirm;

        Dep.prototype.validateConfirm = function () {

            if (this.model.has('currentPassword') && this.name === 'password') {
                if (this.model.get('password') === this.model.get('currentPassword')) {
                    let msg = this.translate('fieldBadCurrentPassword', 'messages')
                        .replace('{field}', this.getLabelText());

                    // this.showValidationMessage(msg);
                    this.notify(msg, 'error');
                    return true;
                }
            }

            return _validateConfirm.call(this);
        }
    });

}).call(window, define)
