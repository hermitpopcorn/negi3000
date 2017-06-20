<template>
    <div class="wrapper">
        <AppHeader/>
        <Sidebar/>
        <div class="content-wrapper">
            <section class="content-header">
                <h1>{{ name }}</h1>
                <breadcrumb :list="list"/>
            </section>
            <router-view></router-view>
        </div>
        <AppFooter/>
    </div>
</template>

<script>
import AppHeader from '../components/Header.vue'
import Sidebar from '../components/Sidebar.vue'
import AppFooter from '../components/Footer.vue'
import Breadcrumb from '../components/Breadcrumb.vue'

export default {
    name: 'full',
    components: {
        AppHeader,
        Sidebar,
        AppFooter,
        Breadcrumb
    },
    computed: {
        name() {
            return this.$route.meta && this.$route.meta.label || this.$route.name
        },

        list() {
            return this.$route.matched
        }
    }
}
</script>
