<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-lg-8 push-lg-2 col-md-12">
                <div class="card">
                    <div class="card-block">
                        <template v-for="(transaction, index) in transactions">
                            <div class="transaction-separator" v-if="index == 0 || transactions[index - 1].date.split(' ')[0] != transaction.date.split(' ')[0]">{{ transaction.date.split(' ')[0] | date }}</div>
                            <div class="transaction" :class="transaction.type">
                                <div class="transaction-head">
                                    <div class="actions">
                                        <router-link :to="{ path: '/transaction/edit/'+transaction.uid }">edit</router-link>
                                        <a href="javascript:;" @click="deleteTransaction(transaction.uid)">delete</a>
                                    </div>
                                    <span class="account" v-if="transaction.type !== 'x'">{{ transaction.accountName }}</span>
                                    <span class="account" v-if="transaction.type === 'x'">{{ transaction.accountName }} > {{ transaction.targetName }}</span>
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
                    </div>
                </div>
            </div><!--/.col-->
        </div><!--/.row-->
    </div>
</template>

<script>
export default {
    name: 'transactions-list-tagged',
    props: ['tag'],
    data: function() {
        return {
            transactions: []
        }
    },
    created: function() {
        this.getTransactions(this.tag)
    },
    methods: {
        getTransactions: function(tag) {
            var self = this

            self.block = true
            self.transactions = []
            self.$http.get('api/transactions/tagged/'+tag).then(response => {
                self.transactions = response.body.transactions
            }, response => {
                self.transactions = []
            })
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
        }
    },
    watch: {
        '$route.params.tag'(newTag, oldTag) {
            this.getTransactions(newTag)
        }
    }
}
</script>
