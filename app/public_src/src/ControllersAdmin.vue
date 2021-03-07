<template>
    <div class="crud">
        <div class="content">
            <div id="data" class="tab">

                <template v-if="!selectedClassName">
                    <p>No controller selected!</p>
                </template>

                <b-modal
                        id="class-permissions"
                        :title="title_permissions"
                        header-bg-variant="success"
                        header-text-variant="light"
                        body-bg-variant="light"
                        body-text-variant="dark"
                        hide-footer
                        size="lg"
                >
                    <b-table
                            striped
                            show-empty
                            :items="items_permissions"
                            :fields="fields_permissions"
                            empty-text="No records found!"
                            head-variant="dark"
                            table-hover
                            :busy.sync="isBusy_permissions"
                    >
                        <template v-slot:[setSlotCell(action_name)]="row" v-for="(permission_uuid, action_name) in items_permissions[0].permissions">
                            <b-form-checkbox :value="row.item.permissions[action_name] ? row.item.permissions[action_name] : 0" unchecked-value="" @change="togglePermission(row.item, action_name, row.item.permissions[action_name] ? 1 : 0)" v-model="row.item.permissions[action_name]"></b-form-checkbox>
                        </template>
<!--
                        <template v-slot:cell(create_granted)="row">
                            <b-form-checkbox :value="row.item.create_granted" :unchecked-value="0" @change="togglePermission(row.item, 'create')" v-model="row.item.create_granted"></b-form-checkbox>
                        </template>

                        <template v-slot:cell(read_granted)="row">
                            <b-form-checkbox :value="row.item.read_granted" :unchecked-value="0" @change="togglePermission(row.item, 'read')" v-model="row.item.read_granted"></b-form-checkbox>
                        </template>

                        <template v-slot:cell(write_granted)="row">
                            <b-form-checkbox :value="row.item.write_granted" :unchecked-value="0" @change="togglePermission(row.item, 'write')" v-model="row.item.write_granted"></b-form-checkbox>
                        </template>

                        <template v-slot:cell(delete_granted)="row">
                            <b-form-checkbox :value="row.item.delete_granted" :unchecked-value="0" @change="togglePermission(row.item, 'delete')" v-model="row.item.delete_granted"></b-form-checkbox>
                        </template>

                        <template v-slot:cell(grant_permission_granted)="row">
                            <b-form-checkbox :value="row.item.grant_permission_granted" :unchecked-value="0" @change="togglePermission(row.item, 'grant_permission')" v-model="row.item.grant_permission_granted"></b-form-checkbox>
                        </template>

                        <template v-slot:cell(revoke_permission_granted)="row">
                            <b-form-checkbox :value="row.item.revoke_permission_granted" :unchecked-value="0" @change="togglePermission(row.item, 'revoke_permission')" v-model="row.item.revoke_permission_granted"></b-form-checkbox>
                        </template>
-->
                    </b-table>
                </b-modal>
            </div>
        </div>
    </div>

</template>

<script>
    import Hook from '@GuzabaPlatform.Platform/components/hooks/Hooks.vue'
    import { stringify } from 'qs'
    export default {
        name: "ControllersAdmin",
        components: {
            Hook
        },
        data() {
            return {
                limit: 10,
                currentPage: 1,
                totalItems: 0,

                selectedClassName: '',
                selectedClassNameShort: '',
                sortBy: 'none',
                sortDesc: false,

                searchValues: {},
                putValues: {},

                requestError: '',

                action: '',
                actionTitle: '',
                modalTitle: '',
                modalVariant: '',
                ButtonTitle: '',
                ButtonVariant: '',

                crudObjectUuid: '',

                actionState: false,
                loadingState: false,

                loadingMessage: '',
                successfulMessage: '',

                items: [],
                fields: [],

                items_permissions: [
                    //must have a default even empty value to avoid the error on template load
                    {
                        permissions: [],
                    }
                ],
                fields_permissions: [],
                fields_permissions_base: [
                    {
                        key: 'role_id',
                        label: 'Role ID',
                        sortable: true
                    },
                    {
                        key: 'role_name',
                        label: 'Role Name',
                        sortable: true
                    },
                ],
                /*
                items_permissions:[],
                fields_permissions:[
                    {
                        key: 'role_id',
                        label: 'Role ID',
                        sortable: true
                    },
                    {
                        key: 'role_name',
                        label: 'Role Name',
                        sortable: true
                    },
                    {
                        key: 'create_granted',
                        label: 'Create',
                        sortable: true,
                    },
                    {
                        key: 'read_granted',
                        label: 'Read',
                        sortable: true,
                    },
                    {
                        key: 'write_granted',
                        label: 'Write',
                        sortable: true,
                    },
                    {
                        key: 'delete_granted',
                        label: 'Delete',
                        sortable: true,
                    },
                    {
                        key: 'grant_permission_granted',
                        label: 'Grant Permission',
                        sortable: true,
                    },
                    {
                        key: 'revoke_permission_granted',
                        label: 'Revoke Permission',
                        sortable: true,
                    }
                ],
                */
                title_permissions: "Permissions",
                isBusy_permissions: false,
                selectedObject: {},

                newObject: {}
            }
        },
        methods: {
            // https://stackoverflow.com/questions/58140842/vue-and-bootstrap-vue-dynamically-use-slots
            setSlotCell(action_name) {
                return `cell(${action_name})`;
            },
            resetParams(className){
                this.currentPage = 1;
                this.totalItems = 0;
                this.selectedClassName = className;
                this.selectedClassNameShort = className.split(".").pop();
                this.sortBy = 'none';
            },

            showPermissions(row) {
                //this.title_permissions = "Permissions for object of class \"" + row.meta_class_name + "\" with id: " + row.meta_object_id + ", object_uuid: " + row.meta_object_uuid;

                //this.selectedObject = row;
                this.title_permissions = "Permissions for controller " + this.selectedClassName;

                let self = this;
                //let [class_name, method_name] = this.selectedClassName.split('::');

                //this.$http.get('/admin/permissions-classes/' + class_name.split('\\').join('-') + '/' + method_name)
                // this.$http.get('/admin/permissions-controllers/' + this.selectedClassName.split('\\').join('-') )
                //     .then(resp => {
                //         self.items_permissions = Object.values(resp.data.items);
                //     })
                //     .catch(err => {
                //         console.log(err);
                //         self.requestError = err;
                //         self.items_permissions = [];
                //     }).finally(function(){
                //     self.$bvModal.show('class-permissions');
                // });
                this.$http.get('/admin/permissions-controllers/' + this.selectedClassName.split('\\').join('-') )
                    .then(resp => {
                        this.items_permissions = Object.values(resp.data.items);
                        //this.fields_permissions = this.fields_permissions_base;//wrong - changes the reference and does while a clone is needed.
                        // https://www.samanthaming.com/tidbits/70-3-ways-to-clone-objects/
                        //this.fields_permissions = Object.assign({}, this.fields_permissions_base);//reset the columns // wrong as produces Object not Array
                        //this.fields_permissions = { ...this.fields_permissions_base};//wrong - produces object not Array
                        this.fields_permissions = JSON.parse(JSON.stringify(this.fields_permissions_base)) //deep clone and produce again Array
                        for (let action_name in this.items_permissions[0].permissions) {
                            this.fields_permissions.push({
                                key: action_name,
                                label: action_name,
                                sortable: true,
                            });
                        }
                    })
                    .catch(err => {
                        this.requestError = err
                        this.items_permissions = []
                    }).finally(() => {
                        this.$bvModal.show('class-permissions')
                    })

            },

            togglePermission(row, action, checked){
                this.isBusy_permission = true;
                let sendValues = {};
                let url = '/acl-permission';

                if (row[action + '_granted']) {
                    //let object_uuid = row[action + '_granted'];
                    let object_uuid = row.permissions[action];
                    this.action = "delete";//http method
                    url += '/' + object_uuid;
                } else {
                    this.action = "post";//http method
                    sendValues.role_id = row.role_id;
                    sendValues.class_name = this.selectedClassName;
                    sendValues.action_name = action;
                    sendValues.object_id = null;
                }

                this.$http({
                        method: this.action,
                        url: url,
                        //data: this.$stringify(sendValues)
                        data: sendValues
                    })
                    .then(resp => {
                        this.$bvToast.toast(resp.data.message, {
                            // title: '',
                            autoHideDelay: 5000,
                            variant: 'info',
                            appendToast: true,
                            solid: true,
                            noCloseButton: true
                        })
                    })
                    .catch(err => {
                        console.log(err);
                        this.$bvToast.toast(err.response.data.message, {
                            // title: '',
                            autoHideDelay: 5000,
                            variant: 'info',
                            appendToast: true,
                            solid: true,
                            noCloseButton: true
                        })
                        //self.requestError = err;

                    })
                    .finally(() => {
                        this.showPermissions(this.selectedObject)
                        this.isBusy_permission = false;
                    });
                    // .catch(err => {
                    //     console.log(err);
                    // })
                    // .finally(function(){
                    //     self.showPermissions(self.selectedObject)
                    //     self.isBusy_permission = false;
                    // });
            }
        },
        props: {
            contentArgs: {}
        },
        watch:{
            $route (to, from) { // needed because by default no class is loaded and when it is loaded the component for the two routes is the same.
                this.selectedClassName = this.$route.params.class.split('-').join('\\');
                this.showPermissions(this.selectedClassName)

            }
        },
        mounted() {
            if (this.$route.params.class) {
                this.selectedClassName = this.$route.params.class.split('-').join('\\');
                if (this.selectedClassName) {
                    this.showPermissions(this.selectedClassName)
                }
            }

        }
    };

</script>

<style>
    .content {
        height: 100vh;
        top: 64px;
    }

    .tab {
        float: left;
        height: 100%;
        overflow: none;
        padding: 20px;
    }

    #sidebar{
        font-size: 10pt;
        border-width: 0 5px 0 0;
        border-style: solid;
        width: 30%;
        text-align: left;
    }

    #data {
        width: 65%;
        font-size: 10pt;
    }

    li {
        cursor: pointer;
    }

    .btn {
        width: 100%;
    }

    tr:hover{
        background-color: #ddd !important;
    }

    th:hover{
        background-color: #000 !important;
    }

    tr {
        cursor: pointer;
    }
</style>
