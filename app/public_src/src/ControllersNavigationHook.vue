<template>
    <div>
        <!-- <tree-menu class="small" v-for="(node, index) in classes" :nodes="node" :label="index" :contentToLoad="loadClassMethod" :depth="1"></tree-menu> -->
        <tree-menu class="small" v-for="(node, index) in classes" v-bind:key="index" :nodes="node" :label="index" :contentToLoad="loadClass" :depth="1"></tree-menu>
    </div>
</template>

<script>

    import TreeMenu from '@GuzabaPlatform.Platform/components/TreeMenu.vue'

    export default {
        name: "ControllersNavigationHook",
        components: {
            TreeMenu
        },
        data() {
            return {
                classes: [],
            }
        },
        methods: {
            resetData() {
                this.classes = [];
            },

            // loadClassMethod(class_method_name) {
            //     let [class_name, method_name] = class_method_name.split('::');
            //     let route_to_load = '/admin/classes/' + class_name.split('\\').join('-') + '/' + method_name;
            //     console.log(route_to_load);
            //     this.$router.push(route_to_load);
            // },
            loadClass(class_name) {
                let route_to_load = '/admin/controllers/' + class_name.split('\\').join('-');
                this.$router.push(route_to_load);
            },
        },
        created() {
            this.resetData();
            var self = this;
            this.$http.get('/admin/controllers')
                .then(resp => {
                    self.classes = resp.data.tree;
                    console.log(self.classes)
                })
                .catch(err => {
                    console.log(err);
                })
        }
    }
</script>

<style scoped>

</style>