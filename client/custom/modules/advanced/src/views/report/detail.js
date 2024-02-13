
define('advanced:views/report/detail', 'views/detail', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            var version = this.getConfig().get('version') || '';
            var arr = version.split('.');

            if (
                version === 'dev' || arr.length > 2 && parseInt(arr[0]) * 100 + parseInt(arr[1]) >= 506 ||
                version === '@@version'
            ) {
                var iconHtml;
                if (~['Grid', 'JointGrid'].indexOf(this.model.get('type'))) {
                    iconHtml = '<span class="fas fa-chart-bar"></span> ';
                } else {
                    iconHtml = '';
                }

                this.addMenuItem('buttons', {
                    action: 'show',
                    link: '#Report/show/' + this.model.id,
                    html: iconHtml + this.translate('Results View', 'labels', 'Report'),
                });
            }
        },

        actionShow: function () {
            var options = {
                id: this.model.id,
                model: this.model
            };

            var rootUrl = this.options.rootUrl || this.options.params.rootUrl;
            if (rootUrl) {
                options.rootUrl = rootUrl;
            }

            this.getRouter().navigate('#Report/show/' + this.model.id, {trigger: false});
            this.getRouter().dispatch('Report', 'show', options);
        },

    });
});
