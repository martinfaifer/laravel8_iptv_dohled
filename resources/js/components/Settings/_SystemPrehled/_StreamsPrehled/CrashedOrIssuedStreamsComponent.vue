<template>
    <v-main>
        <v-row>
            <!-- tabulka se serverovým nastavením -->
            <v-card flat color="transparent elevation-0">
                <div class="text-center">
                    <span class="body-2"> Přehled problémových streamů </span>
                </div>
                <v-data-table
                    :headers="headers"
                    :items="alerts"
                    flat
                    dense
                    class="elevation-0 mt-2"
                >
                    <template v-slot:item.msg="{ item }">
                        <span class="red--text">
                            <strong>
                                {{ item.msg }}
                            </strong>
                        </span>
                    </template>
                </v-data-table>
            </v-card>
        </v-row>
    </v-main>
</template>
<script>
export default {
    data() {
        return {
            headers: [
                {
                    text: "Stream",
                    align: "start",
                    value: "msg"
                },
                {
                    text: "Popis",
                    align: "start",
                    value: "data[0][message]"
                }
            ]
        };
    },

    computed: {
        alerts() {
            return this.$store.state.streamAlerts;
        }
    }
};
</script>
