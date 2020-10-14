<template>
    <v-app>
        <v-app-bar color="transparent" flat fixed dense>
            <div v-if="this.$route.path != '/'">
                <v-btn link to="/" color="white" class="white--text" icon>
                    <v-icon>mdi-home</v-icon>
                </v-btn>
            </div>
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
                            ><v-icon color="grey" right small
                                >mdi-account-cog-outline</v-icon
                            >
                        </v-list-item>
                        <v-list-item @click="">
                            Nastavení App<v-spacer></v-spacer
                            ><v-icon color="grey" right small
                                >mdi-settings</v-icon
                            >
                        </v-list-item>
                        <v-divider></v-divider>
                        <v-list-item @click="logOut()">
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
            width="18%"
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
                    class="body-2 mt-2 transition-fast-in-fast-out"
                    transition="scale-transition"
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
            </div>
        </v-navigation-drawer>

        <transition name="fade" mode="out-in">
            <router-view class="mt-1"> </router-view>
        </transition>

        <v-snackbar
            v-for="newNotification in newNotifications"
            :key="newNotification.id"
            class="mt-12"
            top
            color="error"
            rounded="pill"
            absolute
            right
            v-model="newNotifications"
        >
            <div class="text-center">
                <strong>text</strong>
            </div>
        </v-snackbar>
    </v-app>
</template>

<script>
export default {
    data: () => ({
        drawer: null,
        userMenu: null,
        alerts: [],
        alertCount: "",
        newNotifications: [],

        loggedUser: null
    }),

    created() {
        this.loadAlerts();
        this.loadUser();
    },
    methods: {
        loadAlerts() {
            window.axios.get("/streamAlerts").then(response => {
                this.alerts = response.data;
                this.alertCount = response.data.length;
            });
        },

        logOut() {
            let currentObj = this;
            axios.get("logout").then(response => {
                if (response.data.status === "success") {
                    currentObj.$router.push("/login");
                }
            });
        },

        loadUser() {
            let currentObj = this;
            window.axios.get("user").then(response => {
                if (response.data.status == "error") {
                    currentObj.$router.push("/login");
                } else {
                    this.loggedUser = response.data;
                }
            });
        }
    },

    mounted() {
        Echo.channel("stream-statuses").listen("StreamNotification", e => {
            this.alerts = e.streamsStatuses.original;
            this.alertCount = e.streamsStatuses.original.length;
        });
    },
    watch: {}
};
</script>
