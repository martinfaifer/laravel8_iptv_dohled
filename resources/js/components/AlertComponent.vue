<template>
    <div>
        <v-snackbar
            v-if="status.status"
            class="text-center"
            rounded="pill"
            transition="scale-transition"
            dense
            top
            :timeout="timeout"
            :color="status.status"
            v-model="status"
        >
            <div class="text-center">
                <span class="subtitle-1"> {{ status.msg }} </span>
            </div>
            <template v-slot:action="{ attrs }">
                <v-btn color="blue" text v-bind="attrs"> </v-btn>
            </template>
        </v-snackbar>
    </div>
</template>
<script>
export default {
    props: ["status"],
    data: () => ({
        timeout: 4000,
    }),

    methods: {},
    // watch: {
    //     status() {
    //         if (this.status != null) {
    //             setTimeout(function() {
    //                 this.status = null;
    //             }, 5000);
    //         }
    //     }
    // }
};
</script>
