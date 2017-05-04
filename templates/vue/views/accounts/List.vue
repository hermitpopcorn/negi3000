<template>
    <div class="animated fadeIn">
        <div class="row">
            <template v-for="(account, index) in accounts">
                <div class="col-md-3">
                    <div class="account card">
                        <div class="card-block text-center">
                            <h1>{{ account.name }}</h1>
                            <p>Initial balance: <span v-html="$options.filters.currency(account.initialBalance)"/></p>
                            <p v-if="account.excludeFromBalance" style="color:#176c82">excluded from total balance</p>
                            <p v-if="!account.excludeFromBalance">&nbsp;</p>
                            <p><a class="btn btn-xs btn-primary" style="color:white" @click="editAccount(account.uid)">Edit</a><a class="btn btn-xs btn-danger" style="color:white" @click="deleteAccount(account.uid)">Delete</a></p>
                        </div>
                    </div>
                </div>
            </template>
            <div class="col-md-3">
                <div class="account card" style="cursor:pointer" @click="addNew">
                    <div class="card-block text-center">
                        <h1><i class="icon icon-plus"></i></h1>
                        <p>Add new account</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" v-if="form.show">
            <div class="col-12 col-lg-8 push-lg-2">
                <div class="card">
                    <div class="card-header" v-if="form.title" v-html="form.title" />
                    <div class="card-block">
                        <form>
                            <div class="input-block semi-transparent-white-cover" v-show="form.block"></div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="form-control-label">Name</label>
                                    <input type="text" ref="nameInput" class="form-control" v-model="form.name"></input>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="form-control-label">Initial Balance</label>
                                    <vue-numeric ref="initialBalanceInput" class="form-control text-right" currency="" separator=" " v-model="form.initialBalance" :minus="false" :precision="2" name="initialBalance"></vue-numeric>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="form-control-label">Exclude from Total Balance</label>
                                    <p style="text-align:right"><input name="excludeFromBalance" value="true" type="checkbox" v-model="form.excludeFromBalance"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button class="btn btn-primary btn-block" @click="submit">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                excludeFromBalance: false
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
            this.$set(this.form, 'excludeFromBalance', false)
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
                self.$set(self.form, 'excludeFromBalance', response.body.account.excludeFromBalance == 1 ? true : false)
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
                initialBalance: self.form.initialBalance,
                excludeFromBalance: self.form.excludeFromBalance
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
