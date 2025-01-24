window.addEventListener('load', (event) => {
    //console.log('page is fully loaded');
    var cri = document.querySelectorAll('.crowdriff_image_container');
    cri.forEach(im => {
        // setup lazy load to trigger after
        // hiq image has loaded fully
        var hiq = im.dataset.hiq;
        var imgel = new Image();
        imgel.src = hiq;
        imgel.thumb = im;
        imgel.onload = function(e){
            console.log(e);
            this.thumb.style.backgroundImage = "url('" + hiq + "')";
        }
    });
});