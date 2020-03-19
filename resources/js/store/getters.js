let getters = {
    newTodolist: state => {
        return state.newTododolist
    },
    todolists: state => {
        return state.todolists
    },
    todolistToRemove: state => {
        return state.todolistToRemove
    }
}
export default getters