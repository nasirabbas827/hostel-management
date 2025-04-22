/*search bar*/
let searchBtn = document.querySelector('#search-btn');
let searchBar = document.querySelector('.search-bar-container');

/*menu bar*/
let menu = document.querySelector('#menu-bar');
let navbar = document.querySelector('.navbar');

menu.addEventListener('click', () =>{
    menu.classList.toggle('fa-times');
    navbar.classList.toggle('active');
});

/*video slide*/
let videoBtn = document.querySelectorAll('.vid-btn');

document.querySelectorAll(' .video-container .controls .control-btn').forEach(btn=>{
    btn.onclick=()=>{
        let src=btn.getAttribute('data-src');
        document.querySelector('.video-container .video').src = src
    }
})

/*on scroll*/
window.onscroll = () =>{
    searchBtn.classList.remove('fa-times');
    searchBar.classList.remove('active');
    menu.classList.remove('fa-times');
    navbar.classList.remove('active');
};

/*search bar*/
searchBtn.addEventListener('click', () =>{
    searchBtn.classList.toggle('fa-times');
    searchBar.classList.toggle('active');
});

/*video slider*/
videoBtn.forEach(btn =>{
    btn.addEventListener('click', ()=>{
        document.querySelector('.controls .active').classList.remove('active');
        btn.classList.add('active');
        let src = btn.getAttribute('data-src');
        document.querySelector('#video-slider').src = src;
    });
});

/*reviews*/
var swiper = new Swiper(".review-slider", {
    slidesPerView: 4,
    spaceBetween: 30,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    breakpoints: {
        440:{
            slidesPerView: 1,
        },
        768:{
            slidesPerView: 2,
        },
        1024:{
            slidesPerView: 3,
        },
    },
  });