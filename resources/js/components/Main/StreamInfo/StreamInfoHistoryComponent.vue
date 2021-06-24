<template>
    <v-main>
        <v-card
            height="100%"
            width="100%"
            class="text-center"
            color="transparent"
            flat
        >
            <span>
                <strong>
                    Historie streamu
                </strong>
            </span>
            <v-container v-if="streamHistory != 'none'">
                <v-timeline>
                    <v-timeline-item
                        v-for="stream in streamHistory"
                        :key="stream.id"
                        :color="stream.color"
                        small
                    >
                        <v-card>
                            <v-card-text>
                                <template>
                                    <span
                                        class="body-2 font-weight-bold"
                                        v-text="stream.created_at"
                                    ></span>
                                </template>
                                <div class="py-2">
                                    <div v-if="stream.status == 'stream_ok'">
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Stream je v pořádku
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status == 'stream_start'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Spuštěn dohled streamu
                                        </strong>
                                    </div>

                                    <div
                                        v-else-if="
                                            stream.status == 'stream_audio_ok'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Audio je v pořádku
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status ==
                                                'stream_stoped_by_user'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Uživatel zastavil dohled streamu
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status ==
                                                'scrambledPids_warning'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Změněno pořadí Pidů
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status ==
                                                'transporterrors_warning'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Objevily se TS chyby
                                        </strong>
                                    </div>

                                    <div
                                        v-else-if="
                                            stream.status == 'stream_error'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Stream nefunguje
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status == 'diagnostic_crash'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Problém s diagnostikou
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="stream.status == 'no_audio'"
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Nepodařilo se detekovat audio
                                        </strong>
                                    </div>
                                    <div v-else-if="stream.status == 'issue'">
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Ve streamu se vyskytl problém
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status == 'no_dekrypt'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Stream se nedekryptuje
                                        </strong>
                                    </div>

                                    <div
                                        v-else-if="
                                            stream.status ==
                                                'stream_out_of_sync'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Nefunguje Audio nebo je posunuté
                                        </strong>
                                    </div>

                                    <div
                                        v-else-if="
                                            stream.status == 'no_video_bitrate'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Video nemá datový tok
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status == 'no_audio_bitrate'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Audio nemá datový tok
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status == 'sheduler_disable'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Aplikováno automatická vypnutí
                                            alertů
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status ==
                                                'streamCrash_tryToStart'
                                        "
                                    >
                                        <strong
                                            :class="`${stream.color}--text`"
                                        >
                                            Přestala fungovat diagnostika,
                                            stream se pokusí automaticky spustit
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status == 'sheduler_enable'
                                        "
                                    >
                                        <strong class="green--text"
                                            >> Aplikováno automatická zapnutí
                                            alertů
                                        </strong>
                                    </div>
                                    <div
                                        v-else-if="
                                            stream.status ==
                                                'stream_without_signal'
                                        "
                                    >
                                        <strong class="red--text">
                                            Přestala fungovat automatická
                                            diagnostika
                                        </strong>
                                    </div>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-timeline-item>
                </v-timeline>
            </v-container>
            <v-container v-else>
                <div>
                    <v-alert outlined text type="info">
                        <strong>Není evidována žádná historie u streamu</strong>
                    </v-alert>
                </div>
            </v-container>
        </v-card>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        streamHistory: [],
        streamId: null
    }),

    created() {
        this.getStreamHistory();
    },
    methods: {
        getStreamHistory() {
            try {
                axios
                    .post("streamInfo/history", {
                        streamId: this.$route.params.id,
                        records: "5"
                    })
                    .then(response => {
                        this.streamHistory = response.data;
                    });
            } catch (error) {
                console.log(error);
            }
        }
    },

    mounted() {
        setInterval(
            function() {
                try {
                    this.getStreamHistory();
                } catch (error) {}
            }.bind(this),
            60000
        );
    },
    watch: {
        $route(to, from) {
            this.streamHistory = [];
            this.streamId = null;
            this.getStreamHistory();
        }
    }
};
</script>
