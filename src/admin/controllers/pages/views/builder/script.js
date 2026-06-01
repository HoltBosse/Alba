const optionsSideBar = document.querySelector(".page-builder-sidebar");

optionsSideBar.addEventListener("click", (e) => {
    if (e.target.classList.contains("eb-left-option")) {
        document.querySelectorAll(".eb-left-option").forEach((bar) => {
            bar.classList.remove("active");
        });
        e.target.classList.toggle("active");

        document.querySelectorAll(".eb-left").forEach((bar) => {
            bar.classList.remove("active");
        });
        document.querySelector(`.eb-left.${e.target.dataset.pane}`).classList.toggle("active");
    }
});

document.querySelector(".edit-bar-toggles i.fa-angles-left").addEventListener("click", () => {
    if(optionsSideBar.querySelectorAll(".eb-left-option.active").length === 0) {
        optionsSideBar.querySelector(".eb-left-option").click();
        resizeIframeContainerFromActiveButton();
        return;
    }

    optionsSideBar.querySelectorAll(".eb-left-option").forEach((el) => {
        el.classList.remove("active");
        if(el.dataset.pane) {
            document.querySelector(`.eb-left.${el.dataset.pane}`).classList.remove("active");
        }
    });
    resizeIframeContainerFromActiveButton();
});

document.querySelector(".edit-bar-toggles i.fa-angles-right").addEventListener("click", () => {
    if(document.querySelectorAll(".eb-right.active").length === 0) {
        document.querySelector(".page-configuration-sidebar").classList.add("active");
        resizeIframeContainerFromActiveButton();
        return;
    }

    document.querySelectorAll(".eb-right").forEach((el) => {
        el.classList.remove("active");
    });
    resizeIframeContainerFromActiveButton();
});

function resizeIframeContainer(sizeName) {
    const sizes = {
        "mobile": {
            width: 360,
        },
        "tablet": {
            width: 768,
        },
        "desktop": {
            width: 1280,
        },
        "max": {
            width: Number.MAX_SAFE_INTEGER,
        }
    }

    if(!sizeName || !sizes[sizeName]) {
        console.warn("Invalid size option selected");
        return false;
    }

    //get the viewport width and compute scale for iframe container
    const pageViewportContainer = document.querySelector(".page-viewport-container");
    const containerWidth = pageViewportContainer.clientWidth; //remove padding
    const containerPadding = parseFloat(getComputedStyle(pageViewportContainer).paddingLeft) + parseFloat(getComputedStyle(pageViewportContainer).paddingRight);
    const availableWidth = containerWidth - containerPadding;

    const containerHeight = pageViewportContainer.clientHeight; //remove padding
    const containerPaddingHeight = parseFloat(getComputedStyle(pageViewportContainer).paddingTop) + parseFloat(getComputedStyle(pageViewportContainer).paddingBottom);
    const availableHeight = containerHeight - containerPaddingHeight;

    const size = sizes[sizeName];
    const width = size.width === Number.MAX_SAFE_INTEGER ? availableWidth : size.width;
    const iframeContainer = document.querySelector(".page-viewport-container .iframe-container");
    const sizeWithUnit = `${width}px`;
    iframeContainer.style.width = sizeWithUnit;

    if(width > availableWidth) {
        const scale = availableWidth / width;
        iframeContainer.style.transformOrigin = "top";
        iframeContainer.style.transform = `scale(${scale})`;
        iframeContainer.style.height = `${availableHeight / scale}px`;
    } else {
        iframeContainer.style.transformOrigin = "top";
        iframeContainer.style.transform = "scale(1)";
        iframeContainer.style.height = `${availableHeight}px`;
    }

    return true;
}

function resizeIframeContainerFromActiveButton() {
    const activeSizeButton = document.querySelector(".page-viewport-options .fa-solid.active");
    if(activeSizeButton) {
        return resizeIframeContainer(activeSizeButton.dataset.size);
    }

    return false;
}

document.querySelector(".page-viewport-options").addEventListener("click", (e) => {
    if(e.target.classList.contains("fa-solid")) {
        if(e.target.classList.contains("active")) {
            return;
        }

        const resizeSucess = resizeIframeContainer(e.target.dataset.size);
        if(!resizeSucess) {
            return;
        }

        document.querySelectorAll(".page-viewport-options .fa-solid").forEach((el) => {
            el.classList.remove("active");
        });
        e.target.classList.add("active");
    }
});

window.addEventListener("load", () => {
    document.querySelector(".page-viewport-options .fa-solid[data-size='desktop']").click();
});

window.addEventListener("resize", () => {
    resizeIframeContainerFromActiveButton()
});

document.querySelector("[name='title']").addEventListener("input", (e) => {
    const title = e.target.value;
    document.querySelector(".page-title-preview").textContent = title || "Page Title";
});

// Setup drag events for component options
document.querySelectorAll(".component-option").forEach((option) => {
    option.addEventListener("dragstart", (e) => {
        const component = e.currentTarget.dataset.component;
        const config = e.currentTarget.dataset.config;
        e.dataTransfer.effectAllowed = "copy";
        e.dataTransfer.setData("component", component);
        e.dataTransfer.setData("config", config);
        
        // Disable pointer events on iframe during drag so events can reach parent
        iframe.style.pointerEvents = "none";
    });
    
    option.addEventListener("dragend", (_) => {
        // Re-enable pointer events on iframe after drag
        iframe.style.pointerEvents = "";
    });
});

const iframe = document.querySelector("iframe");
const pageViewportContainer = document.querySelector(".page-viewport-container");

function getElementFromIframeAtPoint(clientX, clientY) {
    // Calculate drop position relative to iframe
    const iframeRect = iframe.getBoundingClientRect();
    const iframeContainer = iframe.parentElement;
    
    // Get the transform scale if any
    const computedStyle = window.getComputedStyle(iframeContainer);
    const transform = computedStyle.transform;
    let scale = 1;
    if (transform && transform !== 'none') {
        const matrix = new DOMMatrix(transform);
        scale = matrix.a; // scaleX
    }
    
    // Calculate position inside iframe (accounting for scale)
    const x = (clientX - iframeRect.left) / scale;
    const y = (clientY - iframeRect.top) / scale;
    
    // Find the element at that position inside the iframe
    const element = iframe.contentDocument.elementFromPoint(x, y);
    if(!element) {
        return null;
    }

    element._dropPosition = { x, y }; // Store drop position on the element for later use

    //compute if the drop cords are on the top or bottom 50% of the element, left or right, and store that on the element as well (boolean)
    const componentParent = element.closest("[data-rendered]");
    if(!componentParent) {
        return element; // If no parent with data-rendered, just return the element without drop position info
    }
    element._isDropTop = (y - componentParent.getBoundingClientRect().top) < (componentParent.getBoundingClientRect().height / 2);
    element._isDropBottom = !element._isDropTop;
    element._isDropLeft = (x - componentParent.getBoundingClientRect().left) < (componentParent.getBoundingClientRect().width / 2);
    element._isDropRight = !element._isDropLeft;

    return element;
}

function setInnerIframeCallbacks() {
    //prevent links from being clicked inside the iframe since that would navigate away from the builder
    iframe.contentDocument.querySelectorAll("a[href]").forEach((link) => {
        link.addEventListener("click", (e) => {
            e.preventDefault();
        });
    });

    iframe.contentDocument.addEventListener("click", (e) => {
        //console.log("hellooooooo");

        if(e.target.closest("[data-rendered]")) {
            const renderedElement = e.target.closest("[data-rendered]");
            console.log("Clicked on rendered element with component:", renderedElement.dataset.component);

            htmx.ajax(
                'POST',
                '/admin/pages/api/get-component-form',
                {
                    target:'.component-configuration-sidebar',
                    //swap:'innerHTML',
                    values: {
                        component: renderedElement.dataset.component,
                        config: renderedElement.dataset.config
                    }
                }
            ).then(() => {
                document.querySelectorAll(".eb-right").forEach((el) => {
                    el.classList.remove("active");
                });
                document.querySelector(".component-configuration-sidebar").classList.add("active");

                document.querySelector(".component-configuration-sidebar").querySelector("form .class-update").addEventListener("click", (e) => {
                    console.log("fdgjkdfgjkfgdjkgdfkjdf");
                    fetch(e.target.closest("form").action, {
                        method: "POST",
                        body: new FormData(e.target.closest("form"))
                    }).then((response) => response.json()).then((data) => {
                        console.log("Received updated config from server:", data);
                        renderedElement.dataset.config = JSON.stringify(data.config);
                        updateIframeContent();

                        document.querySelector(".component-configuration-sidebar").innerHTML = "<p>component configuration</p>";
                        document.querySelector(".component-configuration-sidebar").classList.remove("active");
                        document.querySelector(".page-configuration-sidebar").classList.add("active");

                    }).catch((error) => {
                        console.error("Error submitting component config form:", error);
                    });
                });
            });
        }
    });
}

function updateIframeContent() {
    const iframeBody = iframe.contentDocument.querySelector("body");

    const form = document.createElement("form");
    form.method = "post";
    form.style.display = "none";

    iframeBody.querySelectorAll("[data-rendered]").forEach((el) => {
        const componentInput = document.createElement("input");
        componentInput.type = "hidden";
        componentInput.name = "component[]";
        componentInput.value = el.dataset.component;
        form.appendChild(componentInput);

        const configInput = document.createElement("input");
        configInput.type = "hidden";
        configInput.name = "config[]";
        configInput.value = el.dataset.config;
        form.appendChild(configInput);
    });

    iframe.contentDocument.body.appendChild(form);
    console.log("Submitting form inside iframe with component and config data");
    form.submit();

    iframe.addEventListener("load", () => {
        console.log("Iframe reloaded after form submission");
        setInnerIframeCallbacks();
    }, { once: true });
}

function setupIframeDragEvents() {
    setInnerIframeCallbacks();

    // With pointer-events: none on iframe during drag, events reach the container
    pageViewportContainer.addEventListener("dragover", (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = "copy";

        if(iframe.contentDocument.body.children.length === 0) {
            return;
        }

        const targetElement = getElementFromIframeAtPoint(e.clientX, e.clientY);
        if(targetElement) {
            const componentParent = targetElement.closest("[data-rendered]");
            //componentParent.dataset.targetref = Math.random().toString(36).substr(2, 9); //random id

            if(targetElement.classList.contains("drop-spacer")) {
                return; // Already showing spacer for this element
            }

            iframe.contentDocument.querySelectorAll(".drop-spacer").forEach((spacer) => spacer.remove());

            const spacer = document.createElement("div");
            spacer.classList.add("drop-spacer");
            spacer.style.height = "5rem";
            spacer.style.backgroundColor = "rgba(0, 123, 255, 0.5)";
            spacer.dataset.target = targetElement;
            //spacer.dataset.targetref = componentParent.dataset.targetref;

            if(targetElement._isDropTop) {
                componentParent.parentElement.insertBefore(spacer, componentParent);
            } else if(targetElement._isDropBottom) {
                componentParent.parentElement.insertBefore(spacer, componentParent.nextSibling);
            }
        }
    });
    
    pageViewportContainer.addEventListener("dragenter", (e) => {
        e.preventDefault();
    });
    
    pageViewportContainer.addEventListener("drop", (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        const component = e.dataTransfer.getData("component");
        const config = e.dataTransfer.getData("config");
        const iframeBody = iframe.contentDocument.querySelector("body");
        
        const targetElement = getElementFromIframeAtPoint(e.clientX, e.clientY);
        
        console.log("Dropped component:", component);
        console.log("Component config:", config);
        console.log("Target element:", targetElement);
        console.log("Target element tag:", targetElement?.tagName);
        
        let insertedElement = null;

        //if body has no children
        if(iframeBody.children.length === 0) {
            console.log("Dropped on empty body");

            const placeHolderDiv = document.createElement("div");
            placeHolderDiv.dataset.component = component;
            placeHolderDiv.dataset.config = config;
            placeHolderDiv.dataset.rendered = "true";
            console.log("Created placeholder div with component and config data:", placeHolderDiv);
            console.log(iframeBody);
            iframeBody.appendChild(placeHolderDiv);

            insertedElement = placeHolderDiv;
        } else {
            if(!targetElement) {
                console.warn("No valid drop target found");
                return;
            }

            if(!targetElement.classList.contains("drop-spacer")) {
                console.warn("Drop target is not a spacer, ignoring drop");
                return;
            }

            targetElement.dataset.component = component;
            targetElement.dataset.config = config;
            targetElement.dataset.rendered = "true";

            console.log("Dropped on:", targetElement);

            insertedElement = targetElement;
        }

        //get the index of insertedElement in the list of all elements with data-rendered
        const renderedElements = iframe.contentDocument.querySelectorAll("[data-rendered]");
        const index = Array.from(renderedElements).indexOf(insertedElement);
        console.log("Index of inserted element among rendered elements:", index);

        //query into the iframe for the data-rendered element with the same index and click it
        iframe.addEventListener("load", () => {
            setTimeout(() => {
                const newRenderedElements = iframe.contentDocument.querySelectorAll("[data-rendered]");
                const newElement = newRenderedElements[index];
                if(newElement) {
                    console.log("Clicking on newly inserted element in iframe to open config:", newElement);
                    newElement.click();
                } else {
                    console.warn("Could not find newly inserted element in iframe after reload");
                }
            }, 5); //so we run after the other load event callbacks that set up the iframe content and callbacks
        }, { once: true });

        updateIframeContent();
    });
}

// Check if iframe is already loaded
try {
    if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') {
        setupIframeDragEvents();
    } else {
        iframe.addEventListener("load", setupIframeDragEvents, { once: true });
    }
} catch (_) {
    iframe.addEventListener("load", setupIframeDragEvents, { once: true });
}