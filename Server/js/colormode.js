if (localStorage.getItem("check-state") === null) localStorage.setItem("check-state", "false");
if (localStorage.getItem("check-state") == "false") {
  document.documentElement.style.setProperty('--background-primary', '#191F23');
  document.documentElement.style.setProperty('--background-secondary', '#161B1F');
  document.documentElement.style.setProperty('--background-tertiary', '#27323C');
  document.documentElement.style.setProperty('--white', 'whitesmoke');
  document.documentElement.style.setProperty('--shadow', '#111');
  document.getElementsByClassName("logo")[0].style.filter = "invert(1.0)";
  document.getElementById("color-mode-text").innerHTML = "PREPNÚŤ NA SVETLÝ REŽIM";
  document.getElementById("color-mode-switch").checked = false;
} else {
  document.documentElement.style.setProperty('--background-primary', '#DFE9F1');
  document.documentElement.style.setProperty('--background-secondary', '#FFFFFF');
  document.documentElement.style.setProperty('--background-tertiary', '#EBEEF1');
  document.documentElement.style.setProperty('--white', '#222');
  document.documentElement.style.setProperty('--shadow', '#DDD');
  document.getElementsByClassName("logo")[0].style.filter = "invert(0.0)";
  document.getElementById("color-mode-text").innerHTML = "PREPNÚŤ NA TMAVÝ REŽIM";
  document.getElementById("color-mode-switch").checked = true;
}

function Swap(colorMode = () => { }) {
  if (localStorage.getItem("check-state") == "true") {
    document.documentElement.style.setProperty('--background-primary', '#191F23');
    document.documentElement.style.setProperty('--background-secondary', '#161B1F');
    document.documentElement.style.setProperty('--background-tertiary', '#27323C');
    document.documentElement.style.setProperty('--white', 'whitesmoke');
    document.documentElement.style.setProperty('--shadow', '#111');
    document.getElementsByClassName("logo")[0].style.filter = "invert(1.0)";
    document.getElementById("color-mode-text").innerHTML = "PREPNÚŤ NA SVETLÝ REŽIM";
    localStorage.setItem("check-state", "false");
    colorMode();
  } else {
    document.documentElement.style.setProperty('--background-primary', '#DFE9F1');
    document.documentElement.style.setProperty('--background-secondary', '#FFFFFF');
    document.documentElement.style.setProperty('--background-tertiary', '#EBEEF1');
    document.documentElement.style.setProperty('--white', '#222');
    document.documentElement.style.setProperty('--shadow', '#DDD');
    document.getElementsByClassName("logo")[0].style.filter = "invert(0.0)";
    document.getElementById("color-mode-text").innerHTML = "PREPNÚŤ NA TMAVÝ REŽIM";
    localStorage.setItem("check-state", "true");
    colorMode();
  }
}