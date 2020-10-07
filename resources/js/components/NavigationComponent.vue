<template>
    <v-app>
        <v-app-bar color="transparent" flat fixed dense>
            <v-toolbar-title>TS Analyzer</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-badge
                bordered
                bottom
                color="red"
                :content="alertCount"
                offset-x="18"
                offset-y="18"
            >
                <v-app-bar-nav-icon
                    @click="drawer = !drawer"
                ></v-app-bar-nav-icon>
            </v-badge>
        </v-app-bar>

        <v-navigation-drawer
            v-model="drawer"
            right
            fixed
            temporary
            width="15%"
            color="transparent"
        >
            <div
                id="alerty"
                class="pl-2 pr-2"
                v-for="alert in alerts"
                :key="alert.id"
            >
                <v-alert
                    dense
                    border="left"
                    :type="alert.status"
                    class="body-2 mt-2"
                >
                    {{ alert.msg }}
                </v-alert>
                <!-- <v-alert dense outlined type="error">
                    I'm a dense alert with the <strong>outlined</strong> prop
                    and a <strong>type</strong> of error
                </v-alert> -->
            </div>
        </v-navigation-drawer>

        <transition name="fade" mode="out-in">
            <router-view class="mt-1"> </router-view>
        </transition>
    </v-app>
</template>

<script>
export default {
    data: () => ({
        drawer: null,
        alerts: [],
        alertCount: ""
    }),

    created() {
        this.loadAlerts();
    },
    methods: {
        loadAlerts() {
            window.axios.get("/streamAlerts").then(response => {
                this.alerts = response.data;
                this.alertCount = response.data.lenght;
            });
        }
    },

    watch: {
        // alerts() {
        //     setInterval(() => (
        //         console.log("test"),
        //         this.loadAlerts()
        //     ), 5000)
        // }
    }
};
</script>
