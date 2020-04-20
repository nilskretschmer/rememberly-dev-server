    //../resources/js/components/TodoItem.vue
    <template>
     <li class="todo-item" :class="{ completed: todoItem.completed }">
         <div class="view">
             <input type="checkbox" v-model="todoItem.completed" class="toggle">
             <label>{{todoItem.todoText}}</label>
             <button @click="removeTodolist(todolist)" class="destroy"></button>
         </div>
     </li>
    </template>
    <script>
    export default {
      name: "TodoItem",
      props: ["todoItem"],

      methods: {
        removeTodolist(todolist) {
          this.$store.commit("CACHE_REMOVED", todolist)
          this.$store.dispatch("DELETE_TODOLIST", todolist);
        }
      }
    };
    </script>