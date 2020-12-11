<template>
    <v-app>
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
            <v-app-bar :color="navigationColor" fixed dense>
                <div v-if="this.$route.path != '/'">
                    <v-btn link to="/" color="white" class="white--text" icon>
                        <v-icon>mdi-home</v-icon>
                    </v-btn>
                </div>
                <v-spacer></v-spacer>

                <v-autocomplete
                    v-model="model"
                    :items="items"
                    :loading="isLoading"
                    :search-input.sync="search"
                    color="white"
                    hide-no-data
                    dense
                    filled
                    rounded
                    class="mt-6"
                    hide-selected
                    item-text="result"
                    item-value="url"
                    placeholder="Vyhledejte v aplikaci ... "
                    prepend-inner-icon="mdi-database-search"
                    return-object
                >
                </v-autocomplete>

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
                            <!-- theme change -->
                            <v-list-item>
                                <v-list-item-content>
                                    Dark mode<v-spacer></v-spacer>
                                </v-list-item-content>

                                <v-switch
                                    dense
                                    v-model="dark"
                                    @click="
                                        changeTheme($vuetify.theme.dark),
                                            saveNewThemeOption(
                                                $vuetify.theme.dark
                                            )
                                    "
                                />
                            </v-list-item>
                            <!-- end theme change -->
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
                                Nastavení App<v-spacer></v-spacer
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
                <v-badge
                    bottom
                    color="#65C9FF"
                    :content="alertCount"
                    offset-x="10"
                    offset-y="10"
                >
                    <v-icon color="white" @click="drawer = !drawer"
                        >mdi-bell-outline</v-icon
                    >
                </v-badge>
            </v-app-bar>
        </div>
        <v-navigation-drawer
            v-model="drawer"
            right
            fixed
            temporary
            color="transparent"
        >
            <div v-if="alertCount != '0'">
                <div
                    id="alerty"
                    class="pl-2 pr-2"
                    v-for="alert in alerts"
                    :key="alert.id"
                >
                    <v-alert
                        v-if="alertCount != '0'"
                        dense
                        border="left"
                        :type="alert.status"
                        class="body-2 mt-2"
                    >
                        <strong>{{ alert.msg }}</strong>
                        <div v-show="alert.data">
                            <v-row
                                class="ml-1"
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
            </div>
            <!--  -->
            <div v-else>
                <div class="pl-2 pr-2">
                    <v-alert
                        transition="slide-x-transition"
                        dense
                        border="left"
                        type="success"
                        class="body-2 mt-2"
                    >
                        <strong
                            >Všechny dohledované streamy jsou funknčí</strong
                        >
                    </v-alert>
                </div>
            </div>
        </v-navigation-drawer>

        <transition name="fade" mode="out-in">
            <router-view class="mt-1"> </router-view>
        </transition>
        <!-- iptv doku -->

        <v-snackbar
            transition="scroll-x-reverse-transition"
            v-if="iptvDokuStatus != null"
            :value="iptvDokuStatus"
            color="primary"
            class="mt-12"
            absolute
            right
            rounded="pill"
            top
        >
            <span class="ml-12" v-if="iptvDokuStatus == 'success'">
                Připojeno k IPTV dokumentaci
            </span>
            <span class="ml-12" v-if="iptvDokuStatus == 'error'">
                Nepodařilo se připojit k IPTV dokumentaci
            </span>
        </v-snackbar>
    </v-app>
</template>

<script>
export default {
    data: () => ({
        loadingApp: true,
        status: null,
        dark: true,
        navigationColor: "#121212",
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
        entries: [],
        isLoading: false,
        model: null,
        search: null,
        items: []
    }),

    computed: {
        fields() {
            if (!this.model) return [];

            return Object.keys(this.model).map(key => {
                return {
                    key,
                    value: this.model[key] || "n/a"
                };
            });
        }
    },

    created() {
        this.notifywhenisloggedtoconsole();
        this.loadAlerts();
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
        saveNewThemeOption(theme) {
            let currentObj = this;
            axios
                .post("user/theme", {
                    isDark: theme
                })
                .then(function(response) {
                    currentObj.status = response.data.status;

                    setTimeout(function() {
                        currentObj.status = null;
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
        async loadAlerts() {
            try {
                await axios.get("/streamAlerts").then(response => {
                    if (response.data.length === 0) {
                        this.alerts = response.data;
                        this.$store.commit("updateAlert", response.data);
                        this.alertCount = "0";
                    } else {
                        this.alerts = response.data;
                        this.$store.commit("updateAlert", response.data);
                        this.alertCount = response.data.length;
                    }
                });
            } catch (error) {}
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
                    currentObj.$store.state.loggedUser = currentObj.loggedUser =
                        response.data;
                    currentObj.userRole = response.data.role_id;
                    // při úspěšném načtení informací o uzivateli, se vypne nacitaci animace
                    currentObj.loadingApp = false;
                }
            });
        },

        notifywhenisloggedtoconsole() {
            console.log("II  PPPPPPPP  TTTTTTTT  VV        VV   DDDDDDDD     OOOOOOOO  HH    HH  LL      EEEEEEEE  DDDDDDDD");
            console.log("II  PP   PPP     TT      VV     VV     DD      DD   OO    OO  HH    HH  LL      EE        DD     DD");
            console.log("II  PPPPPPP      TT       VV   VV      DD       DD  OO    OO  HHHHHHHH  LL      EEEEEEEE  DD       DD");
            console.log("II  PP           TT        VV VV       DD      DD   OO    OO  HH    HH  LL      EE        DD      DD");
            console.log("II  PP           TT         VV         DDDDDDDD     OOOOOOOO  HH    HH  LLLLLL  EEEEEEEE  DDDDDDDD");
        }

        // checkTheme() {
        //     if (this.$vuetify.theme.isDark === true) {

        //         this.navigationColor = "#121212";
        //     } else {

        //     }
        // }
    },
    mounted() {
        // this.checkTheme()
        setInterval(
            function() {
                try {
                    this.loadAlerts();
                } catch (error) {}
            }.bind(this),
            2000
        );

        setInterval(
            function() {
                try {
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
        search() {
            if (this.items.length > 0) return;

            this.isLoading = true;

            let currentObj = this;
            setTimeout(() => {
                window.axios
                    .post("search", {
                        search: this.search
                    })
                    .then(function(response) {
                        currentObj.items = response.data;
                    })
                    .finally(() => (this.isLoading = false));
            }, 500);
        },
        model() {
            if (this.model == undefined) {
                // nic
            } else {
                this.$router.push("/" + this.model.url);
                this.model = null;
                this.items = [];
            }
        }
    }
};
</script>
