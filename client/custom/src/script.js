(function (define) {
    define("custom:views/report/modals/sub-report", ["exports", "advanced:views/report/modals/sub-report",'advanced:report-helper'], function (_exports, Dep,ReportHelper) {
        
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

    define("custom:models/user", ["exports", "models/user"], function (_exports, Dep) {
        Dep.prototype.isManger = function () {
            return this.get('type') === 'manger';
        }
    });

    define("custom:views/site/navbar", ["exports", "views/site/navbar"], function (_exports, Dep) {
        Dep.prototype.getMenuDataList = function () {
            let avatarHtml = this.getHelper().getAvatarHtml(this.getUser().id, 'small', 16, 'avatar-link');

            if (avatarHtml) {
                avatarHtml += ' ';
            }

            /** @type {MenuDataItem[]}*/
            let list = [
                {
                    link: '#User/view/' + this.getUser().id,
                    html: avatarHtml + this.getHelper().escapeString(this.getUser().get('name')),
                },
                {divider: true}
            ];

            if (this.getUser().isAdmin() || this.getUser().isManger()) {
                list.push({
                    link: '#Admin',
                    label: this.getLanguage().translate('Administration'),
                });
            }

            list.push({
                link: '#Preferences',
                label: this.getLanguage().translate('Preferences'),
            });

            if (!this.getConfig().get('actionHistoryDisabled')) {
                list.push({divider: true});

                list.push({
                    action: 'showLastViewed',
                    link: '#LastViewed',
                    label: this.getLanguage().translate('LastViewed', 'scopeNamesPlural'),
                });
            }

            list = list.concat([
                {
                    divider: true
                },
                {
                    link: '#About',
                    label: this.getLanguage().translate('About')
                },
                {
                    action: 'logout',
                    label: this.getLanguage().translate('Log Out')
                },
            ]);

            return list;
        }
    });

    define("custom:controllers/record", ["exports", "controllers/record"], function (_exports, Dep) {
        const _checkAccess = Dep.prototype.checkAccess;
        Dep.prototype.checkAccess = function (action) {
            return this.getUser().isManger() || _checkAccess.call(this, action);
        }
    });

    define("custom:controllers/admin", ["exports", "controllers/admin"], function (_exports, Dep) {
        const _checkAccessGlobal = Dep.prototype.checkAccessGlobal;
        Dep.prototype.checkAccessGlobal = function (action) {
            return this.getUser().isManger() || _checkAccessGlobal.call(this, action);
        }
    });

    define("custom:controllers/role", ["exports", "controllers/role"], function (_exports, Dep) {
        const _checkAccess = Dep.prototype.checkAccess;
        Dep.prototype.checkAccess = function (action) {
            return this.getUser().isManger() || _checkAccess.call(this, action);
        }
    });

    define("custom:controllers/team", ["exports", "controllers/team"], function (_exports, Dep) {
        const _checkAccess = Dep.prototype.checkAccess;
        Dep.prototype.checkAccess = function (action) {
            return this.getUser().isManger() || _checkAccess.call(this, action);
        }
    });

    define("custom:views/admin/index", ["exports", "views/admin/index"], function (_exports, Dep) {
        const _setup = Dep.prototype.setup;
        Dep.prototype.setup = function () {
            _setup.call(this);

            if (!this.getUser().isManger()) {
                return;
            }

            const panelDataList = ['roles', 'teams', 'users'];

            this.panelDataList.forEach((panle) => {
                panle.itemList = panle.itemList.filter((item) => {
                    return panelDataList.indexOf(item.description) !== -1;
                });
            }, this);

            this.panelDataList = this.panelDataList.filter((panle) => {
                return panle.itemList.length > 0;
            });
        }
    });
}).call(window, define)
