<template>
    <v-main>
        <div v-if="loadingApp === true">
            <v-row align="center" justify="space-around">
                <span class="mt-12">
                    <i
                        style="color:#EAF0F1"
                        class="fas fa-spinner fa-spin fa-5x"
                    ></i>
                </span>
            </v-row>
        </div>
        <div v-else>
            <search-component></search-component>
            <alert-component></alert-component>
            <v-app-bar :color="navigationColor" fixed dense>
                <v-col
                    cols="1"
                    @click="returnToHome()"
                    style="cursor:pointer"
                    v-if="logo != null"
                >
                    <v-img link to="/" :src="logo" :lazy-src="logo"> </v-img>
                </v-col>
                <v-col cols="1" @click="returnToHome()" v-if="logo === null">
                    <v-icon dark v-if="this.$route.path != '/'">
                        mdi-home
                    </v-icon>
                </v-col>

                <v-spacer></v-spacer>
                <div v-if="isSettings === true">
                    <v-tabs
                        background-color="transparent"
                        centered
                        dense
                        dark
                        color="teal"
                    >
                        <v-tabs-slider color="teal"></v-tabs-slider>

                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-tab
                                    link
                                    to="/settings/prehled"
                                    href="#dashboard"
                                >
                                    <v-icon v-on="on"
                                        >mdi-view-dashboard</v-icon
                                    >
                                </v-tab>
                            </template>
                            <span>Dashboard</span>
                        </v-tooltip>

                        <v-tooltip bottom v-if="userRole != 'view'">
                            <template v-slot:activator="{ on }">
                                <v-tab
                                    link
                                    to="/settings/streams"
                                    href="#tab-2"
                                >
                                    <v-icon v-on="on"
                                        >mdi-television-guide</v-icon
                                    >
                                </v-tab>
                            </template>
                            <span>Kanály</span>
                        </v-tooltip>

                        <v-tooltip bottom v-if="userRole == 'admin'">
                            <template v-slot:activator="{ on }">
                                <v-tab link to="/settings/users" href="#tab-3">
                                    <v-icon v-on="on"
                                        >mdi-account-multiple-outline</v-icon
                                    >
                                </v-tab>
                            </template>
                            <span>Uživatelé</span>
                        </v-tooltip>

                        <v-tooltip bottom v-if="userRole == 'admin'">
                            <template v-slot:activator="{ on }">
                                <v-tab link to="/settings/alerts" href="#tab-3">
                                    <v-icon v-on="on">mdi-bell-outline</v-icon>
                                </v-tab>
                            </template>
                            <span>Nastavení Alertingu</span>
                        </v-tooltip>

                        <v-tooltip bottom v-if="userRole == 'admin'">
                            <template v-slot:activator="{ on }">
                                <v-tab link to="/settings/cron" href="#tab-3">
                                    <v-icon v-on="on">mdi-clock-fast</v-icon>
                                </v-tab>
                            </template>
                            <span>CRON</span>
                        </v-tooltip>

                        <v-tooltip bottom v-if="userRole == 'admin'">
                            <template v-slot:activator="{ on }">
                                <v-tab link to="/settings/logo" href="#tab-3">
                                    <v-icon v-on="on">mdi-file-image</v-icon>
                                </v-tab>
                            </template>
                            <span>Logo Společnosti</span>
                        </v-tooltip>
                    </v-tabs>
                </div>
                <v-spacer></v-spacer>
                <!-- user Part -->
                <template>
                    <v-menu transition="scroll-y-transition" class="body-1">
                        <template v-slot:activator="{ on }">
                            <v-btn class="white--text" fab icon v-on="on">
                                <v-avatar color="transparent" small>
                                    <span v-if="todayEventsCount != '0'">
                                        <v-badge
                                            bottom
                                            color="#65C9FF"
                                            :content="todayEventsCount"
                                            offset-x="10"
                                            offset-y="10"
                                        >
                                            <v-icon dark>
                                                mdi-account
                                            </v-icon>
                                        </v-badge>
                                    </span>
                                    <span v-else>
                                        <v-icon dark>
                                            mdi-account
                                        </v-icon>
                                    </span>
                                </v-avatar>
                            </v-btn>
                        </template>
                        <v-list width="250px" class="text-center subtitle-2">
                            <v-list-item link to="/user/prehled">
                                Editace <v-spacer></v-spacer
                                ><v-icon color="#DAE0E2" right small
                                    >mdi-account-cog-outline</v-icon
                                >
                            </v-list-item>
                            <v-list-item
                                v-show="userRole != '4'"
                                link
                                to="/settings/prehled"
                            >
                                Nastavení<v-spacer></v-spacer
                                ><v-icon color="#00CCCD" right small
                                    >mdi-settings</v-icon
                                >
                            </v-list-item>
                            <v-divider></v-divider>
                            <v-list-item @click="logOut()">
                                Odhlásit se <v-spacer></v-spacer
                                ><v-icon color="red" right small
                                    >mdi-lock</v-icon
                                >
                            </v-list-item>

                            <v-divider
                                v-show="isIptvDoku == 'success'"
                            ></v-divider>
                            <v-list-item
                                v-show="isIptvDoku == 'success'"
                                href="http://iptvdoku.grapesc.cz/#/"
                                target="_blink"
                            >
                                IPTV Dokumentace<v-spacer></v-spacer
                                ><v-icon color="grey" right small
                                    >mdi-television</v-icon
                                >
                            </v-list-item>
                            <v-list-item
                                href="http://10.239.254.130/#/"
                                target="_blink"
                            >
                                Transcoder kontroler<v-spacer></v-spacer
                                ><v-icon color="grey" right small
                                    >mdi-television</v-icon
                                >
                            </v-list-item>
                            <!-- udalosti v daný den -->
                            <div v-if="todayEvents != null">
                                <v-divider></v-divider>
                                <v-list-item
                                    v-for="todayEvent in todayEvents"
                                    :key="todayEvent.id"
                                >
                                    <small class="orange--text">
                                        <strong
                                            >Na dnešní den je plánovaný výpadek
                                            {{ todayEvent.stream }}</strong
                                        > </small
                                    ><v-spacer></v-spacer
                                    ><v-icon color="orange" right small
                                        >mdi-calendar</v-icon
                                    >
                                </v-list-item>
                            </div>
                        </v-list>
                    </v-menu>
                </template>
                <!-- end User Part -->
            </v-app-bar>
        </div>

        <transition name="fade" mode="out-in">
            <router-view class="mt-1"> </router-view>
        </transition>
        <v-snackbar
            v-if="internetConnection === false"
            :timeout="-1"
            :value="true"
            absolute
            bottom
            color="warning"
            left
            class="text--center"
        >
            <v-icon>
                mdi-wifi
            </v-icon>
            <strong class="ml-12">
                Nejste Online
            </strong>
        </v-snackbar>
        <toolbaralert-component v-if="this.$route.path != '/'"></toolbaralert-component>
    </v-main>
</template>

<script>
import SearchComponent from "./Main/Search/SearchComponent";
import AlertComponent from "./AlertComponent";
import ToolBarAlertComponent from "./ToolBarAlertComponent";
export default {
    data: () => ({
        logo: null,
        internetConnection: true,
        loadingApp: true,
        status: null,
        dark: true,
        navigationColor: "#000000",
        todayEvents: null,
        todayEventsCount: "0",
        iptvDokuStatus: null,
        isIptvDoku: null,
        drawer: null,
        userMenu: null,
        alerts: [],
        alertCount: "0",
        newNotifications: [],
        userRole: null,
        loggedUser: null,
        descriptionLimit: 60,
        isLoading: false,
        isSettings: false
    }),

    components: {
        "search-component": SearchComponent,
        "alert-component": AlertComponent,
        "toolbaralert-component": ToolBarAlertComponent
    },
    created() {
        this.loadCompanyLogo();
        this.check_settings_route();
        this.checkIfisOnline();
        this.loadUser();
        setTimeout(
            function() {
                this.testConnectionToIptvDoku();
            }.bind(this),
            1000
        );

        this.loadTodayEvents();
    },
    methods: {
        loadCompanyLogo() {
            axios.get("system/logo").then(resposne => {
                if (resposne.data.length > 0) {
                    this.logo = resposne.data[0].logo_path;
                } else {
                    this.logo = null;
                }
            });
        },

        check_settings_route() {
            if (this.$route.path.includes("settings")) {
                return (this.isSettings = true);
            }

            return (this.isSettings = false);
        },

        saveNewThemeOption(theme) {
            axios
                .post("user/theme", {
                    isDark: theme
                })
                .then(response => {
                    this.status = response.data.status;

                    setTimeout(function() {
                        this.status = null;
                    }, 5000);
                });
        },

        changeTheme(currentTheme) {
            // nyni je dark mode => zmena na light mode
            if (currentTheme === true) {
                this.navigationColor = "#758AA2";
                this.$vuetify.theme.dark = false;
                this.dark = false;
            } else {
                this.navigationColor = "#121212";
                this.$vuetify.theme.dark = true;
                this.dark = true;
            }
        },
        async loadTodayEvents() {
            try {
                await axios.get("todayEvents").then(response => {
                    if (response.data.status == "empty") {
                        this.todayEvents = null;
                        this.todayEventsCount = "0";
                    } else {
                        this.todayEvents = response.data;
                        this.todayEventsCount = response.data.length;
                    }
                });
            } catch (error) {}
        },

        async testConnectionToIptvDoku() {
            try {
                await axios
                    .get("/api/iptvdoku/testConnection")
                    .then(response => {
                        this.$store.state.iptvDokuConnectionStatus =
                            response.data;
                        if (this.isIptvDoku != "success") {
                            this.isIptvDoku = response.data;
                            this.iptvDokuStatus = response.data;
                            setTimeout(function() {
                                this.iptvDokuStatus = null;
                            }, 5000);
                        }
                    });
            } catch (error) {}
        },
        returnToHome() {
            if (this.$route.path != "/") {
                this.$router.push("/");
            }
        },
        logOut() {
            axios.get("logout").then(response => {
                if (response.data.status === "success") {
                    this.$router.push("/login");
                }
            });
        },

        loadUser() {
            axios.get("user").then(response => {
                if (response.data.status == "error") {
                    if (this.$route.path != "/login") {
                        this.$router.push("/login");
                    }
                } else {
                    this.$store.state.loggedUser = this.loggedUser =
                        response.data;
                    this.userRole = response.data.role_id;
                    // při úspěšném načtení informací o uzivateli, se vypne nacitaci animace
                    this.loadingApp = false;
                }
            });
        },

        async checkIfisOnline() {
            try {
                this.internetConnection = window.navigator.onLine;
            } catch (error) {}
        }
    },
    mounted() {
        setInterval(
            function() {
                try {
                    this.checkIfisOnline();
                    this.loadUser();
                } catch (error) {}
            }.bind(this),
            2000
        );

        setInterval(function() {
            try {
                this.testConnectionToIptvDoku;
            } catch (error) {}
        }, 60000);
    },
    watch: {
        internetConnection: function() {
            if (this.internetConnection === false) {
                // this.networkChangeNotification = true;
            } else {
                this.networkChangeNotification = false;
            }
        },
        $route(to, from) {
            this.check_settings_route();
        }
    }
};
</script>
