define('iframe:views/iframe', ['view'], function (Dep) {
    return Dep.extend({

        template: 'iframe:iframe',

        data() {
            const clientDefs = this.getMetadata().get(['clientDefs', this.name]) || {};

            if (clientDefs) {
                return {
                    header: clientDefs.header || '',
                    url: clientDefs.url || ''
                };
            }

            return {
                header: '',
                url: ''
            };
        },

        afterRender() {
            Dep.prototype.afterRender.call(this);

            const $ifream = this.$el.find('iframe');
            $ifream.css('height', 'calc(100vh - 50px)');
        }
    });
});
