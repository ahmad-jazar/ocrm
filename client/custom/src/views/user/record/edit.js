define('custom:views/user/record/edit', ['views/user/record/edit', 'views/record/edit'], function (Dep, Edit) {

    return Dep.extend({

        setup() {
            Edit.prototype.setup.call(this);

            this.setupNonAdminFieldsAccess();

            if (this.model.id === this.getUser().id) {
                this.listenTo(this.model, 'after:save', () => {
                    this.getUser().set(this.model.getClonedAttributes());
                });
            }

            this.hideField('sendAccessInfo');

            this.passwordInfoMessage = this.getPasswordSendingMessage();

            if (!this.passwordInfoMessage) {
                this.hideField('passwordInfo');
            }

            let passwordChanged = false;

            this.listenToOnce(this.model, 'change:password', () => {
                passwordChanged = true;

                if (this.model.isNew()) {
                    this.controlSendAccessInfoFieldForNew();

                    return;
                }

                this.controlSendAccessInfoField();
            });

            this.listenTo(this.model, 'change', (model) => {
                if (!this.model.isNew() && !passwordChanged) {
                    return;
                }

                if (
                    !model.hasChanged('emailAddress') &&
                    !model.hasChanged('portalsIds') &&
                    !model.hasChanged('password')
                ) {
                    return;
                }

                if (this.model.isNew()) {
                    this.controlSendAccessInfoFieldForNew();

                    return;
                }

                this.controlSendAccessInfoField();
            });

            this.setupFieldAppearance(this);

            this.hideField('passwordPreview');

            this.listenTo(this.model, 'change:passwordPreview', (model, value) => {
                value = value || '';

                if (value.length) {
                    this.showField('passwordPreview');
                } else {
                    this.hideField('passwordPreview');
                }
            });


            this.listenTo(this.model, 'after:save', () => {
                this.model.unset('password', {silent: true});
                this.model.unset('passwordConfirm', {silent: true});
            });
        },

        setupFieldAppearance() {
            this.controlFieldAppearance();

            this.listenTo(this.model, 'change', () => {
                this.controlFieldAppearance();
            });
        },

        setupNonAdminFieldsAccess() {
            if (this.getUser().isAdmin() || this.getUser().isManger()) {
                return;
            }

            let nonAdminReadOnlyFieldList = [
                'userName',
                'isActive',
                'teams',
                'roles',
                'password',
                'portals',
                'portalRoles',
                'contact',
                'accounts',
                'type',
                'emailAddress',
            ];

            nonAdminReadOnlyFieldList = nonAdminReadOnlyFieldList.filter(item => {
                if (!this.model.hasField(item)) {
                    return true;
                }

                const aclDefs = /** @type {Object.<string, *>|null} */
                    this.getMetadata().get(['entityAcl', 'User', 'fields', item]);

                if (!aclDefs) {
                    return true;
                }

                if (aclDefs.nonAdminReadOnly) {
                    return true;
                }

                return false;
            });

            nonAdminReadOnlyFieldList.forEach((field) => {
                this.setFieldReadOnly(field, true);
            });

            if (!this.getAcl().checkScope('Team')) {
                this.setFieldReadOnly('defaultTeam', true);
            }

            this.hideField('layoutSet', true);
        },

        controlFieldAppearance() {
            if (this.model.get('type') === 'portal') {
                this.hideField('roles');
                this.hideField('teams');
                this.hideField('defaultTeam');
                this.showField('portals');
                this.showField('portalRoles');
                this.showField('contact');
                this.showField('accounts');
                this.showPanel('portal');
                this.hideField('title');
            } else {
                this.showField('roles');
                this.showField('teams');
                this.showField('defaultTeam');
                this.hideField('portals');
                this.hideField('portalRoles');
                this.hideField('contact');
                this.hideField('accounts');
                this.hidePanel('portal');

                if (this.model.get('type') === 'api') {
                    this.hideField('title');
                    this.hideField('emailAddress');
                    this.hideField('phoneNumber');
                    this.hideField('name');
                    this.hideField('gender');

                    if (this.model.get('authMethod') === 'Hmac') {
                        this.showField('secretKey');
                    } else {
                        this.hideField('secretKey');
                    }

                } else {
                    this.showField('title');
                }
            }

            if (this.model.id === this.getUser().id) {
                this.setFieldReadOnly('type');
            } else {

                if (this.model.get('type') === 'admin' || this.model.get('type') === 'regular' || this.model.get('type') === 'manger') {
                    this.setFieldNotReadOnly('type');
                    if (this.getUser().isAdmin()) {
                        this.setFieldOptionList('type', ['regular', 'admin', 'manger']);
                    }
                    if (this.getUser().isManger()) {
                        this.setFieldOptionList('type', ['regular', 'manger']);
                    }

                } else {
                    this.setFieldReadOnly('type');
                }
            }

            if (
                !this.getConfig().get('auth2FA')
                ||
                !(this.model.isRegular() || this.model.isAdmin())
            ) {
                this.hideField('auth2FA');
            }
        },

        getGridLayout(callback) {
            this.getHelper().layoutManager
                .get(this.model.entityType, this.options.layoutName || this.layoutName, simpleLayout => {
                    let layout = Espo.Utils.cloneDeep(simpleLayout);

                    layout.push({
                        "label": "Teams and Access Control",
                        "name": "accessControl",
                        "rows": [
                            [{"name": "type"}, {"name": "isActive"}],
                            [{"name": "teams"}, {"name": "defaultTeam"}],
                            [{"name": "roles"}, false]
                        ]
                    });

                    layout.push({
                        "label": "Portal",
                        "name": "portal",
                        "rows": [
                            [{"name": "portals"}, {"name": "contact"}],
                            [{"name": "portalRoles"}, {"name": "accounts"}]
                        ]
                    });

                    if (this.getUser().isAdmin() && this.model.isPortal()) {
                        layout.push({
                            "label": "Misc",
                            "name": "portalMisc",
                            "rows": [
                                [{"name": "dashboardTemplate"}, false]
                            ]
                        });
                    }

                    if (this.model.isAdmin() || this.model.isRegular()) {
                        layout.push({
                            "label": "Misc",
                            "name": "misc",
                            "rows": [
                                [{"name": "workingTimeCalendar"}, {"name": "layoutSet"}]
                            ]
                        });
                    }

                    if (
                        this.type === this.TYPE_EDIT &&
                        (this.getUser().isAdmin() || this.getUser().isManger()) &&
                        !this.model.isApi()
                    ) {
                        layout.push({
                            label: 'Password',
                            rows: [
                                [
                                    {
                                        name: 'password',
                                        type: 'password',
                                        params: {
                                            required: false,
                                            readyToChange: true,
                                        },
                                        view: 'views/user/fields/password',
                                    },
                                    {
                                        name: 'generatePassword',
                                        view: 'views/user/fields/generate-password',
                                        customLabel: '',
                                    },
                                ],
                                [
                                    {
                                        name: 'passwordConfirm',
                                        type: 'password',
                                        params: {
                                            required: false,
                                            readyToChange: true
                                        }
                                    },
                                    {
                                        name: 'passwordPreview',
                                        view: 'views/fields/base',
                                        params: {
                                            readOnly: true
                                        },
                                    },
                                ],
                                [
                                    {
                                        name: 'sendAccessInfo'
                                    },
                                    {
                                        name: 'passwordInfo',
                                        type: 'text',
                                        customLabel: '',
                                        customCode: this.passwordInfoMessage,
                                    },
                                ]
                            ]
                        });
                    }

                    if (this.getUser().isAdmin() && this.model.isApi()) {
                        layout.push({
                            "name": "auth",
                            "rows": [
                                [{"name": "authMethod"}, false]
                            ]
                        });
                    }

                    let gridLayout = {
                        type: 'record',
                        layout: this.convertDetailLayout(layout),
                    };

                    callback(gridLayout);
                });
        }
    });
});
