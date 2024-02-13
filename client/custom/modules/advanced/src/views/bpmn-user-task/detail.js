
define('advanced:views/bpmn-user-task/detail', ['views/detail'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            if (!this.model.get('resolution')) {
                if (this.getAcl().checkModel(this.model, 'edit')) {
                    this.addMenuItem('buttons', {
                        label: 'Resolve',
                        action: 'showResolveModal',
                        acl: 'edit',
                    });

                    this.listenTo(this.model, 'sync', () => {
                        if (this.model.get('resolution')) {
                            this.removeMenuItem('showResolveModal');
                        }
                    });

                    this.listenTo(this.model, 'change:resolution', () => {
                        if (this.model.get('resolution')) {
                            this.disableMenuItem('showResolveModal');
                        } else {
                            this.enableMenuItem('showResolveModal');
                        }
                    });
                }
            }
        },

        actionShowResolveModal: function () {
            this.createView('modal', 'advanced:views/bpmn-user-task/modals/resolve', {
                model: this.model,
            }, view => {
                view.render();
            });
        },
    });
});
