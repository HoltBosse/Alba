window.addEventListener('load', (event) => {
    //console.log('page is fully loaded');
    const cri = document.querySelectorAll('.crowdriff_image_container');
    cri.forEach(im => {
        // setup lazy load to trigger after
        // hiq image has loaded fully
        const hiq = im.dataset.hiq;
        const imgel = new Image();
        imgel.src = hiq;
        imgel.thumb = im;
        imgel.onload = (e)=>{
            console.log(e);
            this.thumb.style.backgroundImage = `url('${hiq}')`;
        }
    });
});