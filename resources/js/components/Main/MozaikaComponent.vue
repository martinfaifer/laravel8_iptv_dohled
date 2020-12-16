<template>
    <v-main class="mt-12">
        <v-container fluid>
            <v-row>
                <!-- načtení komponentu pro statickou část mozaiky -->
                <staticmozaika-component
                    v-if="loggedUser.mozaika == 'custom'"
                    :usermozaika="loggedUser.customData"
                ></staticmozaika-component>

                <v-row class="mx-auto mt-1 ma-1 mr-1">
                    <v-col
                        v-for="stream in streams"
                        :key="stream.id"
                        class="mt-2"
                    >
                        <v-hover v-slot:default="{ hover }">
                            <v-card
                                link
                                :to="'stream/' + stream.id"
                                :elevation="hover ? 12 : 0"
                                class="mx-auto ma-0 transition-fast-in-fast-out"
                                height="143"
                                width="250"
                                :class="{
                                    'green darken-1':
                                        stream.status == 'success',
                                    'green darken-3':
                                        stream.status == 'diagnostic_crash',
                                    'red darken-4': stream.status == 'error',
                                    'deep-orange accent-1':
                                        stream.status == 'issue',
                                    '#202020': stream.status == 'waiting'
                                }"
                                @contextmenu="show($event, stream.id)"
                            >
                                <!-- mdi-loading -->
                                <!-- status stream waiting -->
                                <v-img
                                    v-if="stream.status == 'waiting'"
                                    :elevation="hover ? 24 : 0"
                                    class="transition-fast-in-fast-out"
                                >
                                    <v-row
                                        class="fill-height ma-0 mt-12"
                                        justify="center"
                                    >
                                        <v-progress-circular
                                            indeterminate
                                            color="blue lighten-2"
                                        ></v-progress-circular>
                                        <div class="ml-2">
                                            {{ stream.nazev }}
                                            <v-row>
                                                <small class="ml-3 blue--text">
                                                    čeká na zpracování ...
                                                </small>
                                            </v-row>
                                        </div>
                                    </v-row>
                                </v-img>

                                <v-img
                                    v-else-if="stream.image == 'false'"
                                    :elevation="hover ? 24 : 0"
                                    class="transition-fast-in-fast-out"
                                >
                                    <v-row
                                        class="fill-height ma-0 mt-12"
                                        justify="center"
                                    >
                                        <div class="ml-2">
                                            {{ stream.nazev }}
                                            <v-row
                                                v-if="
                                                    stream.status ==
                                                        'success' ||
                                                        stream.status ==
                                                            'issue' ||
                                                        stream.status ==
                                                            'diagnostic_crash'
                                                "
                                            >
                                                <small class="ml-3 white--text">
                                                    <strong>
                                                        čeká se na vytvoření
                                                        náhledu ...
                                                    </strong>
                                                </small>
                                            </v-row>
                                            <v-row
                                                v-if="stream.status == 'error'"
                                            >
                                                <small class="ml-3 white--text">
                                                    <strong>
                                                        stream je ve výpadku ...
                                                    </strong>
                                                </small>
                                            </v-row>
                                        </div>
                                    </v-row>
                                </v-img>

                                <v-img
                                    v-else
                                    :lazy-src="stream.image"
                                    :src="stream.image"
                                    :aspect-ratio="16 / 9"
                                >
                                    <v-expand-transition>
                                        <div
                                            v-if="hover"
                                            class="d-flex transition-fast-in-fast-out grey darken-4 v-card--reveal display-1 white--text"
                                            style="height: 100%;"
                                        >
                                            {{ stream.nazev }}
                                        </div>
                                    </v-expand-transition>
                                </v-img>
                            </v-card>
                        </v-hover>

                        <!-- menu -->
                        <v-menu
                            class="body-2 elevation-0"
                            dense
                            v-model="showMenu"
                            :position-x="x"
                            :position-y="y"
                            absolute
                            offset-y
                        >
                            <v-list dense>
                                <v-list-item @click="OpenLightDetailDialog">
                                    <v-list-item-icon>
                                        <v-icon color="#01CBC6" x-small
                                            >mdi-magnify</v-icon
                                        >
                                    </v-list-item-icon>
                                    <v-list-item-title>
                                        Zobrazit rychlí náhled
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="OpenSmallEditDialog" v-show="loggedUser.role_id != '4'">
                                    <v-list-item-icon>
                                        <v-icon color="#1287A5" x-small
                                            >mdi-pencil</v-icon
                                        >
                                    </v-list-item-icon>
                                    <v-list-item-title>
                                        Upravit popis
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item disabled>
                                    <v-list-item-icon>
                                        <v-icon color="#EAF0F1" x-small
                                            >mdi-calendar-blank</v-icon
                                        >
                                    </v-list-item-icon>
                                    <v-list-item-title>
                                        Přidat událost
                                    </v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                        <!-- konec menu -->
                    </v-col>
                </v-row>
            </v-row>
        </v-container>

        <!-- Dialogy -->

        <v-row justify="center">
            <v-dialog v-model="detailDialog" persistent max-width="900px">
                <v-card>
                    <v-card-text class="pt-6">
                        <div v-if="loading === true">
                            <!-- loading animace -->
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
                            <div
                                v-if="stream.service.pcrpid != null"
                                class="text-start"
                            >
                                <span>
                                    <strong>
                                        Servisní informace o streamu:
                                    </strong>
                                </span>
                                <br />
                                <v-card color="transparent" flat>
                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    PcrPid:
                                                    <span class="ml-3">
                                                        {{
                                                            stream.service
                                                                .pcrpid
                                                        }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >
                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    PmtPid:
                                                    <span class="ml-3">
                                                        {{
                                                            stream.service
                                                                .pmtpid
                                                        }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >
                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    TSid:
                                                    <span class="ml-3">
                                                        {{
                                                            stream.service.tsid
                                                        }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >
                                    </v-list-item>
                                </v-card>
                            </div>
                            <div
                                v-if="stream.audio != null"
                                class="text-start mt-6"
                            >
                                <span>
                                    <strong>
                                        Audio
                                    </strong>
                                </span>
                                <br />
                                <v-card color="transparent" flat>
                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    Discontinuity Errory:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.audio
                                                                    .discontinuities ==
                                                                '0',
                                                            'red--text':
                                                                stream.audio
                                                                    .discontinuities !=
                                                                '0'
                                                        }"
                                                    >
                                                        {{
                                                            stream.audio
                                                                .discontinuities
                                                        }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >

                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    Scrambled:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.audio
                                                                    .scrambled ==
                                                                '0',
                                                            'red--text':
                                                                stream.audio
                                                                    .scrambled !=
                                                                '0'
                                                        }"
                                                    >
                                                        {{
                                                            stream.audio
                                                                .scrambled
                                                        }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >
                                    </v-list-item>

                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    PID:
                                                    <span class="ml-3">
                                                        {{ stream.audio.pid }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >

                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    Jazyková stopa:
                                                    <span class="ml-3">
                                                        {{
                                                            stream.audio
                                                                .language
                                                        }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >
                                    </v-list-item>

                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    Bitrate:
                                                    <span class="ml-3">
                                                        {{
                                                            Math.round(
                                                                stream.audio
                                                                    .bitrate /
                                                                    1024
                                                            )
                                                        }}
                                                        kbps
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >

                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    Dekryptace
                                                    <span class="ml-3">
                                                        <span
                                                            v-if="
                                                                stream.audio
                                                                    .audioAccess ==
                                                                    'success'
                                                            "
                                                            class="green--text"
                                                        >
                                                            Dekryptuje
                                                        </span>
                                                        <span
                                                            v-else
                                                            class="red--text"
                                                        >
                                                            Nedekryptuje
                                                        </span>
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >
                                    </v-list-item>
                                </v-card>
                            </div>

                            <div v-if="stream.video != null" class="text-start">
                                <span>
                                    <strong>
                                        Video
                                    </strong>
                                </span>
                                <br />
                                <v-card color="transparent" flat>
                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    Discontinuity Errory:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.video
                                                                    .discontinuities ==
                                                                '0',
                                                            'red--text':
                                                                stream.video
                                                                    .discontinuities !=
                                                                '0'
                                                        }"
                                                    >
                                                        {{
                                                            stream.video
                                                                .discontinuities
                                                        }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >

                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    Scrambled:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.video
                                                                    .scrambled ==
                                                                '0',
                                                            'red--text':
                                                                stream.video
                                                                    .scrambled !=
                                                                '0'
                                                        }"
                                                    >
                                                        {{
                                                            stream.video
                                                                .scrambled
                                                        }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >
                                    </v-list-item>

                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    Bitrate:
                                                    <span class="ml-3">
                                                        {{
                                                            Math.round(
                                                                (stream.video
                                                                    .bitrate /
                                                                    1048576) *
                                                                    100
                                                            ) / 100
                                                        }}
                                                        Mbps
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >

                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    Dekryptace
                                                    <span class="ml-3">
                                                        <span
                                                            v-if="
                                                                stream.video
                                                                    .access ==
                                                                    'success'
                                                            "
                                                            class="green--text"
                                                        >
                                                            Dekryptuje
                                                        </span>
                                                        <span
                                                            v-else
                                                            class="red--text"
                                                        >
                                                            Nedekryptuje
                                                        </span>
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >
                                    </v-list-item>

                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                <strong>
                                                    PID:
                                                    <span class="ml-3">
                                                        {{ stream.video.pid }}
                                                    </span>
                                                </strong>
                                            </small></v-list-item
                                        >
                                    </v-list-item>
                                </v-card>
                            </div>
                        </div>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="green darken-1"
                            text
                            @click="closeLightDetailDialog()"
                        >
                            Zavřít
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>

        <!-- Editace Dialog -->

        <v-row justify="center" v-if="smallEditStreamData != null">
            <v-dialog v-model="smallEditDialog" persistent max-width="1000px">
                <v-card>
                    <v-card-title>
                        <span class="headline text-center"
                            >Editace streamu</span
                        >
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col>
                                    <v-text-field
                                        label="Název kanálu"
                                        v-model="
                                            smallEditStreamData.stream_name
                                        "
                                        required
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="red darken-1" text @click="closeDialog()">
                            Zavřít
                        </v-btn>
                        <v-btn color="green darken-1" text @click="saveEdit()">
                            Editovat
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>

        <!-- konec editace dialogu -->

        <!-- konec dialogů -->

        <v-container class="text-center">
            <v-pagination
                v-model="pagination.current"
                :length="pagination.total"
                @input="onPageChange"
                circle
            ></v-pagination>
        </v-container>
    </v-main>
</template>

<script>
import StaticMozaika from "./MozaikaStatic/MozaikaStaticComponent";
export default {
    computed: {
        loggedUser() {
            return this.$store.state.loggedUser;
        }
    },
    data: () => ({
        smallEditDialog: false,
        smallEditStreamData: null,
        detailDialog: false,
        streamId: null,
        showMenu: false,
        x: 0,
        y: 0,
        streams: null,
        streamWeb: "",
        pagination: {
            current: 1,
            total: 0
        },
        loading: true,
        stream: {
            video: {
                bitrate: null,
                pid: null,
                discontinuities: null,
                scrambled: null,
                access: null
            },
            audio: {
                bitrate: null,
                pid: null,
                discontinuities: null,
                scrambled: null,
                audioLanguage: null,
                audioAccess: null,
                access: null
            },
            ca: {
                access: null,
                scrambled: null,
                description: null
            },
            service: {
                pcrpid: null,
                pmtpid: null,
                tsid: null
            }
        }
    }),
    components: {
        "staticmozaika-component": StaticMozaika
    },

    created() {
        this.getStreams();
        this.smallEditStreamData = null;
    },
    methods: {
        saveEdit() {
            window.axios
                .post("stream/mozaika/edit/save", {
                    streamId: this.streamId,
                    streamName: this.smallEditStreamData.stream_name
                })
                .then(response => {
                    if (response.data.status === "success") {
                        this.smallEditDialog = false;
                    }
                });
        },
        closeDialog() {
            this.smallEditDialog = false;
        },
        OpenSmallEditDialog() {
            window.axios
                .post("stream/get_name_and_dohled", {
                    streamId: this.streamId
                })
                .then(response => {
                    if (response.data.status === "success") {
                        this.smallEditStreamData = response.data;
                        this.smallEditDialog = true;
                    }
                });
        },
        OpenLightDetailDialog() {
            this.detailDialog = true;

            // open socket connection
            Echo.channel("streamInfoTsVideoBitrate" + this.streamId).listen(
                "StreamInfoTsVideoBitrate",
                eVideo => {
                    this.stream.video.bitrate = eVideo["bitrate"];

                    this.stream.video.pid = eVideo["videoPid"];
                    this.stream.video.discontinuities =
                        eVideo["discontinuities"];
                    this.stream.video.scrambled = eVideo["scrambled"];
                    this.stream.video.access = eVideo["access"];
                    this.loading = false;
                }
            );

            Echo.channel("streamInfoTsAudioBitrate" + this.streamId).listen(
                "StreamInfoAudioBitrate",
                eAudio => {
                    this.stream.audio.bitrate = eAudio["bitrate"];

                    this.stream.audio.pid = eAudio["audioPid"];
                    this.stream.audio.discontinuities =
                        eAudio["audioDiscontinuities"];
                    this.stream.audio.scrambled = eAudio["audioScrambled"];
                    this.stream.audio.audioLanguage = eAudio["audioLanguage"];
                    this.stream.audio.audioAccess = eAudio["audioAccess"];
                }
            );

            Echo.channel("streamInfoTsCa" + this.streamId).listen(
                "StreamInfoCa",
                eCA => {
                    this.stream.ca.access = eCA["access"];
                    this.stream.ca.description = eCA["description"];
                    this.stream.ca.scrambled = eCA["scrambled"];
                }
            );

            Echo.channel("streamInfoTsService" + this.streamId).listen(
                "StreamInfoService",
                eService => {
                    this.stream.service.pcrpid = eService["pcrpid"];
                    this.stream.service.pmtpid = eService["pmtpid"];
                    this.stream.service.tsid = eService["tsid"];
                }
            );
        },

        closeLightDetailDialog() {
            // leave channels
            Echo.leaveChannel("streamInfoTsVideoBitrate" + this.streamId);
            Echo.leaveChannel("streamInfoTsAudioBitrate" + this.streamId);
            Echo.leaveChannel("streamInfoTsCa" + this.streamId);
            Echo.leaveChannel("streamInfoTsService" + this.streamId);
            this.streamId = null;
            this.detailDialog = false;
            this.loading = true;
        },
        show(e, streamId) {
            this.streamId = streamId;
            e.preventDefault();
            this.showMenu = false;
            this.x = e.clientX;
            this.y = e.clientY;
            this.$nextTick(() => {
                this.showMenu = true;
            });
        },
        async getStreams() {
            try {
                await axios
                    .get("pagination?page=" + this.pagination.current)
                    .then(response => {
                        this.streams = response.data.data;
                        this.pagination.current = response.data.current_page;
                        this.pagination.total = response.data.last_page;
                    });
            } catch (error) {}
        },
        onPageChange() {
            this.getStreams();
        }
    },

    mounted() {
        setInterval(
            function() {
                try {
                    this.getStreams();
                } catch (error) {}
            }.bind(this),
            2000
        );

        setInterval(
            function() {
                if (this.pagination.current <= this.pagination.total - 1) {
                    this.pagination.current = this.pagination.current + 1;
                    this.getStreams();
                } else {
                    this.pagination.current = 1;
                    this.getStreams();
                }
            }.bind(this),
            30000
        );
    },
    watch: {}
};
</script>
