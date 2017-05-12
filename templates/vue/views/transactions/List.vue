<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-lg-8 push-lg-2 col-md-12">
                <div class="row">
                    <div class="col-2 push-10">
                        <router-link :to="'/transaction/add'" class="btn btn-primary btn-block" exact><i class="icon-note"></i> Add</router-link>
                    </div>
                </div>

                <div class="input-block semi-transparent-white-cover" v-show="block"></div>
                <div class="card">
                    <div class="card-block row p-0">
                        <div class="col-2 text-center pt-2 pb-2" v-on:click="previousMonth">
                            <i class="icon-arrow-left"></i>
                        </div>
                        <div class="col-8 text-center pt-2 pb-2">{{ cursor.month | date('month') }} {{ cursor.year }}</div>
                        <div class="col-2 text-center pt-2 pb-2" v-on:click="nextMonth">
                            <i class="icon-arrow-right"></i>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-block">
                        <div class="transaction b">
                            <div class="transaction-body white">
                                <span>Balance</span>
                                <h1 v-html="$options.filters.currency(currentBalance)"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-block">
                        <div class="transaction b">
                            <div class="transaction-body white">
                                <span>Balance at the end of the period</span>
                                <h1 v-html="$options.filters.currency(periodBalance)"/>
                            </div>
                        </div>
                        <template v-for="(transaction, index) in transactions">
                            <div class="transaction-separator" v-if="index == 0 || transactions[index - 1].date.split(' ')[0] != transaction.date.split(' ')[0]">{{ transaction.date.split(' ')[0] | date }}</div>
                            <div class="transaction" :class="transaction.type">
                                <div class="transaction-head">
                                    <div class="actions">
                                        <router-link :to="{ path: '/transaction/edit/'+transaction.uid }">edit</router-link>
                                        <a href="javascript:;" @click="deleteTransaction(transaction.uid)">delete</a>
                                    </div>
                                    <span class="account" v-if="transaction.type !== 'x'">{{ transaction.accountName }}</span>
                                    <span class="account" v-if="transaction.type === 'x'">
                                        <template v-if="transaction.target">
                                            {{ transaction.accountName }} > {{ transaction.targetName }}
                                        </template>
                                        <template v-else>
                                            {{ transaction.accountName }} > ???
                                        </template>
                                    </span>
                                </div>
                                <div class="transaction-body">
                                    <h1 v-html="$options.filters.currency(transaction.amount)"/>
                                    <span class="date" v-if="transaction.date.split(' ')[1] !== '00:00:00'">at {{ $options.filters.date(transaction.date).split(' ').slice(-2).join(' ') }}</span>
                                    <p v-if="transaction.note">{{ transaction.note }}</p>
                                </div>
                                <div class="tags" v-if="transaction.tags.length > 0">
                                    <div class="transaction-body pt-1 pb-1">
                                        Tags:
                                        <template v-for="tag in transaction.tags">
                                            <router-link tag="span" class="tag" :to="{ path: '/transaction/list/tagged/'+tag }">#{{ tag }}</router-link>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div class="transaction b">
                            <div class="transaction-body white">
                                <span>Starting balance</span>
                                <h1 v-html="$options.filters.currency(startingBalance)"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--/.col-->
        </div><!--/.row-->
    </div>

</template>

<script>
export default {
    name: 'transactions-list',
    props: ['year', 'month'],
    data: function() {
        return {
            block: false,
            transactions: [],
            startingBalance: 0,
            periodBalance: 0,
            currentBalance: 0,
            cursor: { }
        }
    },
    created: function() {
        var self = this

        let date = new Date()

        // set cursor's default date as now
        self.$set(self.cursor, 'month', date.getMonth() + 1)
        self.$set(self.cursor, 'year', date.getFullYear())

        if(typeof self.year !== "undefined" && typeof self.month !== "undefined") {
            if(parseInt(self.year) <= 9999 && parseInt(self.year) >= 1900 && parseInt(self.month) <= 12 && parseInt(self.month) >= 1) {
                self.$set(self.cursor, 'year', self.year)
                self.$set(self.cursor, 'month', self.month)
            }
        }

        self.getBalance()
        self.loadData()
    },
    methods: {
        getBalance: function() {
            var self = this
            self.currentBalance = 0
            self.$http.get('api/balance/all').then(response => {
                self.currentBalance = response.body.balance
            }, response => {
                self.currentBalance = 0
            })
        },

        getPeriodBalance: function(year, month, day) {
            var self = this
            self.startingBalance = 0
            self.$http.get('api/balance/all/'+year+'-'+month+'-'+day).then(response => {
                self.startingBalance = response.body.balance
                self.calculatePeriodBalance()
            }, response => {
                self.startingBalance = 0
            })
        },

        getTransactions: function(year, month) {
            var self = this

            self.block = true
            self.transactions = []
            self.$http.get('api/transactions/all/'+year+'/'+month).then(response => {
                self.transactions = response.body.transactions
                self.calculatePeriodBalance()
                self.block = false
            }, response => {
                self.transactions = []
                self.block = false
            })
        },

        loadData: function() {
            let date, year, month, day;
            date = new Date()
            date.setMonth(this.cursor.month - 1)
            date.setYear(this.cursor.year)
            date.setDate(1)

            // get starting balance
            date.setDate(0)
            year = date.getFullYear()
            month = date.getMonth() + 1
            day = date.getDate()
            this.getPeriodBalance(year, month, day)

            year = this.cursor.year
            month = this.cursor.month
            this.getTransactions(year, month)
        },

        deleteTransaction: function(transaction) {
            var self = this

            self.$swal({
                title: 'Delete Confirmation',
                text: 'Are you sure?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f86c6b',
                cancelButtonColor: '#1985ac',
                confirmButtonText: 'Delete'
            }).then(function() {
                self.$http.delete('api/transaction/'+transaction, { body: { csrfToken: window.user.csrfToken } }).then(response => {
                    self.$swal({
                        title: 'Deleted',
                        text: 'Transaction has been deleted.',
                        type: 'success'
                    })

                    self.getBalance()
                    self.loadData()
                }, response => {
                    self.$swal({
                        title: 'Failure',
                        text: 'Transaction was not deleted.',
                        type: 'error'
                    })
                })
            })
        },

        calculatePeriodBalance: function() {
            var self = this
            var balance = self.startingBalance

            if(self.transactions.length > 0) {
                for(let i = 0; i < self.transactions.length; i++) {
                    if(self.transactions[i].accountIsSink == false) {
                        if(self.transactions[i].type == 'i') {
                            balance += parseFloat(self.transactions[i].amount)
                        } else if(self.transactions[i].type == 'e') {
                            balance -= parseFloat(self.transactions[i].amount)
                        } else if(self.transactions[i].type == 'x' && self.transactions[i].targetIsSink) {
                            balance -= parseFloat(self.transactions[i].amount)
                        }
                    } else {
                        if(self.transactions[i].type == 'x' && self.transactions[i].targetIsSink == false) {
                            balance += parseFloat(self.transactions[i].amount)
                        }
                    }
                }
            }

            self.periodBalance = balance
        },

        previousMonth: function() {
            var self = this

            // Get the current date from self.cursor
            var date = new Date(self.cursor.year + "-" + self.cursor.month)
            // Set it to go backwards 1 month
            date.setDate(0)

            // Set the new cursor
            self.$set(self.cursor, 'month', date.getMonth() + 1)
            self.$set(self.cursor, 'year', date.getFullYear())

            // Get the starting balance by getting the balance up to the previous month
            let prev = new Date(date.getTime());
            prev.setDate(0)
            self.getPeriodBalance(prev.getFullYear(), prev.getMonth() + 1, prev.getDate())
            // Get the transactions for the month
            self.getTransactions(date.getFullYear(), date.getMonth() + 1)
        },

        nextMonth: function() {
            var self = this

            // Get the current date from self.cursor
            var date = new Date(self.cursor.year + "-" + self.cursor.month)
            // Increment month
            date.setMonth(date.getMonth()+1)

            // Set the new cursor
            self.$set(self.cursor, 'month', date.getMonth() + 1)
            self.$set(self.cursor, 'year', date.getFullYear())

            // Get the starting balance by getting the balance up to the previous month
            let prev = new Date(date.getTime());
            prev.setDate(0)
            self.getPeriodBalance(prev.getFullYear(), prev.getMonth() + 1, prev.getDate())
            // Get the transactions for the month
            self.getTransactions(date.getFullYear(), date.getMonth() + 1)
        }
    }
}
</script>
