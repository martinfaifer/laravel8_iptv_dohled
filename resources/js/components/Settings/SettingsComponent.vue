<template>
    <v-main>
        <!-- menu -->

        <v-app-bar
            class="mt-12"
            color="rgba(18, 18, 18, 0.8)"
            fixed
            flat
            dense
            dark
        >
            <v-row justify="center">
                <v-card flat color="transparent">
                    <v-toolbar color="transparent" flat dense dark>
                        <v-spacer></v-spacer>

                        <!-- <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    link
                                    to="/settings/prehled_streams"
                                    :class="{
                                        'white--text':
                                            $vuetify.theme.dark === true,
                                        'black--text':
                                            $vuetify.theme.dark === false
                                    }"
                                    v-on="on"
                                    icon
                                >
                                    <v-icon
                                        v-if="
                                            $route.path ===
                                                '/settings/prehled_streams'
                                        "
                                        color="teal"
                                        >mdi-view-stream</v-icon
                                    >
                                    <v-icon v-else>mdi-view-stream</v-icon>
                                </v-btn>
                            </template>
                            <span>Dashboard Streamů</span>
                        </v-tooltip> -->

                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    link
                                    to="/settings/prehled"
                                    :class="{
                                        'white--text':
                                            $vuetify.theme.dark === true,
                                        'black--text':
                                            $vuetify.theme.dark === false
                                    }"
                                    v-on="on"
                                    icon
                                >
                                    <v-icon
                                        v-if="
                                            $route.path === '/settings/prehled'
                                        "
                                        color="teal"
                                        >mdi-view-dashboard</v-icon
                                    >
                                    <v-icon v-else>mdi-view-dashboard</v-icon>
                                </v-btn>
                            </template>
                            <span>Dashboard</span>
                        </v-tooltip>

                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    link
                                    to="/settings/streams"
                                    :class="{
                                        'white--text':
                                            $vuetify.theme.dark === true,
                                        'black--text':
                                            $vuetify.theme.dark === false
                                    }"
                                    v-on="on"
                                    icon
                                >
                                    <v-icon
                                        v-if="
                                            $route.path === '/settings/streams'
                                        "
                                        color="teal"
                                        >mdi-television-guide</v-icon
                                    >
                                    <v-icon v-else>mdi-television-guide</v-icon>
                                </v-btn>
                            </template>
                            <span>Kanály</span>
                        </v-tooltip>

                        <div v-if="userRole == '1' || userRole =='2'">
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        link
                                        to="/settings/users"
                                        :class="{
                                            'white--text':
                                                $vuetify.theme.dark === true,
                                            'black--text':
                                                $vuetify.theme.dark === false
                                        }"
                                        v-on="on"
                                        icon
                                    >
                                        <v-icon
                                            v-if="
                                                $route.path ===
                                                    '/settings/users'
                                            "
                                            color="teal"
                                            >mdi-account-multiple-outline</v-icon
                                        >
                                        <v-icon v-else
                                            >mdi-account-multiple-outline</v-icon
                                        >
                                    </v-btn>
                                </template>
                                <span>Uživatelé</span>
                            </v-tooltip>
                        </div>

                        <div v-if="userRole == '1' || userRole =='2'">
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        link
                                        to="/settings/alerts"
                                        :class="{
                                            'white--text':
                                                $vuetify.theme.dark === true,
                                            'black--text':
                                                $vuetify.theme.dark === false
                                        }"
                                        v-on="on"
                                        icon
                                    >
                                        <v-icon
                                            v-if="
                                                $route.path ===
                                                    '/settings/alerts'
                                            "
                                            color="teal"
                                            >mdi-bell-outline</v-icon
                                        >
                                        <v-icon v-else>mdi-bell-outline</v-icon>
                                    </v-btn>
                                </template>
                                <span>Nastavení Alertingu</span>
                            </v-tooltip>
                        </div>
                        <div v-if="userRole == '1' || userRole =='2'">
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        link
                                        to="/settings/firewall"
                                        :class="{
                                            'white--text':
                                                $vuetify.theme.dark === true,
                                            'black--text':
                                                $vuetify.theme.dark === false
                                        }"
                                        v-on="on"
                                        icon
                                    >
                                        <v-icon
                                            v-if="
                                                $route.path ===
                                                    '/settings/firewall'
                                            "
                                            color="teal"
                                            >mdi-shield-outline</v-icon
                                        >
                                        <v-icon v-else
                                            >mdi-shield-outline</v-icon
                                        >
                                    </v-btn>
                                </template>
                                <span>Firewall</span>
                            </v-tooltip>
                        </div>
                        <v-spacer></v-spacer>
                    </v-toolbar>
                </v-card>
            </v-row>
        </v-app-bar>
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
            let currentObj = this;
            window.axios.get("user").then(response => {
                currentObj.userRole = response.data.role_id;
                if (currentObj.userRole == "4") {
                    currentObj.$router.push("/");
                }
            });
        }
    }
};
</script>
