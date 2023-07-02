// Clear previous popup
function clearModal(el) {
  el.style.opacity = 0;
  el.style.display = "none";
  el.style.height = 0;
}

// Establish new popup
document.querySelectorAll(".ambvp-verse").forEach((ele, index) => {
  /* validate the reference form */
  // Checking the spaces and parentheses
  let ref = ele.textContent.trim();
  if (ref[0] !== "(") ref = "(" + ref;
  if (ref.slice(-1) !== ")")
    ref = ref + ")";

  // create the popup element
  const popupEl = document.createElement("div");
  popupEl.className = `ambvp-popup ele-${index}`;

  // Verse text
  const verseValid = ref.match(/[)(:]/g);
  if (verseValid && verseValid.length >= 3 && ele.childNodes.length === 1) {
    let referenceArr = ref.replace(/[)(]/g, "").split(" ");

    const book = ambvpObject.bookDictionary[referenceArr.shift()];

    referenceArr = referenceArr.filter((e) => parseInt(e) >= 0);

    const chapter = parseInt(referenceArr[0]);
    const verseStart = parseInt(referenceArr[1]);
    const verseEnd =
      parseInt(referenceArr[2]) !== null
        ? parseInt(referenceArr[2])
        : parseInt(referenceArr[3]);

    let selectedVerses = ambvpObject.bookText.filter(
      (v) => v.b === book && v.c === chapter
    );

    if (verseEnd)
      selectedVerses = selectedVerses.filter(
        (v) => v.v >= verseStart && v.v <= verseEnd
      );
    else selectedVerses = selectedVerses.filter((v) => v.v === verseStart);

    // Add the selected verses to the html
     const versesTitle = document.createElement("div");
    versesTitle.className = "ambvp-verses-title";
    versesTitle.textContent = ref;
    popupEl.appendChild(versesTitle);

    const versesEl = document.createElement("p");

    selectedVerses.forEach((v) => {
      versesEl.innerHTML += `${v.v}. ${v.text} <br>`;
    });

    popupEl.appendChild(versesEl);
    ele.appendChild(popupEl);

    // Displaying the popup
    ele.addEventListener("mouseenter", function () {
      popupEl.style.opacity = 1;
      popupEl.style.height = "initial";
      popupEl.style.display = "block";
    });
  }
});

document.querySelectorAll(".ambvp-popup").forEach((e) => {
  e.addEventListener("mouseleave", () => clearModal(e));
});
