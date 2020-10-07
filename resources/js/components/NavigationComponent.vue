<template>
    <v-app>
        <v-app-bar color="transparent" flat fixed dense>
            <v-toolbar-title>TS Analyzer</v-toolbar-title>
            <v-spacer></v-spacer>

            <!-- user Part -->

            <template v-if="$vuetify.breakpoint.smAndUp">
                <v-menu transition="scroll-y-transition">
                    <template v-slot:activator="{ on }">
                        <v-btn class="white--text" fab icon v-on="on">
                            <v-avatar color="transparent" small>
                                <v-icon dark>
                                    mdi-account
                                </v-icon>
                            </v-avatar>
                        </v-btn>
                    </template>
                    <v-list width="250px" class="text-center subtitle-2">
                        <v-list-item link to="/user">
                            Editace <v-spacer></v-spacer
                            ><v-icon color="grey" right small>mdi-account-cog-outline</v-icon>
                        </v-list-item>
                        <v-list-item @click="">
                            Nastavení App<v-spacer></v-spacer
                            ><v-icon color="grey" right small>mdi-settings</v-icon>
                        </v-list-item>
                        <v-divider></v-divider>
                        <v-list-item @click="LogOut()">
                            Odhlásit se <v-spacer></v-spacer
                            ><v-icon color="grey" right small>mdi-lock</v-icon>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </template>

            <!-- end User Part -->
            <v-badge
                bordered
                bottom
                color="red"
                :content="alertCount"
                offset-x="10"
                offset-y="10"
            >
                <v-icon @click="drawer = !drawer">mdi-bell-outline</v-icon>
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
                    <strong>{{ alert.msg }}</strong>
                    <div v-show="alert.data">
                        <v-row
                            class="ml-3"
                            v-for="issueData in alert.data"
                            :key="issueData.id"
                        >
                            <small>
                                <strong>
                                    {{ issueData.message }}
                                </strong>
                            </small>
                        </v-row>
                    </div>
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
        userMenu: null,
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
                this.alertCount = response.data.length;
            });
        }
    },

    mounted() {
        setInterval(() => this.loadAlerts(), 1000);
    },
    watch: {}
};
</script>
