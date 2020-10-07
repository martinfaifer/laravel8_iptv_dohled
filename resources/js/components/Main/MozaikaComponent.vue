<template>
    <v-main class="lighten-2 mt-12">
        <v-container fluid>
            <v-row>
                <template v-for="n in 4">
                    <v-col
                        v-for="j in 6"
                        :key="`${n}${j}`"
                        class="mt-2"
                        cols="6"
                        md="2"
                    >
                        <v-sheet height="150"></v-sheet>
                    </v-col>
                </template>
            </v-row>
        </v-container>

        <v-container class="text-center">
            <v-pagination v-model="page" :length="4" circle></v-pagination>
        </v-container>
    </v-main>
</template>

<script>
export default {
    data: () => ({
        streams: [],
        page: 1
    }),

    created() {},
    methods: {},

    mounted() {},
    watch: {}
};
</script>
