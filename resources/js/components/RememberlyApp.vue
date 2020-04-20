    //../resources/js/components/RememberlyApp.vue

     <template>
         <section class="rememberlyapp">
                <header class="header">
                  <h1>todolists</h1>
                </header>
                <new-todolist></new-todolist>
                <todolist-container></todolist-container>
         </section>
     </template>
     <script>
    import newTodoList from "../components/NewTodolist.vue";
    import Todolist from "../components/Todolist.vue";
    import TodolistContainer from "../components/TodolistContainer.vue";
    import { mapGetters } from "vuex";

    export default {
      components: {
       newTodoList,
       Todolist,
       TodolistContainer
      },
      name: "RememberlyApp",
      mounted() {
        window.Echo.private("todolist.1").listen(".todolist-created", e => {
          console.log("Echo Test");
          this.$store.commit("ADD_TODOLIST", e.todolist);
          this.newTodolist.title = "";
        });
        window.Echo.channel("taskRemoved").listen(".task-removed", e => {
            //this.$store.commit("DELETE_TODO", this.toRemove);
        });
      },
      computed: {
        ...mapGetters(["newTodolist", "todolistToRemove"])
      }
    };
    </script>