<template>
    <v-main>
        <alert-component :status="status"></alert-component>
        <v-row>
            <span class="headline">Nastavení prostředí</span>
        </v-row>
        <v-spacer></v-spacer>
        <br />
    </v-main>
</template>
<script>
import AlertComponent from "../AlertComponent";
export default {
    props: ["user"],
    data: () => ({
        status: null
    }),
    components: {
        "alert-component": AlertComponent
    },
    methods: {},
    watch: {
        status() {
            if (this.status != null) {
                setTimeout(function() {
                    this.status = null;
                }, 5000);
            }
        }
    }
};
</script>
