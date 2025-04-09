import React, { useState } from 'react'
import ReactDOM from 'react-dom/client'

function App() {
  const [count, setCount] = useState(0)

  return (
    <div>
      <p>sample react component in php</p>
      <button onClick={() => setCount(count + 1)}>
        Count is: {count}
      </button>
    </div>
  )
}

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
)