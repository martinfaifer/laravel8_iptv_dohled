<template>
    <v-main>
        <div class="ml-12 body-1">
            <v-row>
                <strong>
                    Posledních 8 záznamů z historie výpadků
                </strong>
            </v-row>
        </div>
        <v-card flat class="mt-7" elevation="0" color="#202020">
            <div v-if="history != null">
                <v-timeline dense>
                    <v-timeline-item
                        v-for="stream in history"
                        :key="stream.id"
                        :color="stream.color"
                        small
                    >
                        <template v-slot:opposite>
                            <span
                                class="
                                                body-2 font-weight-bold
                                            "
                                v-text="stream.created_at"
                            ></span>
                        </template>
                        <div class="py-4">
                            <div v-if="stream.status == 'stream_ok'">
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>

                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Stream je v pořádku
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div
                                v-else-if="
                                    stream.status == 'scrambledPids_warning'
                                "
                            >
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Změněno pořadí Pidů
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div
                                v-else-if="
                                    stream.status == 'transporterrors_warning'
                                "
                            >
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Objevily se TS chyby
                                        </strong>
                                    </small>
                                </v-row>
                            </div>

                            <div v-else-if="stream.status == 'stream_error'">
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Stream nefunguje
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div
                                v-else-if="stream.status == 'diagnostic_crash'"
                            >
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Problém s diagnostikou
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div v-else-if="stream.status == 'no_audio'">
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Nepodařilo se detekovat audio
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div v-else-if="stream.status == 'issue'">
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Ve streamu se vyskytl problém
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div v-else-if="stream.status == 'no_dekrypt'">
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Stream se nedekryptuje
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div
                                v-else-if="stream.status == 'no_video_bitrate'"
                            >
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Video nemá datový tok
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div
                                v-else-if="stream.status == 'no_audio_bitrate'"
                            >
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>

                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Audio nemá datový tok
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div
                                v-else-if="stream.status == 'sheduler_disable'"
                            >
                                <v-row>
                                    <small class="green--text">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small class="green--text ml-12">
                                        <strong>
                                            Aplikováno automatické vypnutí
                                            alertů
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div
                                v-else-if="
                                    stream.status == 'streamCrash_tryToStart'
                                "
                            >
                                <v-row>
                                    <small :class="`${stream.color}--text`">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Přestala fungovat diagnostika,
                                            stream se pokusí automaticky spustit
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                            <div v-else-if="stream.status == 'sheduler_enable'">
                                <v-row>
                                    <small class="green--text">
                                        <strong>
                                            {{ stream.created_at }} =>
                                            {{ stream.nazev }}
                                        </strong>
                                    </small>
                                </v-row>
                                <v-row>
                                    <small
                                        :class="`${stream.color}--text`"
                                        class="ml-12"
                                    >
                                        <strong>
                                            Aplikováno automatické zapnutí
                                            alertů
                                        </strong>
                                    </small>
                                </v-row>
                            </div>
                        </div>
                    </v-timeline-item>
                </v-timeline>
            </div>

            <div v-else>
                <v-alert text type="info">
                    Neexistuje žádná historie
                </v-alert>
            </div>
        </v-card>
    </v-main>
</template>
<script>
export default {
    data() {
        return {
            history: null,
            headers: [
                {
                    text: "Stream",
                    align: "start",
                    value: "nazev"
                },
                {
                    text: "Data",
                    value: "data"
                },
                {
                    text: "vytvořeno",
                    value: "created_at"
                }
            ]
        };
    },

    created() {
        this.loadHistory();
    },
    methods: {
        loadHistory() {
            window.axios.get("history").then(response => {
                if (response.data.status == "empty") {
                    this.history = null;
                } else {
                    this.history = response.data;
                }
            });
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadHistory();
            }.bind(this),
            10000
        );
    }
};
</script>
