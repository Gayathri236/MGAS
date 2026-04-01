function toggleChatbot() {
  const chatbot = document.getElementById("chatbotContainer");
  chatbot.style.display = chatbot.style.display === "flex" ? "none" : "flex";
}

function handleKeyPress(event) {
  if (event.key === "Enter") {
    sendMessage();
  }
}

function sendMessage() {
  const input = document.getElementById("chatInput");
  const message = input.value.trim();
  const chatBody = document.getElementById("chatbotBody");

  if (message === "") return;

  const userMsg = document.createElement("div");
  userMsg.className = "user-message";
  userMsg.textContent = message;
  chatBody.appendChild(userMsg);

  chatBody.scrollTop = chatBody.scrollHeight;
  input.value = "";

  setTimeout(() => {
    const botMsg = document.createElement("div");
    botMsg.className = "bot-message";
    botMsg.textContent = getBotReply(message);
    chatBody.appendChild(botMsg);
    chatBody.scrollTop = chatBody.scrollHeight;
  }, 500);
}

function getBotReply(message) {
  const msg = message.toLowerCase();

  if (msg.includes("hello") || msg.includes("hi")) {
    return "Hello 👋 Welcome to Micro Green Garden House. How can I help you today?";
  } 
  else if (msg.includes("product") || msg.includes("products")) {
    return "We offer Microgreens, Lettuce, and Peppers. Please check our Products section for more details.";
  } 
  else if (msg.includes("delivery")) {
    return "Yes, we provide island-wide delivery across Sri Lanka.";
  } 
  else if (msg.includes("price") || msg.includes("cost")) {
    return "Please contact us for the latest prices and bulk order details.";
  } 
  else if (msg.includes("location") || msg.includes("where")) {
    return "We are based in Sri Lanka and supply fresh microgreens to hotels, restaurants, cafés, and supermarkets.";
  } 
  else if (msg.includes("fresh") || msg.includes("quality")) {
    return "Our products are fresh, natural, chemical-free, and grown with high hygiene standards.";
  } 
  else if (msg.includes("contact")) {
    return "Please contact us by phone, WhatsApp, or email for more details.";
  } 
  else {
    return "Sorry, I am still learning. Please check our website or contact us for more information.";
  }
}