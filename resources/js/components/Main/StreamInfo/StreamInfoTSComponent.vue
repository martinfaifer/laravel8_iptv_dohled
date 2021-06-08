<template>
    <v-main>
        <v-card absolute height="100%" width="100%" color="transparent" flat>
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
                        Stream se nepodařilo diagnostikovat
                    </v-alert>
                </div>

                <div v-else>
                    <!-- Servisní informace o streamu -->
                    <v-col
                        cols="12"
                        sm="12"
                        md="12"
                        lg="12"
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
                    </v-col>
                    <!-- CA Modul-->
                    <v-col
                        cols="12"
                        sm="12"
                        md="12"
                        lg="12"
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
                    </v-col>
                    <v-divider></v-divider>
                    <v-row>
                        <v-col cols="12" sm="12" md="6" lg="6">
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
                                    v-if="stream.audio.audioAccess === 'success'"
                                    :buffer-value="
                                        countBitrate(stream.audio.bitrate)
                                    "
                                    :v-model="
                                        countBitrate(stream.audio.bitrate)
                                    "
                                    color="success"
                                    stream
                                >
                                </v-progress-linear>
                                <v-progress-linear
                                    class="mt-4"
                                    v-else
                                    :v-model="
                                        countBitrate(stream.audio.bitrate)
                                    "
                                    :buffer-value="
                                        countBitrate(stream.audio.bitrate)
                                    "
                                    color="error"
                                    stream
                                >
                                </v-progress-linear>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="12" md="6" lg="6">
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
                                    v-if="stream.video.access === 'success'"
                                    :v-model="
                                        countBitrate(stream.video.bitrate)
                                    "
                                    :buffer-value="
                                        countBitrate(stream.video.bitrate)
                                    "
                                    color="success"
                                    stream
                                >
                                </v-progress-linear>
                                <v-progress-linear
                                    class="mt-4"
                                    v-else
                                    :v-model="
                                        countBitrate(stream.video.bitrate)
                                    "
                                    :buffer-value="
                                        countBitrate(stream.video.bitrate)
                                    "
                                    color="error"
                                    stream
                                >
                                </v-progress-linear>
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

        drawDuration: 2000,
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
            try {
                axios
                    .post("streamInfo/ccError", {
                        streamId: this.$route.params.id
                    })
                    .then(response => {
                        if (response.data.status === "exist") {
                            this.ccErorrs.audio = response.data.audio[0];
                            this.ccErorrs.audioTime =
                                response.data.audioTime[0];
                            this.ccErorrs.video = response.data.video[0];
                            this.ccErorrs.videoTime =
                                response.data.videoTime[0];
                        }
                        this.ccErorrs = response.data;
                    });
            } catch (error) {
                console.log(error);
            }
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
                30000
            );
        },

        async websocketData() {
            try {
                Echo.channel(
                    "streamInfoTsVideoBitrate" + this.$route.params.id
                ).listen("StreamInfoTsVideoBitrate", eVideo => {
                    this.stream.video.bitrate = eVideo["bitrate"];
                    this.videoBitrateChartArray.push(
                        parseInt(eVideo["bitrate"])
                    );
                    if (this.videoBitrateChartArray.length == 40) {
                        this.videoBitrateChartArray.shift();
                    }
                    this.stream.video.pid = eVideo["videoPid"];
                    this.stream.video.discontinuities =
                        eVideo["discontinuities"];
                    this.stream.video.scrambled = eVideo["scrambled"];
                    this.stream.video.access = eVideo["access"];
                    this.stream.video.videoDescription =
                        eVideo["videoDescription"];
                    this.loading = false;
                    this.websocketAlert = null;
                });

                Echo.channel(
                    "streamInfoTsAudioBitrate" + this.$route.params.id
                ).listen("StreamInfoAudioBitrate", eAudio => {
                    this.stream.audio.bitrate = eAudio["bitrate"];
                    this.audioBitrateChartArray.push(
                        parseInt(eAudio["bitrate"])
                    );
                    if (this.audioBitrateChartArray.length == 40) {
                        this.audioBitrateChartArray.shift();
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

                Echo.channel(
                    "streamInfoTsService" + this.$route.params.id
                ).listen("StreamInfoService", eService => {
                    this.stream.service.pcrpid = eService["pcrpid"];
                    this.stream.service.pmtpid = eService["pmtpid"];
                    this.stream.service.tsid = eService["tsid"];
                    this.stream.service.name = eService["name"];
                    this.stream.service.provider = eService["provider"];
                });

                Echo.channel("streamInfoTS" + this.$route.params.id).listen(
                    "StreamInfoTs",
                    eTS => {
                        this.stream.ts.country = eTS["country"];
                    }
                );
            } catch (error) {
                console.log(error);
            }
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
        },

        countBitrate(bitrate) {
            return bitrate / "1048576";
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
            15000
        );
    },
    watch: {
        $route(to, from) {
            try {
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
                        this.stream.audio.audioLanguage =
                            eAdudio["audioLanguage"];
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
            } catch (error) {
                console.log(error);
            }
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
