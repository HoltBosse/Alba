// GET THE DROP ZONE
var uploader = document.getElementById('upload_space');

// STOP THE DEFAULT BROWSER ACTION FROM OPENING THE FILE
uploader.addEventListener("dragover", (e)=>{
    e.preventDefault();
	e.stopPropagation();
	e.target.classList.add('ready');
});

uploader.addEventListener("dragleave", (e)=>{
    e.preventDefault();
	e.stopPropagation();
	e.target.classList.remove('ready');
});

// ADD OUR OWN UPLOAD ACTION
uploader.addEventListener("drop", (e)=>{
    e.preventDefault();
    e.stopPropagation();
    e.target.classList.remove('ready');
    document.querySelector("#image_uploader")?.remove();
	do_upload(e);
});

// handle regular file upload
let regular_upload = document.getElementById('regular_upload');
regular_upload.addEventListener('change', (e)=>{
    document.querySelector("#image_uploader")?.remove();
	do_upload(e);
});