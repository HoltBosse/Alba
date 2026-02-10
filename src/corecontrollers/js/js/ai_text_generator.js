/**
 * Chrome Built-in AI helper for generating image alt/title text
 * Uses the Prompt API (Gemini Nano)
 * Requires Chrome 127+ with Prompt API enabled
 * Docs: https://developer.chrome.com/docs/ai/prompt-api
 */

class AITextGenerator {
    constructor() {
        this.session = null;
        this.isAvailable = false;
        this.availabilityStatus = null;
    }

    async initialize() {
        try {
            // Check if LanguageModel is available
            if (typeof LanguageModel === 'undefined') {
                console.warn('LanguageModel API not available. Enable chrome://flags/#prompt-api-for-gemini-nano');
                return false;
            }

            // Check availability status
            this.availabilityStatus = await LanguageModel.availability();
            
            console.log('LanguageModel availability:', this.availabilityStatus);
            
            if (this.availabilityStatus === 'no') {
                console.warn('Language model not available on this device');
                return false;
            }

            if (this.availabilityStatus === 'after-download') {
                console.log('Language model will download on first use (~1.5GB)');
            }

            if (this.availabilityStatus === 'downloadable') {
                console.log('Language model will MAYBE download on first use (~1.5GB)');
            }

            if (this.availabilityStatus === 'readily') {
                console.log('✅ Language model is ready to use');
            }

            this.isAvailable = true;
            return true;
            
        } catch (error) {
            console.error('Error checking LanguageModel availability:', error);
            return false;
        }
    }

    async createSession() {
        if (this.session) {
            return this.session;
        }

        try {
            this.session = await LanguageModel.create({
                expectedInputs: [
                    { type: "text", languages: ["en"] },
                    { type: "image" },
                ],
                expectedOutputs: [{ type: "text", languages: ["en"] }],
                systemPrompt: `You are an expert at writing accessible, SEO-friendly image descriptions.

For ALT text:
- Describe what's in the image clearly and concisely
- Maximum 125 characters
- Don't start with "image of" or "picture of"
- Focus on the main subject and important details
- Consider accessibility needs

For TITLE text:
- Provide helpful context about the image
- Maximum 100 characters
- Make it informative and suitable for SEO
- Can be slightly more descriptive than ALT text

Always respond with ONLY the requested text - no quotes, no explanations, no extra formatting.`
            });
            
            console.log('✅ LanguageModel session created');
            return this.session;
            
        } catch (error) {
            console.error('Failed to create LanguageModel session:', error);
            
            if (error.message?.includes('download')) {
                throw new Error('Model is downloading. Please wait and try again in a few moments.');
            }
            if (error.message?.includes('not available')) {
                throw new Error('AI not available. Please enable chrome://flags/#prompt-api-for-gemini-nano');
            }
            throw error;
        }
    }

    async generateAltText(base64Data) {
        console.log('Generating AI Alt Text for visual content...');
        
        try {
            // 1. Convert Base64 string to a Blob
            const response = await fetch(base64Data);
            const imageBlob = await response.blob();

            // 2. Ensure session is multimodal-capable
            // Note: Some implementations require a specific capability check
            const session = await this.createSession();

            // 3. Define the prompt instructions
            const instructions = `Generate a concise ALT text. 
                Maximum 125 characters. 
                Describe the visual elements directly. 
                Do not use "image of" or "picture of".`;

            // 4. Submit as a multimodal request
            // The API accepts an array of parts: the text instruction and the image Blob
            const result = await session.prompt([
                {
                    role:"user",
                    content:[
                        {
                            type:"text",
                            value: instructions,
                        },
                        {
                            type: "image",
                            value: imageBlob
                        }
                    ]
                }
            ]);

            return this.cleanResponse(result, 125);

        } catch (error) {
            console.error('Multimodal Alt text generation failed:', error);
            throw error;
        }
    }


    async generateTitle(base64Data) {
        console.log('Generating AI Title Text for visual content...');
        
        try {
            // 1. Convert Base64 string to a Blob
            const response = await fetch(base64Data);
            const imageBlob = await response.blob();

            // 2. Ensure session is multimodal-capable
            // Note: Some implementations require a specific capability check
            const session = await this.createSession();

            // 3. Define the prompt instructions
            const instructions = `Generate a concise TITLE text. 
                Maximum 100 characters. 
                Explain in as few words as possible what this picture is as a whole. 
                Do not use "image of" or "picture of".`;

            // 4. Submit as a multimodal request
            // The API accepts an array of parts: the text instruction and the image Blob
            const result = await session.prompt([
                {
                    role:"user",
                    content:[
                        {
                            type:"text",
                            value: instructions,
                        },
                        {
                            type: "image",
                            value: imageBlob
                        }
                    ]
                }
            ]);

            return this.cleanResponse(result, 125);

        } catch (error) {
            console.error('Multimodal Alt text generation failed:', error);
            throw error;
        }
    }

    async generateBoth(filename) {
        const session = await this.createSession();
        
        const cleanName = filename
            .replace(/\.[^/.]+$/, '')
            .replace(/[-_]/g, ' ')
            .replace(/\d+/g, '')
            .trim();
        
        const prompt = `Generate both ALT text and TITLE for an image with filename "${cleanName}".

Requirements:
- ALT: Max 125 characters, accessible description, no "image of" prefix
- TITLE: Max 100 characters, SEO-friendly

Format your response exactly as:
ALT: [your alt text]
TITLE: [your title]`;

        try {
            const result = await session.prompt(prompt);
            
            // Parse the structured response
            const altMatch = result.match(/ALT:\s*(.+?)(?=\s*TITLE:|$)/is);
            const titleMatch = result.match(/TITLE:\s*(.+?)$/is);
            
            return {
                alt: altMatch 
                    ? this.cleanResponse(altMatch[1], 125)
                    : '',
                title: titleMatch 
                    ? this.cleanResponse(titleMatch[1], 100)
                    : ''
            };
        } catch (error) {
            console.error('Batch generation failed:', error);
            throw error;
        }
    }

    // Helper to clean up AI responses
    cleanResponse(text, maxLength) {
        return text
            .trim()
            .replace(/^["']|["']$/g, '')  // Remove quotes
            .replace(/^(alt text:|title:|alt:|title text:)\s*/i, '')  // Remove prefixes
            .replace(/\n.*/g, '')  // Remove everything after first newline
            .substring(0, maxLength);
    }

    // Streaming for real-time updates (optional)
    async *generateStreaming(filename, type = 'alt') {
        const session = await this.createSession();
        
        const cleanName = filename
            .replace(/\.[^/.]+$/, '')
            .replace(/[-_]/g, ' ')
            .replace(/\d+/g, '')
            .trim();
        
        const maxChars = type === 'alt' ? 125 : 100;
        const prompt = type === 'alt'
            ? `Generate ALT text (max ${maxChars} chars) for image "${cleanName}". Don't use "image of".`
            : `Generate TITLE (max ${maxChars} chars) for image "${cleanName}". Be descriptive and SEO-friendly.`;

        try {
            const stream = session.promptStreaming(prompt);
            
            for await (const chunk of stream) {
                yield this.cleanResponse(chunk, maxChars);
            }
        } catch (error) {
            console.error('Streaming generation failed:', error);
            throw error;
        }
    }

    getAvailability() {
        return this.availabilityStatus;
    }

    isReady() {
        return this.isAvailable;
    }

    async destroy() {
        if (this.session) {
            this.session.destroy();
            this.session = null;
        }
    }

    // Clone session for parallel operations
    async cloneSession() {
        if (!this.session) {
            await this.createSession();
        }
        return await this.session.clone();
    }
}

export const aiTextGenerator = new AITextGenerator();