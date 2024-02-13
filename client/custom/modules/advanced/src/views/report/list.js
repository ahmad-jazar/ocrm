
define('advanced:views/report/list', 'views/list-with-categories', function (Dep) {

    return Dep.extend({

        createButton: false,

        quickCreate: false,

        currentCategoryId: null,

        currentCategoryName: '',

        categoryScope: 'ReportCategory',

        categoryField: 'category',

        categoryFilterType: 'inCategory',

        getCreateAttributes: function () {
            return {
                categoryId: this.currentCategoryId,
                categoryName: this.currentCategoryName
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.addMenuItem('buttons', {
                action: 'create',
                html: '<span class="fas fa-plus fa-sm"></span> ' + this.translate('Create ' +  this.scope,  'labels', this.scope),
                style: 'default',
                acl: 'create',
                aclScope: 'Report',
            }, true);
        },

        actionCreate: function () {
            this.createView('createModal', 'advanced:views/report/modals/create', {}, function (view) {
                view.render();

                this.listenToOnce(view, 'create', function (data) {
                    view.close();
                    this.getRouter().dispatch('Report', 'create', {
                        entityType: data.entityType,
                        type: data.type,
                        categoryId: this.currentCategoryId,
                        categoryName: this.currentCategoryName,
                        returnUrl: this.lastUrl || '#' + this.scope,
                        returnDispatchParams: {
                            controller: this.scope,
                            action: null,
                            options: {
                                isReturn: true
                            }
                        }
                    });
                    this.getRouter().navigate('#Report/create/entityType=' + data.entityType + '&type=' + data.type, {trigger: false});
                }, this);
            });
        }
    });
});
