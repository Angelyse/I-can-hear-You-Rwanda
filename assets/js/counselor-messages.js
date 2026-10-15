document.addEventListener("DOMContentLoaded", function () {


    // =========================================
    // ELEMENTS
    // =========================================

    const conversationSearch =
        document.getElementById("conversationSearch");

    const conversationItems =
        document.querySelectorAll(".conversation-item");

    const chatMessages =
        document.getElementById("chatMessages");

    const messageInput =
        document.getElementById("messageInput");

    const sendBtn =
        document.getElementById("sendBtn");

    const attachmentBtn =
        document.getElementById("attachmentBtn");

    const fileInput =
        document.getElementById("fileInput");

    const typingIndicator =
        document.getElementById("typingIndicator");


    // =========================================
    // CONVERSATION SEARCH
    // =========================================

    if (conversationSearch) {

        conversationSearch.addEventListener(
            "input",
            function () {

                const search =
                    conversationSearch.value
                    .toLowerCase()
                    .trim();


                conversationItems.forEach(function (item) {

                    const name =
                        item.dataset.name.toLowerCase();


                    if (name.includes(search)) {

                        item.style.display = "flex";

                    } else {

                        item.style.display = "none";

                    }

                });

            }
        );

    }


    // =========================================
    // SELECT CONVERSATION
    // =========================================

    conversationItems.forEach(function (item) {

        item.addEventListener("click", function () {


            conversationItems.forEach(function (conversation) {

                conversation.classList.remove("active");

            });


            item.classList.add("active");


            const client =
                item.dataset.name;


            if (client !== "Alice Niyonsenga") {

                alert(
                    "The conversation with " +
                    client +
                    " will be loaded from the database later."
                );

            }

        });

    });


    // =========================================
    // SEND MESSAGE
    // =========================================

    function sendMessage() {

        const message =
            messageInput.value.trim();


        if (message === "") {

            return;

        }


        // Create message wrapper

        const messageWrapper =
            document.createElement("div");

        messageWrapper.className =
            "message sent";


        // Create content

        const content =
            document.createElement("div");

        content.className =
            "message-content";


        // Create bubble

        const bubble =
            document.createElement("div");

        bubble.className =
            "message-bubble";

        bubble.textContent =
            message;


        // Time

        const time =
            document.createElement("span");

        time.className =
            "message-time";


        const now =
            new Date();


        time.textContent =
            now.toLocaleTimeString(
                [],
                {
                    hour: "2-digit",
                    minute: "2-digit"
                }
            );


        content.appendChild(bubble);

        content.appendChild(time);

        messageWrapper.appendChild(content);

        chatMessages.appendChild(messageWrapper);


        // Clear input

        messageInput.value = "";

        messageInput.style.height = "auto";


        // Scroll down

        chatMessages.scrollTop =
            chatMessages.scrollHeight;


        // Demo typing response

        showTypingResponse();

    }


    // =========================================
    // SEND BUTTON
    // =========================================

    sendBtn.addEventListener(
        "click",
        sendMessage
    );


    // =========================================
    // ENTER TO SEND
    // =========================================

    messageInput.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Enter" &&
                !event.shiftKey
            ) {

                event.preventDefault();

                sendMessage();

            }

        }
    );


    // =========================================
    // TEXTAREA AUTO HEIGHT
    // =========================================

    messageInput.addEventListener(
        "input",
        function () {

            messageInput.style.height =
                "auto";

            messageInput.style.height =
                messageInput.scrollHeight + "px";

        }
    );


    // =========================================
    // DEMO TYPING RESPONSE
    // =========================================

    function showTypingResponse() {

        typingIndicator.classList.add("show");


        setTimeout(function () {

            typingIndicator.classList.remove("show");

        }, 1500);

    }


    // =========================================
    // ATTACHMENT
    // =========================================

    attachmentBtn.addEventListener(
        "click",
        function () {

            fileInput.click();

        }
    );


    fileInput.addEventListener(
        "change",
        function () {

            if (fileInput.files.length > 0) {

                const file =
                    fileInput.files[0];


                alert(
                    "Selected file: " +
                    file.name
                );

            }

        }
    );


    // =========================================
    // VIDEO CALL
    // =========================================

    const videoCallBtn =
        document.getElementById("videoCallBtn");


    videoCallBtn.addEventListener(
        "click",
        function () {

            const start =
                confirm(
                    "Start a video call with Alice Niyonsenga?"
                );


            if (start) {

                alert(
                    "Video call functionality will be connected later."
                );

            }

        }
    );


    // =========================================
    // PHONE CALL
    // =========================================

    const phoneCallBtn =
        document.getElementById("phoneCallBtn");


    phoneCallBtn.addEventListener(
        "click",
        function () {

            alert(
                "Voice call functionality will be connected later."
            );

        }
    );


    // =========================================
    // CONVERSATION INFO
    // =========================================

    const chatInfoBtn =
        document.getElementById("chatInfoBtn");


    chatInfoBtn.addEventListener(
        "click",
        function () {

            alert(
                "Conversation information will be displayed here."
            );

        }
    );


    // =========================================
    // NEW MESSAGE
    // =========================================

    const newMessageBtn =
        document.getElementById("newMessageBtn");


    newMessageBtn.addEventListener(
        "click",
        function () {

            alert(
                "A client selection window will be added here."
            );

        }
    );


});