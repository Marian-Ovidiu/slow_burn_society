export default function typingEffect(passedTexts = []) {
    return {
        texts: passedTexts,
        currentText: 0,
        displayText: "",
        speed: 100,
        pauseBetweenTexts: 1500,

        startTyping() {
            this.displayText = "";
            const fullText = this.texts[this.currentText] || "";
            let i = 0;

            const type = () => {
                if (i < fullText.length) {
                    this.displayText += fullText[i++];
                    setTimeout(type, this.speed);
                } else {
                    setTimeout(() => {
                        this.currentText = (this.currentText + 1) % this.texts.length;
                        this.startTyping();
                    }, this.pauseBetweenTexts);
                }
            };

            type();
        },

        init() {
            if (this.texts.length) this.startTyping();
        }
    };
}
