<template>
    <div class="sidebar">
        <nav class="sidebar-nav">
            <ul class="nav">
                <li class="nav-item">
                    <router-link :to="'/overview'" class="nav-link" exact><i class="icon-speedometer"></i> Overview</router-link>
                </li>
                <router-link tag="li" class="nav-item nav-dropdown" :to="{ path: '/transaction'}" disabled>
                    <div class="nav-link nav-dropdown-toggle" @click="handleClick"><i class="icon-shuffle"></i> Transactions</div>
                    <ul class="nav-dropdown-items">
                        <li class="nav-item">
                            <router-link :to="'/transaction/list'" class="nav-link" exact><i class="icon-list"></i> View transactions</router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="'/transaction/add'" class="nav-link" exact><i class="icon-note"></i> Add transaction</router-link>
                        </li>
                    </ul>
                </router-link>
                <li class="nav-item">
                    <router-link :to="'/account/list'" class="nav-link" exact><i class="icon-notebook"></i> Accounts</router-link>
                </li>
                <li class="nav-item">
                    <router-link :to="'/profile'" class="nav-link" exact><i class="icon-user"></i> Profile</router-link>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
export default {
    name: 'sidebar',
    methods: {
        handleClick (e) {
            e.preventDefault()
            e.target.parentElement.classList.toggle('open')
        }
    }
}
</script>

<style lang="css">
.nav-link {
    cursor:pointer;
}
</style>
