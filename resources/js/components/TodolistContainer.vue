    //../resources/js/components/Todolist.vue
    <template>
        <div>
        <ul class="todolist">
            <todolist 
            v-for="todolist in todolists" 
            v-bind:todolist="todolist"
            v-bind:key="todolist.id">
            </todolist>
        </ul>
        </div>
    </template>

<script>
    import { mapGetters } from "vuex";
    export default {
      mounted() {
        this.$store.dispatch("GET_TODOLISTS");
      },
      computed: {
        ...mapGetters(["todolists"]),
      }
    };    
</script>
