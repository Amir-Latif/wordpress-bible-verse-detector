// Clear previous popup
function clearModal(el) {
  el.style.opacity = 0;
  el.style.display = "none";
  el.style.height = 0;
}

const verseElements = document.querySelectorAll(".ambvp-verse");
const language = verseElements[0].dataset.lang;
let closeBtnText = "";
switch (language) {
  case "french":
    closeBtnText = "Fermer";
    break;
  case "persian":
    closeBtnText = "بستن";
    break;
  case "russian":
    closeBtnText = "закрывать";
    break;
  case "tamil":
    closeBtnText = "நெருக்கமான";
    break;

  default:
    break;
}

function correctBookName(book, language) {
  const bookCorrection = {
    french: {
      Psaume: "Psaumes",
    },

    persian: {
      اعمال: "اعمال رسولان",
      اشعیا: "اشعيا",
      مزامیر: "مزامير",
    },

    russian: {
      Деяния: "Деяния Апостолов",
      Исаией: "Исаия",
      Исаии: "Исаия",
    },
  };

  if (
    Object.keys(bookCorrection).includes(language) &&
    Object.keys(bookCorrection[language]).includes(book)
  )
    return bookCorrection[language][book];
  else return book;
}

(async function getBibleText() {
  const res = await fetch(`${ambvpObject.textUrl}/${language}Text.json`);
  const bibleText = await res.json();

  // Establish new popup
  verseElements.forEach((ele, index) => {
    /* validate the reference form */
    // Checking the spaces and parentheses
    let ref = ele.textContent.trim();
    if (ref[0] !== "(") ref = "(" + ref;
    if (ref.slice(-1) !== ")") ref = ref + ")";

    // create the popup element
    const popupEl = document.createElement("div");
    popupEl.className = `ambvp-popup pop-${index}`;
    // Verse text
    const verseValidity = ref.match(/[)(:]/g);
    if (verseValidity && verseValidity.length >= 3) {
      let referenceArr = ref.replace(/[)(]/g, "").split(/[\s:-]/g);

      let book = referenceArr.shift();

      // Alternative book names
      book = correctBookName(book, language);

      // if the book is in pattern "2 petros" then the book is 2 words
      if (parseInt(book) > 0) book += ` ${referenceArr.shift()}`;

      // Convert the numbers if persian
      if (language === "persian") {
        referenceArr = referenceArr.map((e) => {
          let singleDigitArr = e.split("");
          singleDigitArr = singleDigitArr.map(
            (d) => ambvpObject.persianNumbersDict[d]
          );

          return singleDigitArr.join("");
        });
      }

      // separate the reference by "and"
      const and = /[(et)وи(மற்றும்);،,]/;
      const versesElArr = [];

      referenceArr
        .join(" ")
        .split(and)
        .filter((e) => e !== "")
        .forEach((subEl) => {
          // continue to get the text
          let subReferenceArr = subEl
            .split(" ")
            .filter((e) => parseInt(e) >= 0);

          // The persian arrays are not arranged as typed. This is a correction
          if (language === "persian")
            subReferenceArr = subReferenceArr.map((_, i) => {
              switch (true) {
                case i === 0:
                  return subReferenceArr[1];
                case i === 1:
                  return subReferenceArr[0];
                case i === 2 && subReferenceArr.length === 3:
                  return subReferenceArr[2];
                case i === 2 && subReferenceArr.length > 3:
                  return subReferenceArr[3];
                case i === 3:
                  return subReferenceArr[2];

                default:
                  break;
              }
            });
          const chapterStart = parseInt(subReferenceArr[0]);
          const verseStart = parseInt(subReferenceArr[1]);

          // consider if the ref ends in another chapter
          const verseFirstEnd =
            subReferenceArr.length === 3 ? parseInt(subReferenceArr[2]) : null;
          const chapterEnd =
            subReferenceArr.length === 4 ? parseInt(subReferenceArr[2]) : null;

          const chapterSecondEnd =
            subReferenceArr.length === 4 ? parseInt(subReferenceArr[3]) : null;

          // First, Select the whole chapter
          let selectedVerses = bibleText.filter(
            (v) => v.b === book && v.c === chapterStart
          );

          // Verse range in the same chapter
          if (verseFirstEnd)
            selectedVerses = selectedVerses.filter(
              (v) => v.v >= verseStart && v.v <= verseFirstEnd
            );
          // Range extends to another chapter
          else if (chapterEnd) {
            // Start with the starting verse
            selectedVerses = selectedVerses.filter((v) => v.v >= verseStart);
            // and the following chapter
            const nextChapter = bibleText.filter(
              (v) =>
                v.b === book && v.c === chapterEnd && v.v <= chapterSecondEnd
            );

            selectedVerses = [...selectedVerses, ...nextChapter];
          }
          // only one verse
          else
            selectedVerses = selectedVerses.filter((v) => v.v === verseStart);

          // Add the selected verses to the html along with the closing button

          popupEl.innerHTML = `<div class="ambvp-flex">
      <div class="ambvp-verses-title">${ref}</div>
      <div class='ambvp-close close-${index}'>${closeBtnText}</div>
      </div>`;

          const versesEl = document.createElement("p");
          // convert from latin to persian
          selectedVerses.forEach((v) => {
            if (language === "persian") {
              v.v = `${v.v}`
                .split("")
                .map((c) =>
                  Object.keys(ambvpObject.persianNumbersDict).find(
                    (n) => ambvpObject.persianNumbersDict[n] === c
                  )
                )
                .join("");

              if (`${v.c}`.length === 1) {
                v.c = Object.keys(ambvpObject.persianNumbersDict).find(
                  (n) => ambvpObject.persianNumbersDict[n] === `${v.c}`
                );
              }
            }
            versesEl.innerHTML += `(${v.c}${
              language === "persian" ? " " : ""
            }:${language === "persian" ? " " : ""}${v.v}) ${v.text} <br>`;
          });

          versesElArr.push(versesEl.outerHTML);
        });

      versesElArr.forEach((e) => {
        popupEl.innerHTML += e;
      });

      ele.innerHTML += popupEl.outerHTML;

      // Displaying the popup
      ele.addEventListener("mouseenter", function () {
        ele.lastChild.style.opacity = 1;
        ele.lastChild.style.height = "initial";
        ele.lastChild.style.display = "block";
      });
    }
  });

  // Hide the popup on leaving the popup el
  document.querySelectorAll(".ambvp-popup").forEach((e) => {
    e.addEventListener("mouseleave", () => clearModal(e));
  });

  // Hide the popup on manual closing
  document.querySelectorAll(".ambvp-close").forEach((el, i) => {
    el.addEventListener("click", (event) => {
      event.preventDefault();
      clearModal(document.querySelector(`.pop-${i}`));
    });
  });
})();
