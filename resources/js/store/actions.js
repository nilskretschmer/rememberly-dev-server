let actions = {
    ADD_TODOLIST({commit}, todolist) {
          axios.post('/api/todolists', todolist).then(res => {
              if (res.data === "added")
                  console.log('ok')
          }).catch(err => {
              console.log(err)
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