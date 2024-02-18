define('custom:views/seminar-registrations-follow-up/detail', ['views/detail'], function (Dep) {
    return Dep.extend({
        actionCreateRelated(data) {
            data = data || {};

            let link = data.link;
            let scope = this.model.defs['links'][link].entity;
            let foreignLink = this.model.defs['links'][link].foreign;

            let attributes = {};

            if (
                this.relatedAttributeFunctions[link] &&
                typeof this.relatedAttributeFunctions[link] === 'function'
            ) {
                attributes = _.extend(this.relatedAttributeFunctions[link].call(this), attributes);
            }

            let attributeMap = this.getMetadata()
                    .get(['clientDefs', this.scope, 'relationshipPanels', link, 'createAttributeMap']) ||
                this.relatedAttributeMap[link] || {};

            Object.keys(attributeMap)
                .forEach(attr => {
                    attributes[attributeMap[attr]] = this.model.get(attr);
                });

            Espo.Ui.notify(' ... ');

            let handler = this.getMetadata()
                .get(['clientDefs', this.scope, 'relationshipPanels', link, 'createHandler']);

            new Promise(resolve => {
                if (!handler) {
                    resolve({});

                    return;
                }

                Espo.loader.requirePromise(handler)
                    .then(Handler => new Handler(this.getHelper()))
                    .then(handler => {
                        handler.getAttributes(this.model)
                            .then(attributes => resolve(attributes));
                    });
            }).then(additionalAttributes => {
                attributes = {...attributes, ...additionalAttributes};

                let viewName = this.getMetadata()
                    .get(['clientDefs', scope, 'modalViews', 'edit']) || 'views/modals/edit';

                this.createView('quickCreate', viewName, {
                    scope: scope,
                    relate: {
                        model: this.model,
                        link: foreignLink,
                    },
                    attributes: attributes,
                }, view => {
                    view.render();
                    view.notify(false);

                    this.listenToOnce(view, 'after:save', () => {
                        if (data.fromSelectRelated) {
                            setTimeout(() => this.clearView('dialogSelectRelated'), 25);
                        }

                        this.updateRelationshipPanel(link);

                        this.model.trigger('after:relate', view.model);
                        this.model.trigger('after:relate:' + link);
                    });
                });
            });
        }
    });
});
