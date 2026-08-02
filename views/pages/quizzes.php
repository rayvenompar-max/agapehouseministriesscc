<section class="page" id="page-quizzes">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--quizzes">
    <div class="page-hero-inner">
      <p class="eyebrow quizzes-hero-eyebrow">Faith &amp; Knowledge</p>
      <h2 class="quizzes-hero-title">Bible <em>Quizzes.</em></h2>
      <p class="lede quizzes-hero-lede">Test your knowledge of Scripture — from the Gospels to the Epistles.</p>
    </div>

    <!-- Spark orb + animated rings -->
    <div class="quiz-spark-wrap" aria-hidden="true">
      <div class="quiz-spark-ring"></div>
      <div class="quiz-spark-ring quiz-spark-ring--delay"></div>
      <div class="quiz-spark"></div>
    </div>
  </div>

  <!-- ── Quiz grid view ───────────────────────────────────────────────────── -->
  <div class="section-wrap quiz-section-wrap" id="quizGridView">

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
  <div class="section-wrap quiz-active-wrap" id="quizActiveView" hidden>

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
          <button id="quizRetryBtn">Try Again</button>
          <button id="quizAllBtn">All Quizzes</button>
        </div>
      </div>
    </div>

  </div>

</section>
