const sortBtns = document.querySelectorAll(".job-id > *");
const sortItems = document.querySelectorAll(".jobs-container > *");

sortBtns.forEach((btn) => {
  btn.addEventListener("click", () => {
    sortBtns.forEach((btn) => btn.classList.remove("active"));
    btn.classList.add("active");

    const targetData = btn.getAttribute("data-target");
    sortItems.forEach((item) => {
      item.classList.add("delete");
      if (
        item.getAttribute("data-item") === targetData ||
        targetData === "all"
      ) {
        item.classList.remove("delete");
      }
    });
  });
});

const bar = document.getElementById("bar");
const close = document.getElementById("close");
const nav = document.getElementById("menu");

if (bar) {
  bar.addEventListener("click", () => {
    nav.classList.add("active");
  });
}

if (close) {
  close.addEventListener("click", () => {
    nav.classList.remove("active");
  });
}

document.getElementById("searchButton").addEventListener("click", () => {
  const searchQuery = document.getElementById("searchInput").value;
  window.location.href = `/jobs/job.html?search=${searchQuery}`;
});
