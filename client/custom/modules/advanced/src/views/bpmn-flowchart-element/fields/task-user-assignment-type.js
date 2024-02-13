
define('advanced:views/bpmn-flowchart-element/fields/task-user-assignment-type',
[
    'views/fields/enum',
    'advanced:views/bpmn-flowchart-element/fields/task-send-message-from'
],
function (Dep, From) {

    return Dep.extend({

        setupOptions: function () {
            Dep.prototype.setupOptions.call(this);

            this.params.options = Espo.Utils.clone(this.params.options);

            var linkOptionList = From.prototype.getLinkOptionList.call(this, true, true);

            linkOptionList.forEach(item => {
                this.params.options.push(item);
            });

            this.translateOptions(this);
        },

        translateOptions: function () {
            this.translatedOptions = {};
            var entityType = this.model.targetEntityType;

            this.params.options.forEach(item => {
                if (item.indexOf('link:') === 0) {

                    var link = item.substring(5);

                    if (~link.indexOf('.')) {
                        var arr = link.split('.');
                        link = arr[0];

                        var subLink = arr[1];

                        if (subLink === 'followers') {
                            this.translatedOptions[item] =
                                this.translate('Related', 'labels', 'Workflow') + ': ' +
                                this.translate(link, 'links', entityType) +
                                '.' + this.translate('Followers');

                            return;
                        }

                        var relatedEntityType = this.getMetadata()
                            .get(['entityDefs', entityType, 'links', link, 'entity']);

                        this.translatedOptions[item] = this.translate('Related', 'labels', 'Workflow') +
                            ': ' + this.translate(link, 'links', entityType) +
                            '.' + this.translate(subLink, 'links', relatedEntityType);

                        return;
                    }

                    this.translatedOptions[item] = this.translate('Related', 'labels', 'Workflow') + ': ' +
                        this.translate(link, 'links', entityType);

                    return;
                }

                this.translatedOptions[item] = this.getLanguage()
                    .translateOption(item, 'assignmentType', 'BpmnFlowchartElement')
            });
        },
    });
});
