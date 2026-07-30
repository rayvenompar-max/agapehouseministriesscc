<section class="page" id="page-quizzes">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--quizzes">
    <div class="page-hero-inner">
      <div class="eyebrow quizzes-hero-eyebrow">Faith &amp; Knowledge</div>
      <h2 class="quizzes-hero-title">Bible <em>Quizzes</em></h2>
      <p class="lede quizzes-hero-lede">Test your knowledge of Scripture — from the Gospels to the Epistles.</p>
    </div>

    <!-- Sun disc + rays -->
    <div class="page-hero-horizon-stage" aria-hidden="true">
      <div class="sun-wrap">
        <div class="sun-ray sun-ray--r1"></div>
        <div class="sun-ray sun-ray--r2"></div>
        <div class="sun-ray sun-ray--r3"></div>
        <div class="sun-ray sun-ray--r4"></div>
        <div class="sun-ray sun-ray--r5"></div>
        <div class="sun-ray sun-ray--r6"></div>
        <div class="sun-ray sun-ray--r7"></div>
        <div class="sun-ray sun-ray--r8"></div>
        <div class="sun-ray sun-ray--r9"></div>
        <div class="sun-disc"></div>
      </div>
    </div>
  </div>

  <!-- ── Quiz grid view ───────────────────────────────────────────────────── -->
  <div class="section-wrap" style="padding-top:40px; padding-bottom:80px;" id="quizGridView">

    <!-- Filter pills -->
    <div class="quiz-filter-row" id="quizFilterRow">
      <button class="quiz-filter-pill active" data-category="">All</button>
      <button class="quiz-filter-pill" data-category="Old Testament">Old Testament</button>
      <button class="quiz-filter-pill" data-category="New Testament">New Testament</button>
      <button class="quiz-filter-pill" data-category="Parables">Parables</button>
      <button class="quiz-filter-pill" data-category="Characters">Characters</button>
    </div>

    <div class="quiz-grid" id="quizGrid"></div>
  </div>

  <!-- ── Active quiz view ─────────────────────────────────────────────────── -->
  <div class="section-wrap quiz-active-wrap" id="quizActiveView" hidden style="padding-top:40px; padding-bottom:80px;">

    <!-- Quiz header bar -->
    <div class="quiz-header-bar">
      <button class="quiz-back-btn" id="quizBackBtn" aria-label="Back to quizzes">
        ← Back to Quizzes
      </button>
      <span class="quiz-header-title" id="quizActiveTitle"></span>
    </div>

    <!-- Progress bar -->
    <div class="quiz-progress-wrap">
      <div class="quiz-progress-meta">
        <span id="quizProgressLabel">Question 1 of 5</span>
        <span id="quizScoreLabel">Score: 0</span>
      </div>
      <div class="quiz-progress-bar">
        <div class="quiz-progress-fill" id="quizProgressFill"></div>
      </div>
    </div>

    <!-- Question card -->
    <div class="quiz-question-card" id="quizQuestionCard">
      <p class="quiz-question-text" id="quizQuestionText"></p>
      <div class="quiz-choices" id="quizChoices"></div>
    </div>

    <!-- Result screen (hidden until quiz ends) -->
    <div class="quiz-result-screen" id="quizResultScreen" hidden>
      <div class="quiz-result-inner">
        <div class="quiz-result-icon" id="quizResultIcon">🏆</div>
        <h3 class="quiz-result-heading" id="quizResultHeading">Well done!</h3>
        <p class="quiz-result-score" id="quizResultScore"></p>
        <p class="quiz-result-verse" id="quizResultVerse"></p>
        <div class="quiz-result-actions">
          <button class="btn btn-primary" id="quizRetryBtn">Try Again</button>
          <button class="btn btn-ghost-dark" id="quizAllBtn">All Quizzes</button>
        </div>
      </div>
    </div>

  </div>

</section>
