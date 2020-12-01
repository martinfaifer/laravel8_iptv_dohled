<template>
    <v-main>
        <v-card
            height="100%"
            width="100%"
            class="text-center pl-10 pr-10"
            color="transparent"
            flat
        >
            <span>
                <strong>
                    TS data
                </strong>
            </span>

            <v-container>
                <!-- loading -->

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
                    <!-- Servisní informace o streamu -->
                    <div
                        v-if="stream.service.pcrpid != null"
                        class="text-start"
                    >
                        <span>
                            Servisní informace o streamu:
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
                        </v-card>
                    </div>
                    <!-- audio -->
                    <div v-if="stream.audio != null" class="text-start mt-6">
                        <span>
                            Audio bitrate:
                            <strong>
                                {{ Math.round(stream.audio.bitrate / 1024) }}
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
                                                        .discontinuities == '0',
                                                'red--text':
                                                    stream.audio
                                                        .discontinuities != '0'
                                            }"
                                        >
                                            <strong>
                                                {{
                                                    stream.audio.discontinuities
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
                                                    stream.audio.scrambled ==
                                                    '0',
                                                'red--text':
                                                    stream.audio.scrambled !=
                                                    '0'
                                            }"
                                        >
                                            <strong>
                                                {{ stream.audio.scrambled }}
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
                                                {{ stream.audio.language }}
                                            </strong>
                                        </span>
                                    </small></v-list-item
                                >
                            </v-list-item>
                        </v-card>
                        <v-progress-linear
                            v-if="stream.audio.audioAccess == 'success'"
                            :buffer-value="stream.audio.bitrate / '1048576'"
                            v-model="stream.audio.bitrate / '1048576'"
                            color="success"
                            stream
                        >
                        </v-progress-linear>
                        <v-progress-linear
                            v-else
                            v-model="stream.audio.bitrate / '1048576'"
                            :buffer-value="stream.audio.bitrate / '1048576'"
                            color="error"
                            stream
                        >
                        </v-progress-linear>

                        <br />

                        <!--  -->
                        <v-card
                            flat
                            v-show="audioBitrateChartArray.length > 1"
                            color="transparent"
                        >
                            <div class="text-center">
                                <span>
                                    Graf audio bitratu
                                </span>
                            </div>
                            <v-sheet color="transparent">
                                <!-- audio bitrate chart -->

                                <v-sparkline
                                    fill
                                    :auto-draw-duration="drawDuration"
                                    :label-size="labelSize"
                                    color="#EAF0F1"
                                    :smooth="16"
                                    type="trend"
                                    :labels="audioLabels"
                                    :gradient="['rgba(0, 136, 255, 0.2)']"
                                    :line-width="lineWidth"
                                    :show-labels="labels"
                                    :value="audioBitrateChartArray"
                                    stroke-linecap="round"
                                ></v-sparkline>
                            </v-sheet>
                        </v-card>
                        <!--  -->

                        <v-card
                            v-if="ccErorrs.status === 'exist'"
                            flat
                            color="transparent"
                            class="elevation-0"
                        >
                            <br />
                            <div class="text-center body-2">
                                <span>
                                    Audio CC Erory
                                </span>
                            </div>
                            <v-sparkline
                                v-if="ccErorrs.status === 'exist'"
                                :smooth="1"
                                type="bar"
                                :gradient="['#ff3333']"
                                :line-width="1"
                                :value="ccErorrs.audio"
                                :labels="ccErorrs.audioTime"
                            ></v-sparkline>
                        </v-card>

                        <br />
                    </div>

                    <!-- video -->
                    <div v-if="stream.video != null" class="text-start">
                        <span>
                            Video bitrate:
                            <strong>
                                {{
                                    Math.round(
                                        (stream.video.bitrate / 1048576) * 100
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
                                                        .discontinuities == '0',
                                                'red--text':
                                                    stream.video
                                                        .discontinuities != '0'
                                            }"
                                        >
                                            <strong>
                                                {{
                                                    stream.video.discontinuities
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
                                                    stream.video.scrambled ==
                                                    '0',
                                                'red--text':
                                                    stream.video.scrambled !=
                                                    '0'
                                            }"
                                        >
                                            <strong>
                                                {{ stream.video.scrambled }}
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
                        </v-card>
                        <v-progress-linear
                            v-if="stream.video.access == 'success'"
                            v-model="stream.video.bitrate / '1048576'"
                            :buffer-value="stream.video.bitrate / '1048576'"
                            color="success"
                            stream
                        >
                        </v-progress-linear>

                        <v-progress-linear
                            v-else
                            v-model="stream.video.bitrate / '1048576'"
                            :buffer-value="stream.video.bitrate / '1048576'"
                            color="error"
                            stream
                        >
                        </v-progress-linear>

                        <br />

                        <v-card
                            flat
                            v-show="videoBitrateChartArray.length > 1"
                            color="transparent"
                        >
                            <div class="text-center">
                                <span class="body-1">
                                    Graf video bitratu
                                </span>
                            </div>
                            <v-sheet color="transparent">
                                <v-sparkline
                                    fill
                                    :auto-draw-duration="drawDuration"
                                    :label-size="labelSize"
                                    color="#EAF0F1"
                                    :smooth="16"
                                    type="trend"
                                    :labels="videoLabels"
                                    :gradient="['rgba(0, 136, 255, 0.2)']"
                                    :line-width="lineWidth"
                                    :show-labels="labels"
                                    :value="videoBitrateChartArray"
                                    stroke-linecap="round"
                                ></v-sparkline>
                            </v-sheet>
                        </v-card>

                        <v-card
                            v-if="ccErorrs.status === 'exist'"
                            flat
                            color="transparent"
                            class="elevation-0"
                        >
                            <br />
                            <div class="text-center body-2">
                                <span>
                                    Video CC Erory
                                </span>
                            </div>
                            <v-sparkline
                                v-if="ccErorrs.status === 'exist'"
                                show-labels
                                :smooth="1"
                                type="bar"
                                :gradient="['#ff3333']"
                                :line-width="1"
                                :value="ccErorrs.video"
                                :labels="ccErorrs.videoTime"
                            ></v-sparkline>
                        </v-card>
                        <br />
                    </div>

                    <!-- CA Modul-->
                    <div v-if="stream.ca.access != null" class="text-start">
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
                        <br />
                    </div>
                </div>
            </v-container>
        </v-card>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        loading: true,
        stream: {
            video: {
                bitrate: "",
                pid: "",
                discontinuities: "",
                scrambled: "",
                access: ""
            },
            audio: {
                bitrate: "",
                pid: "",
                discontinuities: "",
                scrambled: "",
                audioLanguage: "",
                audioAccess: "",
                access: ""
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
        },
        interval: null,
        videoLabels: [],
        videoBitrateChartArray: [],
        audioBitrateChartArray: [],
        audioLabels: [],
        streamId: null,
        ccErorrs: [],

        drawDuration: 1000,
        labelSize: 6,
        lineWidth: 1,
        labels: true,
        routeId: null
    }),

    created() {
        // this.getStreamDetailInfo();
        this.loadCCError();
        this.bindRouteId();
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

        getStreamDetailInfo() {
            window.axios
                .post("streamInfo/detail", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    this.stream = response.data;
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
                this.loading = false;
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
                }
            );
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
            (this.loading = true), this.bindRouteId();
            Echo.leaveChannel("streamInfoTsVideoBitrate" + from.params.id);
            Echo.leaveChannel("streamInfoTsAudioBitrate" + from.params.id);
            Echo.leaveChannel("streamInfoTsCa" + from.params.id);
            Echo.leaveChannel("streamInfoTsService" + from.params.id);

            this.videoBitrateChartArray = [];
            this.audioBitrateChartArray = [];

            this.getStreamDetailInfo();
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
    }
};
</script>
