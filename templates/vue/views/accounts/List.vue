<template>
    <section class="content">
        <div class="row">
            <template v-for="(account, index) in accounts">
                <div class="col-md-3">
                    <div class="account small-box" :class="{ 'bg-green': !account.isSink, 'bg-yellow': account.isSink }">
                        <div class="inner">
                            <h3>{{ account.name }}</h3>
                            <template v-if="!account.isSink">
                                <p>Initial balance: <span v-html="$options.filters.currency(account.initialBalance)"/></p>
                                <div class="icon">
                                    <i class="fa fa-bank"></i>
                                </div>
                            </template>
                            <template v-if="account.isSink">
                                <p v-if="account.isSink">marked as a money sink</p>
                                <div class="icon">
                                    <i class="fa fa-credit-card"></i>
                                </div>
                            </template>
                            <div class="small-box-footer">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <a class="btn btn-primary btn-flat btn-xs btn-block" @click="editAccount(account.uid)" v-scroll-to="'#account-form-row, 10px'"><i class="fa fa-pencil"></i> Edit</a>
                                    </div>
                                    <div class="col-xs-6">
                                        <a class="btn btn-danger btn-flat btn-xs btn-block" @click="deleteAccount(account.uid)"><i class="fa fa-remove"></i> Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <div class="col-md-3">
                <div class="account small-box bg-blue" style="cursor:pointer" @click="addNew" v-scroll-to="'#account-form-row, 10px'">
                    <div class="inner">
                        <h3>New</h3>
                        <p>Add new account</p>
                        <div class="icon">
                            <i class="fa fa-plus"></i>
                        </div>
                        <div class="small-box-footer">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="account-form-row">
            <div class="col-lg-8 col-lg-push-2" v-if="form.show">
                <div class="box box-primary" id="account-form">
                    <div class="box-header with-border" v-if="form.title">
                        <h3 class="box-title" v-html="form.title" />
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <form>
                        <div class="box-body">
                            <div class="input-block semi-transparent-white-cover" v-show="form.block"></div>
                            <div class="form-group">
                                <label class="form-control-label">Name</label>
                                <input type="text" ref="nameInput" class="form-control" v-model="form.name"></input>
                            </div>
                            <div class="form-group" v-if="!form.isSink">
                                <label class="form-control-label">Initial Balance</label>
                                <vue-numeric ref="initialBalanceInput" class="form-control text-right" currency="" separator=" " v-model="form.initialBalance" :minus="false" :precision="2" name="initialBalance" :disabled="form.isSink"></vue-numeric>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Mark as money sink</label>
                                <p class="text-right m-0"><input name="isSink" value="true" type="checkbox" v-model="form.isSink"></p>
                                <p class="form-text">
                                    Accounts marked as money sink will not have its current balance shown on the overview page.
                                    The account's balance, expense, and income will not be counted towards the statistic total,
                                    also, transfers to the account will be counted as an expense, and transfers from the account
                                    will be counted as an income.
                                </p>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-block" @click="submit">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<style lang="css">
.account.card a {
    font-size: 0.8em;
    cursor: pointer;
}
</style>

<script>
import VueNumeric from '../../components/Numeric.vue'

export default {
    name: 'accounts-list',
    data: function() {
        return {
            accounts: [],
            form: {
                show: false,
                block: false,
                title: "",
                uid: null,
                name: "",
                initialBalance: 0,
                isSink: false
            }
        }
    },
    components: {
        VueNumeric
    },
    created: function() {
        this.getAccounts()
    },
    methods: {
        getAccounts: function() {
            var self = this
            self.accounts = []
            self.$http.get('api/accounts').then(response => {
                self.accounts = response.body.accounts
            }, response => {
                self.accounts = []
            })
        },

        addNew: function() {
            this.$set(this.form, 'title', "Add new account")
            this.$set(this.form, 'uid', null)
            this.$set(this.form, 'name', "")
            this.$set(this.form, 'initialBalance', 0)
            this.$set(this.form, 'isSink', false)
            this.$set(this.form, 'block', false)
            this.$set(this.form, 'show', true)
        },

        editAccount: function(account) {
            var self = this

            self.$set(self.form, 'block', true)
            self.$http.get('api/account/'+account).then(response => {
                this.$set(this.form, 'title', "Edit existing account ("+response.body.account.name+")")
                self.$set(self.form, 'uid', response.body.account.uid)
                self.$set(self.form, 'name', response.body.account.name)
                self.$set(self.form, 'initialBalance', response.body.account.initialBalance)
                self.$set(self.form, 'isSink', response.body.account.isSink == 1 ? true : false)
                self.$set(self.form, 'block', false)
                self.$set(self.form, 'show', true)
            }, response => {
                self.$swal({
                    title: 'Not Found',
                    text: 'Could not find account with that identifier.',
                    type: 'warning'
                })
            })
        },

        deleteAccount: function(account) {
            var self = this

            self.$swal({
                title: 'Delete Confirmation',
                text: 'Are you sure? ALL transactions under this account will also be DELETED.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f86c6b',
                cancelButtonColor: '#1985ac',
                confirmButtonText: 'Delete'
            }).then(function() {
                self.$http.delete('api/account/'+account, { body: { csrfToken: window.user.csrfToken } }).then(response => {
                    self.$swal({
                        title: 'Deleted',
                        text: 'Account has been deleted.',
                        type: 'success'
                    })

                    self.getAccounts()
                }, response => {
                    self.$swal({
                        title: 'Failure',
                        text: 'Account was not deleted.',
                        type: 'error'
                    })
                })
            })
        },

        submit: function(e) {
            e.preventDefault()
            var self = this

            if(self.form.block) {
                return false
            }

            self.$set(self.form, 'block', true)

            var data = {
                csrfToken: window.user.csrfToken,
                name: self.form.name,
                initialBalance: self.form.isSink ? 0 : self.form.initialBalance,
                isSink: self.form.isSink
            }

            if(typeof self.form.uid == "undefined" || self.form.uid == null) {

                self.$http.post('api/accounts', data).then(response => {
                    var account = response.body.account;

                    self.$swal({
                        title: 'Success',
                        text: 'Account saved.',
                        type: 'success'
                    }).then(function() {
                        self.getAccounts()
                        self.$set(self.form, 'show', false)
                    }, function() {
                        self.getAccounts()
                        self.$set(self.form, 'show', false)
                    })
                }, response => {
                    self.$swal({
                        title: 'Failure',
                        text: 'Data not saved.',
                        type: 'error'
                    })
                    self.$set(self.form, 'block', false)
                })
            } else {
                self.$http.put('api/account/'+self.form.uid, data).then(response => {
                    var account = response.body.account

                    self.$swal({
                        title: 'Success',
                        text: 'Account saved.',
                        type: 'success'
                    }).then(function() {
                        self.getAccounts()
                        self.$set(self.form, 'show', false)
                    }, function() {
                        self.getAccounts()
                        self.$set(self.form, 'show', false)
                    })
                    self.$set(self.form, 'block', false)
                }, response => {
                    self.$swal({
                        title: 'Failure',
                        text: 'Data not saved.',
                        type: 'error'
                    })
                    self.$set(self.form, 'block', false)
                })
            }
        }
    }
}
</script>
