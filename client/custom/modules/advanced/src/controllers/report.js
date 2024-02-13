
define('advanced:controllers/report', ['controllers/record'], function (Dep) {

    return Dep.extend({

        create: function (options) {
            options = options || {};

            var hasAttributes = !!options.attributes;

            options.attributes = options.attributes || {};

            if ('type' in options) {
                options.attributes.type = options.type;
            } else if (!options.attributes.type) {
                options.attributes.type = 'Grid';
            }

            if ('entityType' in options) {
                options.attributes.entityType = options.entityType;
            } else {
                if (!hasAttributes && options.attributes.type !== 'JointGrid') {
                    throw new Espo.Exceptions.NotFound();
                }
            }

            if ('categoryId' in options) {
                options.attributes.categoryId = options.categoryId;
            }

            if ('categoryName' in options) {
                options.attributes.categoryName = options.categoryName;
            }

            Dep.prototype.create.call(this, options);
        },

        actionShow: function (options) {
            var id = options.id;

            if (!id) {
                throw new Espo.Exceptions.NotFound("No report id.");
            }

            var createView = model =>{
                var view = this.getViewName('result');

                this.main(view, {
                    scope: this.name,
                    model: model,
                    returnUrl: options.returnUrl,
                    returnDispatchParams: options.returnDispatchParams,
                    params: options,
                });
            };

            var model = options.model;

            if (model && model.id && model.get('type') && model.get('groupBy')) {
                createView(model);

                this.showLoadingNotification();

                model.fetch().then(() => {
                    this.hideLoadingNotification();
                });

                this.listenToOnce(this.baseController, 'action', () => {
                    model.abortLastFetch();
                });

                return;
            }

            this.getModel().then(model =>{
                model.id = id;

                this.showLoadingNotification();

                model.fetch({main: true}).then(() => {
                    createView(model);
                });

                this.listenToOnce(this.baseController, 'action', () => {
                    model.abortLastFetch();
                });
            });
        },
    });
});
