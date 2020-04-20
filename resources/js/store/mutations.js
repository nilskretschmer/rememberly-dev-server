let mutations = {
    ADD_TODOLIST(state, todolist) {
        state.todolists.push(todolist)
    },
    CACHE_REMOVED(state, todolist) {
      state.todolistToRemove = todolist;
    },
    GET_TODOLISTS(state, todolists) {
        state.todolists = todolists
    },
    DELETE_TODO(state, todolists) {
        state.todolist.splice(state.todolists.indexOf(todolist), 1)
        state.todolistToRemove = null;
    }
}
export default mutations