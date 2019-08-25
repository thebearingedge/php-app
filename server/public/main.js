fetch('/api/todos')
  .then(res => res.json())
  .then(todos => {
    $data = document.createElement('pre')
    $data.textContent = JSON.stringify(todos, null, 2)
    document.body.appendChild($data)
  })
