let getters = {
    newTodolist: state => {
        return state.newTodolist
    },
    todolists: state => {
        return state.todolists
    },
    todolistToRemove: state => {
        return state.todolistToRemove
    }
}
export default getters