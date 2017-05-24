<template>
    <section class="content">
        <div class="error-page">
            <h2 class="headline text-yellow">404</h2>

            <div class="error-content">
                <h3>Page not found.</h3>

                <p>
                    66.9% sure you just goofed up. <router-link :to="{ path: '/overview' }">Click here to go back to the overview page.</router-link>
                </p>
            </div>
        </div>
    </section>
</template>

<style>
.error-content {
    padding-top: 2em;
}
</style>

<script>
export default {
    name: 'Page404'
}
</script>
