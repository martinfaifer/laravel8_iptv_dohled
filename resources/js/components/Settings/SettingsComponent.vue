<template>
    <v-main class="pt-5">
        <!-- router -->
        <transition name="fade" mode="out-in">
            <router-view class="mt-16" />
        </transition>

        <!-- konec menu -->
    </v-main>
</template>
<script>
export default {
    computed: {
        userData() {
            return this.$store.state.userData;
        }
    },
    data() {
        return {
            userRole: null
        };
    },
    created() {
        this.loadUser();
    },
    methods: {
        loadUser() {
            axios.get("user").then(response => {
                this.userRole = response.data.role_id;
                if (this.userRole == "4") {
                    this.$router.push("/");
                }
            });
        }
    }
};
</script>
