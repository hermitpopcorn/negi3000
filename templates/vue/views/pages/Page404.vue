<template>
    <div class="app flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="clearfix">
                        <h1 class="float-left display-3 mr-4">404</h1>
                        <h4 class="pt-3">Page not found.</h4>
                        <router-link tag="p" class="text-muted" :to="{ path: '/overview' }" style="cursor:pointer">Click here to go back to the overview page.</router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Page404'
}
</script>
