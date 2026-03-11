class Actions {
    constructor() {
        console.log("Actions initialized");
    }

    static async add_action(type, action) {
        const basePath = typeof window !== "undefined" && window.uripath ? window.uripath : "";
        const normalizedType = String(type || "").trim().toLowerCase();
        const csrfEntry = typeof window !== "undefined" ? window.albaActionCsrf[normalizedType] : null;

        if (!csrfEntry || typeof csrfEntry !== "string") {
            throw new Error(`Missing CSRF token for action type: ${normalizedType}`);
        }

        const formData = new FormData();
        formData.append("type", normalizedType);
        formData.append("action", JSON.stringify(action));
        formData.append(`csrf_${normalizedType}`, csrfEntry);

        const response = await fetch(`${basePath}/api/actions/add_action`, {
            method: "POST",
            body: formData,
        });

        let payload = {};
        try {
            payload = await response.json();
        } catch (_error) {
            throw new Error("Unable to parse add_action response");
        }

        if (!response.ok || !payload.success) {
            throw new Error(payload.msg || "Failed to add action");
        }

        return payload;
    }
}

export default Actions;
