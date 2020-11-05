<template>
    <v-app>
        <v-app-bar color="#121212" fixed dense>
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

            <template v-if="$vuetify.breakpoint.smAndUp">
                <v-menu transition="scroll-y-transition">
                    <template v-slot:activator="{ on }">
                        <v-btn class="white--text" fab icon v-on="on">
                            <v-avatar color="transparent" small>
                                <span v-if="todayEventsCount != '0'">
                                    <v-badge
                                        bordered
                                        bottom
                                        color="blue"
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
                            ><v-icon color="grey" right small
                                >mdi-account-cog-outline</v-icon
                            >
                        </v-list-item>
                        <v-list-item
                            v-show="userRole != '4'"
                            link
                            to="/settings/prehled"
                        >
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

                        <v-divider v-show="isIptvDoku == 'success'"></v-divider>
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
        <!-- iptv doku -->
        <v-snackbar
            v-if="iptvDokuStatus != null"
            :value="iptvDokuStatus"
            color="primary"
            absolute
            right
            rounded="pill"
            bottom
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
        todayEvents: null,
        todayEventsCount: "0",
        iptvDokuStatus: null,
        isIptvDoku: null,
        drawer: null,
        userMenu: null,
        alerts: [],
        alertCount: "",
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
        this.loadAlerts();
        this.loadUser();
        this.testConnectionToIptvDoku();
        this.loadTodayEvents();
    },
    methods: {
        loadTodayEvents() {
            window.axios.get("todayEvents").then(response => {
                if (response.data.status == "empty") {
                    this.todayEvents = null;
                    this.todayEventsCount = "0";
                } else {
                    this.todayEvents = response.data;
                    this.todayEventsCount = response.data.length;
                }
            });
        },

        testConnectionToIptvDoku() {
            window.axios.get("/api/iptvdoku/testConnection").then(response => {
                this.$store.state.iptvDokuConnectionStatus = response.data;
                if (this.isIptvDoku != "success") {
                    this.isIptvDoku = response.data;
                    this.iptvDokuStatus = response.data;
                    setTimeout(function() {
                        this.iptvDokuStatus = null;
                    }, 5000);
                }
            });
        },
        loadAlerts() {
            window.axios.get("/streamAlerts").then(response => {
                this.alerts = response.data;
                this.$store.commit("updateAlert", response.data);
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
                    currentObj.$store.state.loggedUser = currentObj.loggedUser =
                        response.data;
                    currentObj.userRole = response.data.role_id;
                }
            });
        }
    },

    mounted() {
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
