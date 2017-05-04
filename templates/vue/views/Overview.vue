<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-sm-12 col-lg-6">
                <cardBalance/>
            </div>
            <div class="col-sm-6 col-lg-3">
                <cardExpenseCurrentMonth/>
            </div>
            <div class="col-sm-6 col-lg-3">
                <cardIncomeCurrentMonth/>
            </div>
        </div>

        <div class="row">
            <template v-for="account in accounts">
                <div class="col-12">
                    <div class="card card-inverse" :class="cardColor(account.balance)">
                        <div class="card-block pb-0">
                            <h4 class="mb-0" v-html="$options.filters.currency(account.balance)"/>
                            <p>{{ account.name }} Balance</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="row">
            <div class="col-md-6">
                <router-link :to="'/transaction/list'" class="btn btn-lg btn-block btn-primary" exact><h3><i class="icon-shuffle"></i> Transactions</h3></router-link>
            </div>
            <div class="col-md-6">
                <router-link :to="'/account/list'" class="btn btn-lg btn-block btn-primary" exact><h3><i class="icon-notebook"></i> Accounts</h3></router-link>
            </div>
        </div>
    </div>
</template>

<script>
import CardBalance from './overview/CardBalance.vue'
import CardIncomeCurrentMonth from './overview/CardIncomeCurrentMonth.vue'
import CardExpenseCurrentMonth from './overview/CardExpenseCurrentMonth.vue'

export default {
    name: 'overview',
    components: {
        CardBalance,
        CardIncomeCurrentMonth,
        CardExpenseCurrentMonth
    },
    data: function() {
        return {
            accounts: []
        }
    },
    mounted: function() {
        var self = this

        self.$http.get('api/accounts').then(response => {
            self.accounts = response.body.accounts
            for(let i = 0; i < self.accounts.length; i++) {
                self.$set(self.accounts[i], 'balance', 0)
                self.$http.get('api/balance/'+self.accounts[i].uid).then(response => {
                    self.accounts[i].balance = response.body.balance
                    self.$set(self.accounts[i], 'balance', response.body.balance)
                }, response => {
                    self.$set(self.accounts[i], 'balance', 0)
                })
            }
        }, response => {
            self.accounts = []
        })
    },
    methods: {
        cardColor: function(balance) {
            if(balance >= 0) {
                return "card-success"
            } else {
                return "card-danger"
            }
        }
    }
}
</script>
