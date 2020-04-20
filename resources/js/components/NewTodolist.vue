    //../resources/js/components/NewTodo.vue
    <template>
         <input type="text" v-model="newTodolist.title"
         @keyup.enter="addTodolist" autofocus="autofocus"
          placeholder="What are you trying to get done?" class="new-todoItem">
    </template>
    <script>
    import { mapGetters } from "vuex";
    export default {
      name: "NewTodolist",
      methods: {
        addTodolist() {
          this.$store.dispatch("ADD_TODOLIST", this.newTodolist);
          
        }
      },
      computed: {
        ...mapGetters(["newTodolist"])
      }
    };
    </script>