<template>
    <header class="main-header">
        <router-link :to="{ path: '/' }" class="logo">
            <span class="logo-mini"><b>N</b>3K</span>
            <span class="logo-lg"><b>NEGI</b>3000</span>
        </router-link>

        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button" @click="sidebarToggle">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu" style="cursor:default">
                        <a class="dropdown-toggle" data-toggle="dropdown">
                            <span class="hidden-xs">{{ user.name }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header">
                                <p>
                                    {{ user.name }}
                                </p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-left">
                                    <router-link :to="{ path: '/profile' }" class="btn btn-default btn-flat">Profile</router-link>
                                </div>
                                <div class="pull-right">
                                    <a href="auth/logout" class="btn btn-default btn-flat">Log out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
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
        sidebarToggle (e) {
            e.preventDefault()
            document.body.classList.toggle('sidebar-collapse')
            document.body.classList.toggle('sidebar-open')
        }
    }
}
</script>
