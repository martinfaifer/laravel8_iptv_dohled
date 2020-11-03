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
                TS data
            </span>

            <v-container>
                <!-- audio -->
                <div v-if="stream.audio != null" class="text-start">
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
                                                stream.audio.discontinuities ==
                                                '0',
                                            'red--text':
                                                stream.audio.discontinuities !=
                                                '0'
                                        }"
                                    >
                                        <strong>
                                            {{ stream.audio.discontinuities }}
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
                                                stream.audio.scrambled == '0',
                                            'red--text':
                                                stream.audio.scrambled != '0'
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
                        v-if="stream.audio.access == 'success'"
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
                                :smooth="16"
                                :labels="audioLabels"
                                :gradient="['#1feaea', '#ffd200', '#f72047']"
                                :line-width="1"
                                :value="audioBitrateChartArray"
                                auto-draw
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
                        <column-chart
                            :colors="['#ff3333']"
                            height="100px"
                            v-if="ccErorrs.status === 'exist'"
                            :stacked="true"
                            :data="ccErorrs.audio"
                        ></column-chart>
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
                                                stream.video.discontinuities ==
                                                '0',
                                            'red--text':
                                                stream.video.discontinuities !=
                                                '0'
                                        }"
                                    >
                                        <strong>
                                            {{ stream.video.discontinuities }}
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
                                                stream.video.scrambled == '0',
                                            'red--text':
                                                stream.video.scrambled != '0'
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
                                :smooth="16"
                                :labels="videoLabels"
                                :gradient="['#1feaea', '#ffd200', '#f72047']"
                                :line-width="1"
                                :value="videoBitrateChartArray"
                                auto-draw
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
                        <column-chart
                            :colors="['#ff3333']"
                            height="100px"
                            v-if="ccErorrs.status === 'exist'"
                            :stacked="true"
                            :data="ccErorrs.video"
                        ></column-chart>
                    </v-card>
                    <br />
                </div>

                <!-- CA Modul-->
                <div v-if="stream.ca != null" class="text-start">
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
                                                stream.ca.access == 'success',
                                            'red--text':
                                                stream.ca.access != 'success'
                                        }"
                                    >
                                        <strong
                                            v-if="stream.ca.access == 'success'"
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

                <!-- Servisní informace o streamu -->
                <div v-if="stream.service != null" class="text-start">
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
            </v-container>
        </v-card>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        stream: [],

        videoLabels: [],
        videoBitrateChartArray: [],
        audioBitrateChartArray: [],
        audioLabels: [],
        streamId: null,
        ccErorrs: []
    }),

    created() {
        this.getStreamDetailInfo();
        this.loadCCError();
        // this.exitWebsocektChannels();
        this.websocketData();
    },
    methods: {
        loadCCError() {
            window.axios
                .post("streamInfo/ccError", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    if (response.data.status === "exist") {
                        this.ccErorrs.audio = response.data.audio;
                        this.ccErorrs.video = response.data.video;
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
                return today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        },

        websocketData() {
            Echo.leaveChannel(
                "streamInfoTsAudioBitrate" + this.$route.params.id
            );
            Echo.leaveChannel(
                "streamInfoTsVideoBitrate" + this.$route.params.id
            );

            Echo.channel(
                "streamInfoTsVideoBitrate" + this.$route.params.id
            ).listen("StreamInfoTsVideoBitrate", e => {
                this.stream.video.bitrate = e["bitrate"];
                this.videoBitrateChartArray.push(parseInt(e["bitrate"]));


                if(this.videoBitrateChartArray.length == 10) {
                    this.videoBitrateChartArray.shift();
                }

                this.videoLabels.push(this.currentTime());
                if(this.videoLabels.length == 10) {
                    this.videoLabels.shift();
                }



                this.stream.video.pid = e["videoPid"];
                this.stream.video.discontinuities = e["discontinuities"];
                this.stream.video.scrambled = e["scrambled"];
            });

            Echo.channel(
                "streamInfoTsAudioBitrate" + this.$route.params.id
            ).listen("StreamInfoAudioBitrate", e => {
                // console.log(e);
                this.stream.audio.bitrate = e["bitrate"];
                this.audioBitrateChartArray.push(parseInt(e["bitrate"]));
                if(this.audioBitrateChartArray.length == 10) {
                    this.audioBitrateChartArray.shift();
                }

                this.audioLabels.push(this.currentTime());
                if(this.audioLabels.length == 10) {
                    this.audioLabels.shift();
                }
                this.stream.audio.pid = e["audioPid"];
                this.stream.audio.discontinuities = e["audioDiscontinuities"];
                this.stream.audio.scrambled = e["audioScrambled"];
                this.stream.audio.audioLanguage = e["audioLanguage"];
                this.stream.audio.audioAccess = e["audioAccess"];
            });
        },
        exitWebsocektChannels() {
            Echo.leaveChannel(
                "streamInfoTsAudioBitrate" + this.$route.params.id
            );
            Echo.leaveChannel(
                "streamInfoTsVideoBitrate" + this.$route.params.id
            );
        },
        waitForConnectToWebsocket() {
            let currentObj = this;
            setTimeout(function() {
                currentObj.websocketData();
            }, 2000);
        }
    },

    mounted() {
        this.websocketData();

        setInterval(
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
            this.videoBitrateChartArray = [];
            this.audioBitrateChartArray = [];

            this.waitForConnectToWebsocket;
            this.getStreamDetailInfo();
            this.loadCCError();
        }
    },
    destroyed() {
        Echo.leaveChannel("streamInfoTsAudioBitrate" + this.$route.params.id);
        Echo.leaveChannel("streamInfoTsVideoBitrate" + this.$route.params.id);
    }
};
</script>
