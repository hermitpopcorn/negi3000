import Router from 'vue-router'

// Containers
import Full from '../containers/Full.vue'

// Views
import Overview from '../views/Overview.vue'

// Views - Transactions
import TransactionsList from '../views/transactions/List.vue'
import TransactionsListTagged from '../views/transactions/ListTagged.vue'
import TransactionsForm from '../views/transactions/Form.vue'

// Views - Accounts
import AccountsList from '../views/accounts/List.vue'

// View - Profile
import EditProfile from '../views/profile/Edit.vue'

// 404 Page
import Page404 from '../views/pages/Page404.vue'

export default new Router({
    mode: 'hash',
    linkActiveClass: 'open active',
    scrollBehavior: () => ({ y: 0 }),
    routes: [
        {
            path: '/',
            redirect: '/overview',
            name: 'Home',
            component: Full,
            children: [
                {
                    path: 'overview',
                    name: 'Overview',
                    component: Overview
                },
                {
                    path: 'transaction',
                    redirect: '/transaction/list',
                    name: 'Transactions',
                    component: {
                        render (c) { return c('router-view') }
                    },
                    children: [
                        {
                            path: 'list',
                            name: 'TransactionsList',
                            meta: { label: 'List' },
                            component: TransactionsList
                        },
                        {
                            path: 'list/:year(\\d+)/:month(\\d+)',
                            name: 'TransactionsListJump',
                            meta: { label: 'List' },
                            component: TransactionsList,
                            props: true
                        },
                        {
                            path: 'list/tagged/:tag',
                            name: 'TransactionsListTagged',
                            meta: { label: 'Tagged' },
                            component: TransactionsListTagged,
                            props: true
                        },
                        {
                            path: 'add',
                            name: 'TransactionsAdd',
                            meta: { label: 'Add' },
                            component: TransactionsForm,
                            props: { transaction: null }
                        },
                        {
                            path: 'edit/:transaction',
                            name: 'TransactionsEdit',
                            meta: { label: 'Edit' },
                            component: TransactionsForm,
                            props: true
                        }
                    ]
                },
                {
                    path: 'account',
                    redirect: '/account/list',
                    name: 'Accounts',
                    component: {
                        render (c) { return c('router-view') }
                    },
                    children: [
                        {
                            path: 'list',
                            name: 'AccountsList',
                            meta: { label: 'List' },
                            component: AccountsList
                        }
                    ]
                },
                {
                    path: 'profile',
                    name: 'Profile',
                    component: EditProfile
                }
            ]
        },
        {
            path: '*',
            component: Page404
        }
    ]
})
