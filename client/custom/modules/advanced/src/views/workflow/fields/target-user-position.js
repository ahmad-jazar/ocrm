
define('advanced:views/workflow/fields/target-user-position', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.translatedOptions = {
                '': '--' + this.translate('All') + '--',
            };

            this.params.options = [''];

            if (this.model.get('targetUserPosition') && this.model.get('targetTeamId')) {
                this.params.options.push(this.model.get('targetUserPosition'));
            }

            this.loadRoleList(() => {
                if (this.mode === 'edit') {
                    if (this.isRendered()) {
                        this.render();
                    }
                }
            });

            this.listenTo(this.model, 'change:targetTeamId', () => {
                this.loadRoleList(() => {
                    this.render();
                });
            });
        },

        loadRoleList: function (callback, context) {
            var teamId = this.model.get('targetTeamId');

            if (!teamId) {
                this.params.options = [''];
            }

            this.getModelFactory().create('Team', team => {
                team.id = teamId;

                this.listenToOnce(team, 'sync', () => {
                    this.params.options = team.get('positionList') || [];
                    this.params.options.unshift('');

                    callback.call(context);
                });

                team.fetch();
            });
        },
    });
});
