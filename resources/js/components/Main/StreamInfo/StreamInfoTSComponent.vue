<template>
    <v-main>
        <v-card
            absolute
            height="100%"
            width="100%"
            class="text-center pl-10 pr-10"
            color="transparent"
            flat
        >
            <v-container>
                <!-- loading -->

                <div v-if="loading === true && websocketAlert === null">
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
                <div
                    v-else-if="loading === true && websocketAlert === 'alert'"
                    class="mt-12"
                >
                    <v-alert dense text type="error" border="left">
                        Stream nepodporuje pokročilou diagnostiku
                    </v-alert>
                </div>

                <div v-else>
                    <!-- Servisní informace o streamu -->
                    <div
                        v-if="stream.service.pcrpid != null"
                        class="text-start"
                    >
                        <span>
                            Servisní informace a metadata o streamu:
                        </span>
                        <br />
                        <v-card color="transparent" flat>
                            <v-list-item>
                                <v-list-item>
                                    <small>
                                        PcrPid:
                                        <span class="ml-3">
                                            <strong>
                                                {{ stream.service.pcrpid }}
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >
                                <v-list-item>
                                    <small>
                                        PmtPid:
                                        <span class="ml-3">
                                            <strong>
                                                {{ stream.service.pmtpid }}
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >
                                <v-list-item>
                                    <small>
                                        TSid:
                                        <span class="ml-3">
                                            <strong>
                                                {{ stream.service.tsid }}
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >
                            </v-list-item>

                            <v-list-item>
                                <v-list-item v-if="stream.ts.country != null">
                                    <small>
                                        Země původu:
                                        <span class="ml-3">
                                            <strong>
                                                {{ stream.ts.country }}
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >

                                <v-list-item
                                    v-if="stream.service.provider != null"
                                >
                                    <small>
                                        Provider:
                                        <span class="ml-3">
                                            <strong>
                                                {{ stream.service.provider }}
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >

                                <v-list-item v-if="stream.service.name != null">
                                    <small>
                                        Název
                                        <span class="ml-3">
                                            <strong>
                                                {{ stream.service.name }}
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >
                            </v-list-item>
                        </v-card>
                    </div>
                    <!-- CA Modul-->
                    <div
                        v-if="stream.ca.access != null"
                        class="text-start mt-6"
                    >
                        <span>
                            Informace o CA Modulu:
                        </span>
                        <br />
                        <v-card color="transparent" flat>
                            <v-list-item>
                                <v-list-item>
                                    <small>
                                        Dekryptace:
                                        <span
                                            class="ml-3"
                                            :class="{
                                                'green--text':
                                                    stream.ca.access ==
                                                    'success',
                                                'red--text':
                                                    stream.ca.access !=
                                                    'success'
                                            }"
                                        >
                                            <strong
                                                v-if="
                                                    stream.ca.access ==
                                                        'success'
                                                "
                                            >
                                                Dekryptuje
                                            </strong>
                                            <strong v-else>
                                                Nedekryptuje
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >
                                <v-list-item>
                                    <small>
                                        Scrambled:
                                        <span
                                            class="ml-3"
                                            :class="{
                                                'green--text':
                                                    stream.ca.scrambled == '0',
                                                'red--text':
                                                    stream.ca.scrambled != '0'
                                            }"
                                        >
                                            <strong>
                                                {{ stream.ca.scrambled }}
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >
                            </v-list-item>
                            <v-list-item v-if="stream.ca.description != null">
                                <v-list-item>
                                    <small>
                                        Typ:
                                        <span class="ml-3">
                                            <strong>
                                                {{ stream.ca.description }}
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >
                            </v-list-item>
                        </v-card>
                    </div>
                    <v-divider></v-divider>
                    <v-row no-gutters>
                        <v-col class="col-6">
                            <!-- audio -->
                            <div
                                v-if="stream.audio.pid != null"
                                class="text-start mt-6"
                            >
                                <span>
                                    Audio bitrate:
                                    <strong>
                                        {{
                                            Math.round(
                                                stream.audio.bitrate / 1024
                                            )
                                        }}
                                        kbps
                                    </strong>
                                </span>
                                <br />
                                <v-card color="transparent" flat>
                                    <v-list-item>
                                        <v-list-item>
                                            <small>
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
                                                    <strong>
                                                        {{
                                                            stream.audio
                                                                .discontinuities
                                                        }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >

                                        <v-list-item>
                                            <small>
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
                                                    <strong>
                                                        {{
                                                            stream.audio
                                                                .scrambled
                                                        }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >
                                    </v-list-item>

                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                PID:
                                                <span class="ml-3">
                                                    <strong>
                                                        {{ stream.audio.pid }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >

                                        <v-list-item>
                                            <small>
                                                Jazyková stopa:
                                                <span class="ml-3">
                                                    <strong>
                                                        {{
                                                            stream.audio
                                                                .audioLanguage
                                                        }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >
                                    </v-list-item>

                                    <v-list-item>
                                        <v-list-item
                                            v-if="
                                                stream.audio.description != null
                                            "
                                        >
                                            <small>
                                                Popis pidu:
                                                <span class="ml-3">
                                                    <strong>
                                                        {{
                                                            stream.audio
                                                                .description
                                                        }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >
                                    </v-list-item>
                                </v-card>
                                <v-progress-linear
                                    class="mt-4"
                                    v-if="stream.audio.audioAccess == 'success'"
                                    :buffer-value="
                                        stream.audio.bitrate / '1048576'
                                    "
                                    v-model="stream.audio.bitrate / '1048576'"
                                    color="success"
                                    stream
                                >
                                </v-progress-linear>
                                <v-progress-linear
                                    class="mt-4"
                                    v-else
                                    v-model="stream.audio.bitrate / '1048576'"
                                    :buffer-value="
                                        stream.audio.bitrate / '1048576'
                                    "
                                    color="error"
                                    stream
                                >
                                </v-progress-linear>

                                <v-card flat color="transparent" dense>
                                    <v-toolbar
                                        dense
                                        color="transparent"
                                        flat
                                        class="elevation-0"
                                    >
                                        <template v-slot:extension>
                                            <v-tabs dense v-model="audioTab">
                                                <v-tabs-slider
                                                    dense
                                                    color="primary"
                                                ></v-tabs-slider>

                                                <v-tab>
                                                    Audio bitrate
                                                </v-tab>
                                                <v-tab>
                                                    CC chyby
                                                </v-tab>
                                            </v-tabs>
                                        </template>
                                    </v-toolbar>
                                    <v-tabs-items
                                        v-model="audioTab"
                                        color="transparent"
                                    >
                                        <v-tab-item v-show="audioTab == 0">
                                            <v-card
                                                flat
                                                v-show="
                                                    audioBitrateChartArray.length >
                                                        1
                                                "
                                                color="transparent"
                                            >
                                                <v-sheet color="transparent">
                                                    <!-- audio bitrate chart -->
                                                    <v-sparkline
                                                        height="100%"
                                                        fill
                                                        :auto-draw-duration="
                                                            drawDuration
                                                        "
                                                        :label-size="labelSize"

                                                        :smooth="16"
                                                        type="trend"
                                                        :labels="audioLabels"
                                                        :gradient="[
                                                            'rgba(0, 136, 255, 0.2)'
                                                        ]"
                                                        :line-width="lineWidth"
                                                        :show-labels="labels"
                                                        :value="
                                                            audioBitrateChartArray
                                                        "
                                                        stroke-linecap="round"
                                                    ></v-sparkline>
                                                </v-sheet>
                                            </v-card>
                                        </v-tab-item>
                                        <v-tab-item v-show="audioTab == 1">
                                            <v-card
                                                v-if="
                                                    ccErorrs.status === 'exist'
                                                "
                                                flat
                                                color="transparent"
                                                class="elevation-0"
                                            >
                                                <v-sparkline
                                                    height="100%"
                                                    v-if="
                                                        ccErorrs.status ===
                                                            'exist'
                                                    "
                                                    :smooth="1"
                                                    :label-size="labelSize"
                                                    type="bar"
                                                    :gradient="['#ff3333']"
                                                    :line-width="1"
                                                    :value="ccErorrs.audio"
                                                    :labels="ccErorrs.audioTime"
                                                ></v-sparkline>
                                            </v-card>
                                            <v-card
                                                v-else
                                                flat
                                                color="transparent"
                                                class="elevation-0"
                                            >
                                                <v-alert
                                                    dense
                                                    text
                                                    type="error"
                                                    border="left"
                                                >
                                                    Neexistuje chybovost v audiu
                                                </v-alert>
                                            </v-card>
                                        </v-tab-item>
                                    </v-tabs-items>
                                </v-card>
                            </div>
                        </v-col>
                        <v-col class="col-6 pl-6">
                            <!-- video -->
                            <div
                                v-if="stream.video.pid != null"
                                class="text-start mt-6"
                            >
                                <span>
                                    Video bitrate:
                                    <strong>
                                        {{
                                            Math.round(
                                                (stream.video.bitrate /
                                                    1048576) *
                                                    100
                                            ) / 100
                                        }}
                                        Mbps
                                    </strong>
                                </span>
                                <br />
                                <v-card color="transparent" flat>
                                    <v-list-item>
                                        <v-list-item>
                                            <small>
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
                                                    <strong>
                                                        {{
                                                            stream.video
                                                                .discontinuities
                                                        }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >

                                        <v-list-item>
                                            <small>
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
                                                    <strong>
                                                        {{
                                                            stream.video
                                                                .scrambled
                                                        }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >
                                    </v-list-item>

                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                PID:
                                                <span class="ml-3">
                                                    <strong>
                                                        {{ stream.video.pid }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >
                                    </v-list-item>

                                    <v-list-item>
                                        <v-list-item
                                            v-if="
                                                stream.video.videoDescription !=
                                                    null
                                            "
                                        >
                                            <small>
                                                Popis pidu:
                                                <span class="ml-3">
                                                    <strong>
                                                        {{
                                                            stream.video
                                                                .videoDescription
                                                        }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >
                                    </v-list-item>
                                </v-card>
                                <v-progress-linear
                                    class="mt-4"
                                    v-if="stream.video.access == 'success'"
                                    v-model="stream.video.bitrate / '1048576'"
                                    :buffer-value="
                                        stream.video.bitrate / '1048576'
                                    "
                                    color="success"
                                    stream
                                >
                                </v-progress-linear>

                                <v-progress-linear
                                    class="mt-4"
                                    v-else
                                    v-model="stream.video.bitrate / '1048576'"
                                    :buffer-value="
                                        stream.video.bitrate / '1048576'
                                    "
                                    color="error"
                                    stream
                                >
                                </v-progress-linear>

                                <!-- slide menu bar -->
                                <v-card flat color="transparent" dense>
                                    <v-toolbar
                                        dense
                                        color="transparent"
                                        flat
                                        class="elevation-0"
                                    >
                                        <template v-slot:extension>
                                            <v-tabs dense v-model="videoTab">
                                                <v-tabs-slider
                                                    dense
                                                    color="primary"
                                                ></v-tabs-slider>

                                                <v-tab>
                                                    Video bitrate
                                                </v-tab>
                                                <v-tab>
                                                    CC chyby
                                                </v-tab>
                                            </v-tabs>
                                        </template>
                                    </v-toolbar>
                                    <v-tabs-items
                                        v-model="videoTab"
                                        color="transparent"
                                    >
                                        <v-tab-item v-show="videoTab == 0">
                                            <v-card
                                                flat
                                                v-show="
                                                    videoBitrateChartArray.length >
                                                        1
                                                "
                                                color="transparent"
                                            >
                                                <v-sheet color="transparent">
                                                    <v-sparkline
                                                        height="104%"
                                                        fill
                                                        :auto-draw-duration="
                                                            drawDuration
                                                        "
                                                        :label-size="labelSize"
                                                        :smooth="16"
                                                        type="trend"
                                                        :labels="videoLabels"
                                                        :gradient="[
                                                            'rgba(0, 136, 255, 0.2)'
                                                        ]"
                                                        :line-width="lineWidth"
                                                        :show-labels="labels"
                                                        :value="
                                                            videoBitrateChartArray
                                                        "
                                                        stroke-linecap="round"
                                                    ></v-sparkline>
                                                </v-sheet>
                                            </v-card>
                                        </v-tab-item>
                                        <v-tab-item v-show="videoTab == 1">
                                            <v-card
                                                v-if="
                                                    ccErorrs.status === 'exist'
                                                "
                                                flat
                                                color="transparent"
                                                class="elevation-0"
                                            >
                                                <v-sparkline
                                                    height="108%"
                                                    v-if="
                                                        ccErorrs.status ===
                                                            'exist'
                                                    "
                                                    show-labels
                                                    :label-size="labelSize"
                                                    :smooth="1"
                                                    type="bar"
                                                    :gradient="['#ff3333']"
                                                    :line-width="1"
                                                    :value="ccErorrs.video"
                                                    :labels="ccErorrs.videoTime"
                                                ></v-sparkline>
                                            </v-card>
                                            <v-card
                                                v-else
                                                flat
                                                color="transparent"
                                                class="elevation-0"
                                            >
                                                <v-alert
                                                    dense
                                                    text
                                                    type="error"
                                                    border="left"
                                                >
                                                    Neexistuje chybovost ve
                                                    videu
                                                </v-alert>
                                            </v-card>
                                        </v-tab-item>
                                    </v-tabs-items>
                                </v-card>
                            </div>
                        </v-col>
                    </v-row>
                </div>
            </v-container>
        </v-card>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        audioTab: null,
        videoTab: null,
        loading: true,
        stream: {
            video: {
                bitrate: null,
                pid: null,
                discontinuities: null,
                scrambled: null,
                access: null,
                videoDescription: null
            },
            audio: {
                bitrate: null,
                pid: null,
                discontinuities: null,
                scrambled: null,
                audioLanguage: null,
                audioAccess: null,
                access: null,
                description: null
            },
            ca: {
                access: null,
                scrambled: null,
                description: null
            },
            service: {
                pcrpid: null,
                pmtpid: null,
                tsid: null,
                name: null,
                provider: null
            },
            ts: {
                country: null
            }
        },
        interval: null,
        videoLabels: [],
        videoBitrateChartArray: [],
        audioBitrateChartArray: [],
        audioLabels: [],
        streamId: null,
        ccErorrs: [],

        drawDuration: 1000,
        labelSize: 4,
        lineWidth: 1,
        labels: true,
        routeId: null,
        websocketAlert: null
    }),

    created() {
        this.loadCCError();
        this.bindRouteId();
        this.websocketData();
        this.checkIfSebsocketisConnectedAndReturnResponse();
    },
    methods: {
        bindRouteId() {
            this.routeId = this.$route.params.id;
        },
        loadCCError() {
            window.axios
                .post("streamInfo/ccError", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    if (response.data.status === "exist") {
                        this.ccErorrs.audio = response.data.audio[0];
                        this.ccErorrs.audioTime = response.data.audioTime[0];
                        this.ccErorrs.video = response.data.video[0];
                        this.ccErorrs.videoTime = response.data.videoTime[0];
                    }
                    this.ccErorrs = response.data;
                });
        },
        currentTime() {
            var today = new Date();
            return (
                today.getHours() +
                ":" +
                today.getMinutes() +
                ":" +
                today.getSeconds()
            );
        },

        checkIfSebsocketisConnectedAndReturnResponse() {
            this.websocketAlert = null;
            setTimeout(
                function() {
                    if (this.stream.video.pid === null) {
                        this.websocketAlert = "alert";
                    }
                }.bind(this),
                5000
            );
        },

        websocketData() {
            Echo.channel(
                "streamInfoTsVideoBitrate" + this.$route.params.id
            ).listen("StreamInfoTsVideoBitrate", eVideo => {
                this.stream.video.bitrate = eVideo["bitrate"];
                this.videoBitrateChartArray.push(parseInt(eVideo["bitrate"]));
                if (this.videoBitrateChartArray.length == 10) {
                    this.videoBitrateChartArray.shift();
                }

                this.videoLabels.push(this.currentTime());
                if (this.videoLabels.length == 10) {
                    this.videoLabels.shift();
                }

                this.stream.video.pid = eVideo["videoPid"];
                this.stream.video.discontinuities = eVideo["discontinuities"];
                this.stream.video.scrambled = eVideo["scrambled"];
                this.stream.video.access = eVideo["access"];
                this.stream.video.videoDescription = eVideo["videoDescription"];
                this.loading = false;
                this.websocketAlert = null;
            });

            Echo.channel(
                "streamInfoTsAudioBitrate" + this.$route.params.id
            ).listen("StreamInfoAudioBitrate", eAudio => {
                // console.log(e["bitrate"]);
                this.stream.audio.bitrate = eAudio["bitrate"];
                this.audioBitrateChartArray.push(parseInt(eAudio["bitrate"]));
                if (this.audioBitrateChartArray.length == 10) {
                    this.audioBitrateChartArray.shift();
                }

                this.audioLabels.push(this.currentTime());
                if (this.audioLabels.length == 10) {
                    this.audioLabels.shift();
                }
                this.stream.audio.pid = eAudio["audioPid"];
                this.stream.audio.discontinuities =
                    eAudio["audioDiscontinuities"];
                this.stream.audio.scrambled = eAudio["audioScrambled"];
                this.stream.audio.audioLanguage = eAudio["audioLanguage"];
                this.stream.audio.audioAccess = eAudio["audioAccess"];
                this.stream.audio.description = eAudio["audioDescription"];
            });

            Echo.channel("streamInfoTsCa" + this.$route.params.id).listen(
                "StreamInfoCa",
                eCA => {
                    this.stream.ca.access = eCA["access"];
                    this.stream.ca.description = eCA["description"];
                    this.stream.ca.scrambled = eCA["scrambled"];
                }
            );

            Echo.channel("streamInfoTsService" + this.$route.params.id).listen(
                "StreamInfoService",
                eService => {
                    this.stream.service.pcrpid = eService["pcrpid"];
                    this.stream.service.pmtpid = eService["pmtpid"];
                    this.stream.service.tsid = eService["tsid"];
                    this.stream.service.name = eService["name"];
                    this.stream.service.provider = eService["provider"];
                }
            );

            Echo.channel("streamInfoTS" + this.$route.params.id).listen(
                "StreamInfoTs",
                eTS => {
                    this.stream.ts.country = eTS["country"];
                }
            );
        },

        resetStreamDataToDefault() {
            this.stream.video.pid = null;
            this.stream.video.bitrate = null;
            this.stream.video.discontinuities = null;
            this.stream.video.scrambled = null;
            this.stream.video.access = null;
            this.stream.video.videoDescription = null;

            this.stream.audio.bitrate = null;
            this.stream.audio.pid = null;
            this.stream.audio.discontinuities = null;
            this.stream.audio.scrambled = null;
            this.stream.audio.audioLanguage = null;
            this.stream.audio.audioAccess = null;
            this.stream.audio.access = null;
            this.stream.audio.description = null;

            this.stream.ca.access = null;
            this.stream.ca.scrambled = null;
            this.stream.ca.description = null;

            this.stream.ca.pcrpid = null;
            this.stream.ca.pmtpid = null;
            this.stream.ca.tsid = null;
            this.stream.ca.name = null;
            this.stream.ca.provider = null;

            this.stream.ts.country = null;
        }
    },

    mounted() {
        this.websocketData();

        this.interval = setInterval(
            function() {
                try {
                    this.loadCCError();
                } catch (error) {}
            }.bind(this),
            5000
        );
    },
    watch: {
        $route(to, from) {
            this.audioTab = null;
            this.videoTab = null;
            this.resetStreamDataToDefault();
            this.loading = true;
            this.bindRouteId();
            this.checkIfSebsocketisConnectedAndReturnResponse();
            Echo.leaveChannel("streamInfoTsVideoBitrate" + from.params.id);
            Echo.leaveChannel("streamInfoTsAudioBitrate" + from.params.id);
            Echo.leaveChannel("streamInfoTsCa" + from.params.id);
            Echo.leaveChannel("streamInfoTsService" + from.params.id);
            Echo.leaveChannel("streamInfoTS" + from.params.id);

            this.videoBitrateChartArray = [];
            this.audioBitrateChartArray = [];

            // this.getStreamDetailInfo();
            this.loadCCError();

            Echo.channel("streamInfoTsVideoBitrate" + to.params.id).listen(
                "StreamInfoTsVideoBitrate",
                eVideo => {
                    this.loading = false;
                    this.stream.video.bitrate = eVideo["bitrate"];
                    this.videoBitrateChartArray.push(
                        parseInt(eVideo["bitrate"])
                    );

                    if (this.videoBitrateChartArray.length == 10) {
                        this.videoBitrateChartArray.shift();
                    }

                    this.videoLabels.push(this.currentTime());
                    if (this.videoLabels.length == 10) {
                        this.videoLabels.shift();
                    }

                    this.stream.video.pid = eVideo["videoPid"];
                    this.stream.video.discontinuities =
                        eVideo["discontinuities"];
                    this.stream.video.scrambled = eVideo["scrambled"];
                    this.stream.video.access = eVideo["access"];
                    this.stream.video.videoDescription =
                        eVideo["videoDescription"];
                }
            );

            Echo.channel("streamInfoTsAudioBitrate" + to.params.id).listen(
                "StreamInfoAudioBitrate",
                eAdudio => {
                    this.stream.audio.bitrate = eAdudio["bitrate"];
                    this.audioBitrateChartArray.push(
                        parseInt(eAdudio["bitrate"])
                    );
                    if (this.audioBitrateChartArray.length == 10) {
                        this.audioBitrateChartArray.shift();
                    }

                    this.audioLabels.push(this.currentTime());
                    if (this.audioLabels.length == 10) {
                        this.audioLabels.shift();
                    }
                    this.stream.audio.pid = eAdudio["audioPid"];
                    this.stream.audio.discontinuities =
                        eAdudio["audioDiscontinuities"];
                    this.stream.audio.scrambled = eAdudio["audioScrambled"];
                    this.stream.audio.audioLanguage = eAdudio["audioLanguage"];
                    this.stream.audio.audioAccess = eAdudio["audioAccess"];
                }
            );

            Echo.channel("streamInfoTsCa" + to.params.id).listen(
                "StreamInfoCa",
                eCA => {
                    this.stream.ca.access = eCA["access"];
                    this.stream.audio.description = eCA["description"];
                    this.stream.audio.scrambled = eCA["scrambled"];
                }
            );

            Echo.channel("streamInfoTsService" + to.params.id).listen(
                "StreamInfoService",
                eService => {
                    this.stream.service.pcrpid = eService["pcrpid"];
                    this.stream.service.pmtpid = eService["pmtpid"];
                    this.stream.service.tsid = eService["tsid"];
                    this.stream.service.name = eService["name"];
                    this.stream.service.provider = eService["provider"];
                }
            );

            Echo.channel("streamInfoTS" + to.params.id).listen(
                "StreamInfoTs",
                eTS => {
                    this.stream.ts.country = eTS["country"];
                }
            );
        }
    },

    beforeDestroy: function() {
        clearInterval(this.interval);
        Echo.leaveChannel("streamInfoTsVideoBitrate" + this.routeId);
        Echo.leaveChannel("streamInfoTsAudioBitrate" + this.routeId);
        Echo.leaveChannel("streamInfoTsCa" + this.routeId);
        Echo.leaveChannel("streamInfoTsService" + this.routeId);
        Echo.leaveChannel("streamInfoTS" + this.routeId);
    }
};
</script>
