    //../resources/js/components/Todolist.vue
    <template>
    <li>{{ todolist.title }} {{ todolist.updated_at }}</li>
    </template>

    <script>
       export default {
          props: ['todolist']
       }

    </script>