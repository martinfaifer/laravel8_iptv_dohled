<template>
    <v-main>
        <!-- menu -->
        <v-app-bar class="mt-12" color="transparent" fixed dense flat dark>
            <v-row justify="center" fixed>
                <v-card fixed flat color="transparent">
                    <v-toolbar color="transparent" flat dense dark>
                        <v-spacer></v-spacer>

                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    link
                                    to="/settings/prehled"
                                    class="white--text"
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
                                    class="white--text"
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

                        <div>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        link
                                        to="/settings/users"
                                        class="white--text"
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

                        <div>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        link
                                        to="/settings/alerts"
                                        class="white--text"
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

                        <div>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        link
                                        to="/settings/logs"
                                        class="white--text"
                                        v-on="on"
                                        icon
                                    >
                                        <v-icon
                                            v-if="
                                                $route.path === '/settings/logs'
                                            "
                                            color="teal"
                                            >mdi-git</v-icon
                                        >
                                        <v-icon v-else>mdi-git</v-icon>
                                    </v-btn>
                                </template>
                                <span>Log</span>
                            </v-tooltip>
                        </div>
                        <div>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        link
                                        to="/settings/firewall"
                                        class="white--text"
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
        return {};
    },
    watch: {
        // userData: function() {
        //     if(this.userData.user_role === 'náhled') {
        //         this.$router.push("/");
        //     }
        // }
    }
};
</script>
