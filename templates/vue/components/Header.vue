<template>
    <navbar>
        <button class="navbar-toggler mobile-sidebar-toggler d-lg-none" type="button" @click="mobileSidebarToggle">&#9776;</button>
        <a class="navbar-brand" href="#"></a>
        <ul class="nav navbar-nav ml-auto" style="margin-right: 2em">
            <li class="nav-item d-md-down-none">
                <router-link :to="{ path: '/profile' }"><span class="d-md-down-none btn btn-primary btn-sm btn-block">{{ user.name }}</span></router-link>
            </li>
            <li class="nav-item d-md-down-none">
                <a class="btn btn-danger btn-sm" href="auth/logout"><strong><i class="icon-logout"></i> Log out</strong></a>
            </li>
        </ul>
    </navbar>
</template>
<script>

import navbar from './Navbar.vue'

export default {
    name: 'header',
    data: function() {
        return {
            user: window.user
        }
    },
    components: {
        navbar
    },
    methods: {
        click () {
            // do nothing
        },
        sidebarToggle (e) {
            e.preventDefault()
            document.body.classList.toggle('sidebar-hidden')
        },
        sidebarMinimize (e) {
            e.preventDefault()
            document.body.classList.toggle('sidebar-compact')
        },
        mobileSidebarToggle (e) {
            e.preventDefault()
            document.body.classList.toggle('sidebar-mobile-show')
        },
        asideToggle (e) {
            e.preventDefault()
            document.body.classList.toggle('aside-menu-hidden')
        }
    }
}
</script>
