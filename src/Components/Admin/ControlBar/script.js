// biome-ignore lint/correctness/noUnusedVariables: <called via onClick in ControlBar.php>
function fixedControllerBarGoBack() {
    if(window.history.length <= 1) {
        if(document.referrer!=='' && (new URL(document.referrer)).origin == window.location.origin) {
            window.location.href = document.referrer;
            return;
        }
        window.location.href = window.location.origin + window.uripath + "/admin";
    } else {
        window.history.back();
    }
}