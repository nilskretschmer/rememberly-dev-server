let actions = {
    ADD_TODOLIST({commit}, todolist) {
          axios.post('/api/todolists', todolist).then(res => {
              console.log(res)
              if (res.data === "added")
                  console.log('ok')
                  commit('ADD_TODOLIST', res.data)
          }).catch(err => {
              console.log(err.response)
          })
      },
      DELETE_TODOLIST({commit}, todolist) {
          axios.delete(`/api/todolists/${todolist.id}`)
              .then(res => {
                  if (res.data === 'deleted')
                      console.log('deleted')
              }).catch(err => {
                  console.log(err)
              })
      },
      GET_TODOLISTS({commit}) {
          axios.get('/api/todolists')
              .then(res => {
                  {  console.log(res.data)
                      commit('GET_TODOLISTS', res.data)
                  }
              }).catch(err => {
                  console.log(err)
              })
      }
  }
  export default actions