<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-block">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="callout callout-info">
                                            <small class="text-muted">Total Balance</small>
                                            <br>
                                            <strong class="h4" v-html="$options.filters.currency(totalBalance)"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="callout callout-danger">
                                            <small class="text-muted">Total expense this month</small>
                                            <br>
                                            <strong class="h4" v-html="$options.filters.currency(totalExpense)"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="callout callout-success">
                                            <small class="text-muted">Total income this month</small>
                                            <br>
                                            <strong class="h4" v-html="$options.filters.currency(totalIncome)"></strong>
                                        </div>
                                    </div>
                                </div>

                                <hr class="mt-0">
                                <ul style="list-style: none; padding: 0; margin: 0; margin-bottom: 1em">
                                    <li>
                                        <div class="row">
                                            <div class="col-4">
                                                <span style="color: #318a4f">income</span>
                                            </div>
                                            <div class="col-4 text-center">
                                                <span style="color: #7d7b9a">balance this month</span>
                                            </div>
                                            <div class="col-4 text-right">
                                                <span style="color: #a83838">expense</span>
                                            </div>
                                        </div>
                                        <div class="bars">
                                            <div class="progress progress-lg income-v-expense">
                                                <div class="progress-bar" role="progressbar" :style="{ width: incomePercentage + '%' }" :aria-valuenow="incomePercentage" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4">
                                                <span style="color: #318a4f">{{ incomePercentage }}%</span>
                                            </div>
                                            <div class="col-4 text-center">
                                                <span style="color: #7d7b9a" v-html="$options.filters.currency(totalIncome - totalExpense)"></span>
                                            </div>
                                            <div class="col-4 text-right">
                                                <span style="color: #a83838">{{ expensePercentage }}%</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>

                                <hr class="mt-0">
                                <ul class="icons-list">
                                    <template v-for="account in accounts">
                                        <li>
                                            <i class="icon-notebook bg-primary" v-if="account.balance !== null"></i>
                                            <i class="icon-wallet bg-danger" v-if="account.balance === null"></i>
                                            <div class="desc">
                                                <div class="small text-muted" v-if="account.balance !== null">regular account</div>
                                                <div class="small text-muted" v-if="account.balance === null">money sink</div>
                                                <div class="title" style="padding:0">{{ account.name }}</div>
                                            </div>
                                            <div class="value" v-if="account.balance !== null">
                                                <div class="small text-muted">remaining balance</div>
                                                <strong v-html="$options.filters.currency(account.balance)"></strong>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
export default {
    name: 'overview',
    data: function() {
        return {
            totalBalance: "Loading...",
            totalIncome: "Loading...",
            totalExpense: "Loading...",
            accounts: []
        }
    },
    mounted: function() {
        var self = this

        this.getTotalBalance()
        this.getAccountsBalance()
        this.getTotalIncome()
        this.getTotalExpense()
    },
    methods: {
        getTotalBalance: function() {
            var self = this
            self.$http.get('api/balance/all').then(response => {
                self.totalBalance = response.body.balance
            }, response => {
                self.totalBalance = "???"
            });
        },

        getAccountsBalance: function() {
            var self = this
            self.$http.get('api/accounts').then(response => {
                self.accounts = response.body.accounts
                for(let i = 0; i < self.accounts.length; i++) {
                    if(!self.accounts[i].isSink) {
                        self.$set(self.accounts[i], 'balance', 0)
                        self.$http.get('api/balance/'+self.accounts[i].uid).then(response => {
                            self.accounts[i].balance = response.body.balance
                            self.$set(self.accounts[i], 'balance', response.body.balance)
                        }, response => {
                            self.$set(self.accounts[i], 'balance', 0)
                        })
                    } else {
                        self.$set(self.accounts[i], 'balance', null)
                    }
                }
            }, response => {
                self.accounts = []
            })
        },

        getTotalIncome: function() {
            var self = this

            let date = new Date()
            let year = date.getFullYear()
            let month = date.getMonth() + 1
            self.$http.get('api/stats/income/'+year+'/'+month).then(response => {
                self.totalIncome = response.body.income
            }, response => {
                self.totalIncome = "???"
            })
        },

        getTotalExpense: function() {
            var self = this

            let date = new Date()
            let year = date.getFullYear()
            let month = date.getMonth() + 1
            self.$http.get('api/stats/expense/'+year+'/'+month).then(response => {
                self.totalExpense = response.body.expense
            }, response => {
                self.totalExpense = "???"
            })
        }
    },
    computed: {
        incomePercentage: function() {
            let income = !isNaN(this.totalIncome) ? this.totalIncome : 0
            let expense = !isNaN(this.totalExpense) ? this.totalExpense : 0
            let percentage = ((income / (income + expense)) * 100)
            return Math.round(percentage * 100) / 100
        },
        expensePercentage: function() {
            let income = this.incomePercentage
            let percentage = (100 - income)
            return Math.round(percentage * 100) / 100
        }
    }
}
</script>
