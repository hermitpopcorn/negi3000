<template>
    <aside class="main-sidebar">
        <section class="sidebar">
            <ul class="sidebar-menu" data-widget="tree">
                <li>
                    <router-link :to="'/overview'" exact><i class="fa fa-dashboard"></i> <span>Overview</span></router-link>
                </li>
                <li class="treeview">
                    <a>
                        <i class="fa fa-random"></i>
                        <span>Transactions</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li><router-link :to="'/transaction/list'" exact><i class="fa fa-list"></i> View transactions</router-link></li>
                        <li><router-link :to="'/transaction/add'" exact><i class="fa fa-plus"></i> Add transactions</router-link></li>
                    </ul>
                </li>
                <li>
                    <router-link :to="'/account/list'" exact><i class="fa fa-book"></i> <span>Accounts</span></router-link>
                </li>
                <li>
                    <router-link :to="'/stats/monthly'" exact><i class="fa fa-bar-chart"></i> <span>Statistics</span></router-link>
                </li>
                <li>
                    <router-link :to="'/profile'" exact><i class="fa fa-user"></i> <span>Profile</span></router-link>
                </li>
            </ul>
        </section>
    </aside>
</template>

<style lang="css">
.nav-link, li.treeview, li.treeview > a {
    cursor: pointer;
}
</style>
