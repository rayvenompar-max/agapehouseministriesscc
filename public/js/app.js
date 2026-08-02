/**
 * Daybreak — Front-end Application
 *
 * Handles:
 *  - SPA page routing
 *  - API calls to the PHP back-end
 *  - Rendering dynamic content (media, articles, prayers, events)
 *  - Form submissions (prayer, donation, contact)
 */

const BASE_URL = '/DigitalEvangelization/api';

// ─── Bible verse dataset (must be at the top — loaded before router fires) ───
var BIBLE_VERSES = [
  { ref:'John 3:16',            text:`For God so loved the world, that he gave his only born Son, that whoever believes in him should not perish, but have eternal life.`,                                                    topics:['love','salvation','faith'] },
  { ref:'Romans 8:28',          text:`We know that all things work together for good for those who love God, to those who are called according to his purpose.`,                                                              topics:['hope','faith','strength'] },
  { ref:'Philippians 4:13',     text:`I can do all things through Christ, who strengthens me.`,                                                                                                                               topics:['strength','faith'] },
  { ref:'Jeremiah 29:11',       text:`"For I know the plans that I have for you," says the LORD, "plans for your welfare and not for calamity, to give you hope and a future."`,                                             topics:['hope','peace'] },
  { ref:'Psalm 23:1',           text:`The LORD is my shepherd; I shall lack nothing.`,                                                                                                                                        topics:['peace','strength','faith'] },
  { ref:'Isaiah 40:31',         text:`But those who wait for the LORD will renew their strength. They will mount up with wings like eagles. They will run, and not be weary. They will walk, and not faint.`,                 topics:['strength','hope','faith'] },
  { ref:'Romans 10:9',          text:`That if you will confess with your mouth that Jesus is Lord, and believe in your heart that God raised him from the dead, you will be saved.`,                                          topics:['salvation','faith'] },
  { ref:'John 14:6',            text:`Jesus said to him, "I am the way, the truth, and the life. No one comes to the Father, except through me."`,                                                                            topics:['salvation','faith'] },
  { ref:'Proverbs 3:5-6',       text:`Trust in the LORD with all your heart, and do not lean on your own understanding. In all your ways acknowledge him, and he will make your paths straight.`,                             topics:['faith','peace','strength'] },
  { ref:'Matthew 11:28',        text:`"Come to me, all you who labor and are heavily burdened, and I will give you rest."`,                                                                                                    topics:['peace','hope','fear anxiety'] },
  { ref:'Romans 5:8',           text:`But God commends his own love toward us, in that while we were yet sinners, Christ died for us.`,                                                                                        topics:['love','salvation','forgiveness'] },
  { ref:'1 John 4:8',           text:`He who does not love does not know God, for God is love.`,                                                                                                                               topics:['love'] },
  { ref:'John 15:13',           text:`Greater love has no one than this, that someone lay down his life for his friends.`,                                                                                                     topics:['love'] },
  { ref:'1 Corinthians 13:4-5', text:`Love is patient and is kind. Love does not envy. Love does not brag, is not proud, does not behave inappropriately, does not seek its own way, is not provoked.`,                      topics:['love'] },
  { ref:'Hebrews 11:1',         text:`Now faith is assurance of things hoped for, proof of things not seen.`,                                                                                                                  topics:['faith','hope'] },
  { ref:'Ephesians 2:8-9',      text:`For by grace you have been saved through faith, and that not of yourselves; it is the gift of God, not of works, that no one would boast.`,                                             topics:['salvation','faith'] },
  { ref:'Romans 6:23',          text:`For the wages of sin is death, but the free gift of God is eternal life in Christ Jesus our Lord.`,                                                                                      topics:['salvation','forgiveness'] },
  { ref:'1 John 1:9',           text:`If we confess our sins, he is faithful and righteous to forgive us the sins, and to cleanse us from all unrighteousness.`,                                                              topics:['forgiveness'] },
  { ref:'Micah 7:18',           text:`Who is a God like you, who pardons iniquity, and passes over the transgression of the remnant of his heritage? He does not retain his anger forever, because he delights in loving kindness.`, topics:['forgiveness','love'] },
  { ref:'Colossians 3:13',      text:`Bearing with one another, and forgiving each other, if any man has a complaint against any; even as Christ forgave you, so you also do.`,                                                topics:['forgiveness','love'] },
  { ref:'Isaiah 41:10',         text:`"Do not be afraid, for I am with you. Do not be dismayed, for I am your God. I will strengthen you. Yes, I will help you. Yes, I will uphold you with the right hand of my righteousness."`, topics:['fear anxiety','strength','peace'] },
  { ref:'Psalm 34:4',           text:`I sought the LORD, and he answered me, and delivered me from all my fears.`,                                                                                                             topics:['fear anxiety','faith'] },
  { ref:'2 Timothy 1:7',        text:`For God did not give us a spirit of fear, but of power, love, and self-control.`,                                                                                                        topics:['fear anxiety','strength','love'] },
  { ref:'Philippians 4:6-7',    text:`In nothing be anxious, but in everything, by prayer and petition with thanksgiving, let your requests be made known to God. And the peace of God, which surpasses all understanding, will guard your hearts and your thoughts in Christ Jesus.`, topics:['fear anxiety','peace'] },
  { ref:'John 14:27',           text:`"Peace I leave with you. My peace I give to you; not as the world gives, I give to you. Do not let your heart be troubled, neither let it be afraid."`,                                 topics:['peace','fear anxiety'] },
  { ref:'Romans 15:13',         text:`Now may the God of hope fill you with all joy and peace in believing, that you may abound in hope, in the power of the Holy Spirit.`,                                                    topics:['hope','peace','faith'] },
  { ref:'Lamentations 3:22-23', text:`It is of the LORD's loving kindnesses that we are not consumed, because his compassion does not fail. They are new every morning; great is your faithfulness.`,                         topics:['hope','love','faith'] },
  { ref:'Psalm 46:1',           text:`God is our refuge and strength, a very present help in trouble.`,                                                                                                                        topics:['strength','peace','hope'] },
  { ref:'2 Corinthians 12:9',   text:`"My grace is sufficient for you, for my power is made perfect in weakness."`,                                                                                                            topics:['strength','faith'] },
  { ref:'Joshua 1:9',           text:`"Be strong and courageous. Do not be afraid. Do not be dismayed, for the LORD your God is with you wherever you go."`,                                                                   topics:['strength','fear anxiety','faith'] },
];
var BIBLE_REF_RE = /^((\d\s)?[a-z]+\.?\s*\d+(\s*:\s*\d+(\s*-\s*\d+)?)?)/i;
var bibleActiveTopic = '';

// ─── Scroll-lock helper ──────────────────────────────────────────────────────
// Counts how many overlays are currently open so that the body scroll is only
// restored when the LAST overlay closes (prevents the page freezing when one
// modal opens while another is already open, or when a close handler fires out
// of order during navigation).
let _scrollLockCount = 0;
function lockScroll() {
  _scrollLockCount++;
  if (_scrollLockCount === 1) {
    document.body.style.overflow = 'hidden';
  }
}
function unlockScroll() {
  if (_scrollLockCount <= 0) return;   // guard against unmatched unlocks
  _scrollLockCount--;
  if (_scrollLockCount === 0) {
    document.body.style.overflow = '';
  }
}
// Safety valve: call this if you are unsure of the current state (e.g. on page
// navigation) to fully reset the lock.
function resetScrollLock() {
  _scrollLockCount = 0;
  document.body.style.overflow = '';
}

// ─── Modal animation helper ───────────────────────────────────────────────────
// Adds .modal-is-closing to `overlayEl` (triggers CSS exit animation), waits
// for it to finish, then hides the overlay and calls `callback`.
function animatedModalClose(overlayEl, callback) {
  if (!overlayEl || overlayEl.hidden) { callback && callback(); return; }
  overlayEl.classList.add('modal-is-closing');
  // 240 ms covers the 0.22 s modalBoxOut animation with a small safety buffer.
  setTimeout(() => {
    overlayEl.classList.remove('modal-is-closing');
    overlayEl.hidden = true;
    callback && callback();
  }, 240);
}

// ─── Pending Approval Modal ──────────────────────────────────────────────────
function showPendingApprovalModal(message, { title, icon } = {}) {
  const modal = document.getElementById('pendingApprovalModal');
  if (!modal) return;
  const body = document.getElementById('pendingApprovalBody');
  if (body && message) body.innerHTML = message;
  const titleEl = document.getElementById('pendingApprovalTitle');
  if (titleEl && title) titleEl.textContent = title;
  const iconEl = modal.querySelector('.pending-approval-icon i[data-lucide]');
  if (iconEl && icon) {
    iconEl.setAttribute('data-lucide', icon);
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [iconEl.parentElement] });
  }
  modal.hidden = false;
  lockScroll();
  document.getElementById('pendingApprovalOkBtn')?.focus();
}

function resetPendingApprovalModal() {
  const titleEl = document.getElementById('pendingApprovalTitle');
  if (titleEl) titleEl.textContent = 'Post Submitted!';
  const modal = document.getElementById('pendingApprovalModal');
  const iconEl = modal?.querySelector('.pending-approval-icon i[data-lucide]');
  if (iconEl) {
    iconEl.setAttribute('data-lucide', 'clock');
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [iconEl.parentElement] });
  }
}

function closePendingApprovalModal() {
  const modal = document.getElementById('pendingApprovalModal');
  if (!modal) return;
  animatedModalClose(modal, () => {
    unlockScroll();
    resetPendingApprovalModal();
  });
}

// Wires up the pending approval modal close handlers (called once on DOMContentLoaded)
(function initPendingApprovalModal() {
  document.getElementById('pendingApprovalOkBtn')
    ?.addEventListener('click', closePendingApprovalModal);
  document.getElementById('pendingApprovalBackdrop')
    ?.addEventListener('click', closePendingApprovalModal);
  // Escape key support
  document.addEventListener('keydown', (e) => {
    const modal = document.getElementById('pendingApprovalModal');
    if (e.key === 'Escape' && modal && !modal.hidden) closePendingApprovalModal();
  });
})();

// ─── Utilities ───────────────────────────────────────────────────────────────

async function apiFetch(path, options = {}) {
  const res = await fetch(BASE_URL + path, {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    ...options,
  });
  return res.json();
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

// ─── SPA Router ──────────────────────────────────────────────────────────────

const pages      = document.querySelectorAll('.page');
const navButtons = document.querySelectorAll('[data-page]');

// ─── Quizzes — data & state declared here so they are available before the
//     loaders map and initHashRouting fire (avoids temporal dead zone) ────────

const QUIZ_DATA = [
  {
    id: 'life-of-jesus',
    title: 'Life of Jesus',
    description: 'Key moments from the Gospels',
    category: 'New Testament',
    questions: [
      { q: 'Where was Jesus born?', choices: ['Bethlehem','Nazareth','Jerusalem','Capernaum'], answer: 0 },
      { q: 'Who baptized Jesus?', choices: ['Peter','Paul','John the Baptist','Elijah'], answer: 2 },
      { q: 'How many days did Jesus fast in the wilderness?', choices: ['20','30','40','50'], answer: 2 },
      { q: 'How many disciples did Jesus call?', choices: ['7','10','12','14'], answer: 2 },
      { q: 'What was Jesus\'s first miracle?', choices: ['Healing a blind man','Feeding 5,000','Walking on water','Turning water into wine'], answer: 3 },
      { q: 'Where did Jesus deliver the Sermon on the Mount?', choices: ['Jerusalem','Galilee','Judea','Samaria'], answer: 1 },
      { q: 'Who betrayed Jesus?', choices: ['Peter','Thomas','Judas Iscariot','James'], answer: 2 },
      { q: 'On what day did Jesus rise from the dead?', choices: ['First day','Second day','Third day','Fourth day'], answer: 2 },
      { q: 'Where did Jesus ascend into heaven?', choices: ['Bethlehem','Galilee','Bethany','Jerusalem'], answer: 2 },
      { q: 'What did the angel say to the shepherds at Jesus\'s birth?', choices: ['"Fear not"','"Rejoice"','"Be still"','"Hallelujah"'], answer: 0 },
    ],
  },
  {
    id: 'kings-of-israel',
    title: 'Kings of Israel',
    description: 'From Saul to Solomon',
    category: 'Old Testament',
    questions: [
      { q: 'Who was the first king of Israel?', choices: ['David','Solomon','Saul','Samuel'], answer: 2 },
      { q: 'Who anointed the first king of Israel?', choices: ['Elijah','Moses','Samuel','Nathan'], answer: 2 },
      { q: 'Which king defeated Goliath?', choices: ['Saul','Solomon','David','Jonathan'], answer: 2 },
      { q: 'Who was David\'s son and third king of Israel?', choices: ['Absalom','Nathan','Solomon','Rehoboam'], answer: 2 },
      { q: 'What gift did God give Solomon when asked?', choices: ['Long life','Riches','Wisdom','Victory in battle'], answer: 2 },
      { q: 'How many wives did Solomon have?', choices: ['100','300','700','1,000'], answer: 2 },
      { q: 'What was Saul\'s tribe?', choices: ['Judah','Levi','Benjamin','Ephraim'], answer: 2 },
      { q: 'Who was David\'s best friend?', choices: ['Abner','Jonathan','Joab','Nathan'], answer: 1 },
    ],
  },
  {
    id: 'daily-challenge',
    title: 'Daily Challenge',
    description: 'New set every day',
    category: 'New Testament',
    questions: [
      { q: 'What is the shortest verse in the Bible?', choices: ['"Jesus wept."','"Amen."','"Rejoice."','"Trust God."'], answer: 0 },
      { q: 'How many books are in the New Testament?', choices: ['24','27','30','39'], answer: 1 },
      { q: 'Who wrote the book of Revelation?', choices: ['Paul','Peter','James','John'], answer: 3 },
      { q: 'What language was most of the Old Testament written in?', choices: ['Aramaic','Greek','Latin','Hebrew'], answer: 3 },
      { q: 'How many days did creation take according to Genesis?', choices: ['5','6','7','8'], answer: 1 },
      { q: 'Which apostle denied Jesus three times?', choices: ['John','James','Thomas','Peter'], answer: 3 },
      { q: 'What river did Jesus get baptized in?', choices: ['Nile','Euphrates','Jordan','Tigris'], answer: 2 },
      { q: 'Who was swallowed by a great fish?', choices: ['Elijah','Jonah','Isaiah','Ezekiel'], answer: 1 },
      { q: 'What was the name of Abraham\'s wife?', choices: ['Rachel','Leah','Rebekah','Sarah'], answer: 3 },
      { q: 'On which mountain did Moses receive the Ten Commandments?', choices: ['Sinai','Zion','Carmel','Ararat'], answer: 0 },
      { q: 'Who is the author of most of the Psalms?', choices: ['Solomon','Moses','David','Asaph'], answer: 2 },
      { q: 'How many loaves did Jesus use to feed the 5,000?', choices: ['2','5','7','12'], answer: 1 },
    ],
  },
  {
    id: 'parables',
    title: 'Parables',
    description: 'Stories Jesus told',
    category: 'Parables',
    questions: [
      { q: 'In the Parable of the Prodigal Son, who ran to meet the returning son?', choices: ['Older brother','Servant','Father','Mother'], answer: 2 },
      { q: 'In the Parable of the Sower, what does the seed represent?', choices: ['Faith','Prayer','The Word of God','Love'], answer: 2 },
      { q: 'In the Parable of the Talents, how many talents did the lazy servant receive?', choices: ['1','2','5','10'], answer: 0 },
      { q: 'Who was the neighbor in the Parable of the Good Samaritan?', choices: ['A priest','A Levite','A Samaritan','A soldier'], answer: 2 },
      { q: 'In the Parable of the Ten Virgins, how many had no oil?', choices: ['3','5','7','10'], answer: 1 },
      { q: 'What does the lost sheep parable illustrate?', choices: ['God\'s justice','God\'s joy over one repentant sinner','Hard work','Community'], answer: 1 },
      { q: 'In the Parable of the Mustard Seed, what does it grow into?', choices: ['A vine','A large tree','A forest','A garden'], answer: 1 },
      { q: 'What did the Prodigal Son ask for before leaving?', choices: ['A house','His inheritance','Servants','A horse'], answer: 1 },
      { q: 'In the Parable of the Pearl, what did the merchant do to buy it?', choices: ['Borrowed money','Traded goods','Sold everything he had','Stole it'], answer: 2 },
    ],
  },
  {
    id: 'miracles',
    title: 'Miracles',
    description: 'Signs and wonders',
    category: 'New Testament',
    questions: [
      { q: 'What was the first plague of Egypt?', choices: ['Frogs','Darkness','Water turned to blood','Locusts'], answer: 2 },
      { q: 'How many fish did Simon catch after Jesus told him to cast on the right side?', choices: ['100','127','153','200'], answer: 2 },
      { q: 'Who did Jesus raise from the dead in Bethany?', choices: ['Jairus','Lazarus','Stephen','Tabitha'], answer: 1 },
      { q: 'What did Jesus walk on?', choices: ['Sand','Fire','Water','Air'], answer: 2 },
      { q: 'How many loaves and fish fed 5,000 people?', choices: ['2 loaves 1 fish','5 loaves 2 fish','7 loaves 4 fish','3 loaves 5 fish'], answer: 1 },
      { q: 'Who parted the Red Sea?', choices: ['Aaron','Joshua','Elijah','Moses'], answer: 3 },
      { q: 'What did Jesus turn water into?', choices: ['Milk','Honey','Wine','Oil'], answer: 2 },
      { q: 'Which prophet called fire down from heaven on Mount Carmel?', choices: ['Elisha','Isaiah','Jeremiah','Elijah'], answer: 3 },
      { q: 'Who healed a man lame from birth at the temple gate?', choices: ['Paul','Stephen','Peter','James'], answer: 2 },
      { q: 'Jesus healed ten lepers — how many returned to give thanks?', choices: ['1','2','5','10'], answer: 0 },
    ],
  },
  {
    id: 'women-of-the-bible',
    title: 'Women of the Bible',
    description: 'Faith and courage',
    category: 'Characters',
    questions: [
      { q: 'Who said "Where you go I will go"?', choices: ['Esther','Mary','Ruth','Naomi'], answer: 2 },
      { q: 'Which woman hid the Israelite spies in Jericho?', choices: ['Deborah','Rahab','Abigail','Huldah'], answer: 1 },
      { q: 'Who was the first woman judge of Israel?', choices: ['Miriam','Esther','Deborah','Huldah'], answer: 2 },
      { q: 'Which queen approached the king unsummoned to save her people?', choices: ['Bathsheba','Jezebel','Vashti','Esther'], answer: 3 },
      { q: 'Who was the mother of John the Baptist?', choices: ['Anna','Mary','Elizabeth','Salome'], answer: 2 },
      { q: 'What was the name of Abraham\'s Egyptian servant?', choices: ['Keturah','Hagar','Zilpah','Bilhah'], answer: 1 },
      { q: 'Who anointed Jesus\'s feet with expensive perfume?', choices: ['Martha','Salome','Mary Magdalene','Mary of Bethany'], answer: 3 },
      { q: 'Which prophetess was Moses\'s sister?', choices: ['Deborah','Huldah','Anna','Miriam'], answer: 3 },
    ],
  },
  {
    id: 'journeys-of-paul',
    title: 'Journeys of Paul',
    description: 'Missionary travels',
    category: 'Characters',
    questions: [
      { q: 'What was Paul\'s name before his conversion?', choices: ['Barnabas','Silas','Saul','Philip'], answer: 2 },
      { q: 'On which road did Paul have his conversion experience?', choices: ['Road to Jericho','Road to Damascus','Road to Rome','Road to Corinth'], answer: 1 },
      { q: 'Who was Paul\'s companion on his first missionary journey?', choices: ['Silas','Luke','Barnabas','Timothy'], answer: 2 },
      { q: 'In which city was Paul and Silas imprisoned and then freed by an earthquake?', choices: ['Corinth','Philippi','Athens','Ephesus'], answer: 1 },
      { q: 'Which epistle did Paul write while in prison?', choices: ['Romans','Galatians','Philippians','1 Corinthians'], answer: 2 },
      { q: 'On which island was Paul shipwrecked?', choices: ['Cyprus','Crete','Malta','Rhodes'], answer: 2 },
      { q: 'Who was Paul\'s young protégé whom he wrote two letters to?', choices: ['Titus','Timothy','Silas','Philemon'], answer: 1 },
      { q: 'In what city did Paul preach to the Areopagus about the "unknown God"?', choices: ['Rome','Corinth','Athens','Ephesus'], answer: 2 },
      { q: 'What trade did Paul practice to support himself?', choices: ['Fishing','Carpentry','Tentmaking','Farming'], answer: 2 },
    ],
  },
  {
    id: 'the-exodus',
    title: 'The Exodus',
    description: 'Out of Egypt',
    category: 'Old Testament',
    questions: [
      { q: 'How many plagues struck Egypt?', choices: ['7','8','10','12'], answer: 2 },
      { q: 'What did Moses\'s staff turn into?', choices: ['A fish','A serpent','A sword','A tree'], answer: 1 },
      { q: 'What food did God provide the Israelites in the wilderness?', choices: ['Manna and quail','Bread and fish','Figs and dates','Milk and honey'], answer: 0 },
      { q: 'How many years did the Israelites wander in the wilderness?', choices: ['20','30','40','50'], answer: 2 },
      { q: 'Who was Moses\'s brother?', choices: ['Joshua','Caleb','Aaron','Hur'], answer: 2 },
      { q: 'What marked the doorposts of Israelite homes during Passover?', choices: ['Oil','Lamb\'s blood','Water','Flour'], answer: 1 },
      { q: 'On which mountain did God give Moses the Law?', choices: ['Nebo','Carmel','Horeb / Sinai','Moriah'], answer: 2 },
      { q: 'What guided the Israelites at night in the wilderness?', choices: ['A star','A pillar of fire','The moon','Angels'], answer: 1 },
      { q: 'Who helped Moses hold up his arms during the battle against Amalek?', choices: ['Joshua and Caleb','Aaron and Hur','Miriam and Aaron','Phinehas and Eleazar'], answer: 1 },
      { q: 'Where was Moses when he saw the burning bush?', choices: ['Egypt','Sinai desert','Canaan','The Nile delta'], answer: 1 },
      { q: 'What was the golden object the Israelites built while Moses was on the mountain?', choices: ['An ox','A calf','A serpent','A lamb'], answer: 1 },
    ],
  },
];

// ── Quiz state ────────────────────────────────────────────────────────────────
let _quizActive   = null;
let _quizIdx      = 0;
let _quizScore    = 0;
let _quizAnswered = false;

const RESULT_VERSES = [
  { score: 1.0, text: '"Well done, good and faithful servant!" — Matthew 25:21' },
  { score: 0.7, text: '"I can do all things through Christ who strengthens me." — Philippians 4:13' },
  { score: 0.4, text: '"Keep seeking and you will find." — Matthew 7:7' },
  { score: 0.0, text: '"Trust in the Lord with all your heart." — Proverbs 3:5' },
];

// Map of page id → loader function (called once, lazily)
const loaders = {
  watch:        () => loadWatch(),
  read:         () => loadArticles(),
  bible:        () => loadBible(),
  quizzes:      () => loadQuizzes(),
  prayer:       () => loadPrayerWall(),
  events:       () => loadEvents(),
  announcement: () => loadAnnouncements(),
};
const loaded = new Set();

// Valid page ids — used to validate hash values
const validPages = new Set([
  'home', 'watch', 'read', 'bible', 'quizzes', 'prayer', 'events', 'announcement', 'about', 'connect'
]);

function goTo(pageId, pushHash = true) {
  const next = document.getElementById('page-' + pageId);
  if (!next) return;

  const current = document.querySelector('.page.active, .page.is-entering');
  if (current && current === next) return;

  // Update URL hash so refresh restores this page
  if (pushHash) {
    const hash = pageId === 'home' ? '#' : '#' + pageId;
    history.replaceState(null, '', hash);
  }

  // Update nav highlight
  document.querySelectorAll('nav.primary button').forEach(b =>
    b.classList.toggle('active', b.dataset.page === pageId)
  );
  // Highlight the parent group toggle when a child page is active
  document.querySelectorAll('.nav-group').forEach(group => {
    const hasActive = group.querySelector(`button[data-page="${pageId}"]`);
    group.querySelector('.nav-group-toggle')?.classList.toggle('active', !!hasActive);
  });
  document.getElementById('primaryNav').classList.remove('open');
  // Reset any scroll locks left behind by unclosed modals/drawers
  resetScrollLock();
  window.scrollTo({ top: 0, behavior: 'instant' });

  // Hide current instantly — no display toggle, just class swap
  if (current) {
    current.classList.remove('active', 'is-entering');
    // Remove hero-animate so it replays next time home is visited
    current.classList.remove('hero-animate');
    // Remove watch-animate so it replays next time watch is visited
    current.classList.remove('watch-animate');
    // Remove events-animate so it replays next time events is visited
    current.classList.remove('events-animate');
    // Remove read-animate so it replays next time read is visited
    current.classList.remove('read-animate');
    // Remove bible-animate so it replays next time bible is visited
    current.classList.remove('bible-animate');
    // Remove quizzes-animate so it replays next time quizzes is visited
    current.classList.remove('quizzes-animate');
    // Remove prayer-animate so it replays next time prayer is visited
    current.classList.remove('prayer-animate');
    // Remove announcement-animate so it replays next time announcement is visited
    current.classList.remove('announcement-animate');
    // Remove about-animate so it replays next time about is visited
    current.classList.remove('about-animate');
    // Remove connect-animate so it replays next time connect is visited
    current.classList.remove('connect-animate');
  }

  // Animate next in — visibility/opacity driven, no display change
  next.classList.add('is-entering');

  function activatePage() {
    next.classList.remove('is-entering');
    next.classList.add('active');
    // Trigger page-specific hero animations AFTER the page is fully visible,
    // so child elements are not still inside a height:0 / overflow:hidden container.
    void next.offsetWidth; // force reflow to restart animations cleanly
    const animClass = {
      home: 'hero-animate', watch: 'watch-animate', events: 'events-animate',
      read: 'read-animate', bible: 'bible-animate', prayer: 'prayer-animate',
      announcement: 'announcement-animate', about: 'about-animate', connect: 'connect-animate',
      quizzes: 'quizzes-animate',
    }[pageId];
    if (animClass) next.classList.add(animClass);
  }

  // Fire on the page's own animationend; fallback after 500ms in case it never fires
  const fallback = setTimeout(activatePage, 500);
  next.addEventListener('animationend', (e) => {
    if (e.target !== next) return;
    clearTimeout(fallback);
    activatePage();
  }, { once: true });

  // Lazy-load data for the page on first visit (don't wait for animation)
  if (loaders[pageId] && !loaded.has(pageId)) {
    loaded.add(pageId);
    loaders[pageId]();
  }
}

navButtons.forEach(btn => {
  btn.addEventListener('click', () => goTo(btn.dataset.page));
});

// Restore page from URL hash on load (handles refresh & direct links)
(function initHashRouting() {
  const hash   = window.location.hash.replace('#', '').trim();
  const pageId = validPages.has(hash) ? hash : 'home';
  
  // On initial load, manually trigger hero animation for the active page
  const activePage = document.getElementById('page-' + pageId);
  if (activePage) {
    // Force animation class immediately on page load
    requestAnimationFrame(() => {
      const animClass = {
        home: 'hero-animate', watch: 'watch-animate', events: 'events-animate',
        read: 'read-animate', bible: 'bible-animate', prayer: 'prayer-animate',
        announcement: 'announcement-animate', about: 'about-animate', connect: 'connect-animate',
        quizzes: 'quizzes-animate',
      }[pageId];
      if (animClass) activePage.classList.add(animClass);
    });
    
    // Load data if needed
    if (loaders[pageId] && !loaded.has(pageId)) {
      loaded.add(pageId);
      loaders[pageId]();
    }
  }
  
  // Show the correct page without pushing the hash again
  // (only needed if hash doesn't match the server-rendered active page)
  if (pageId !== 'home') {
    goTo(pageId, false);
  }

  // Keep hash up-to-date for the initial page
  const initialHash = pageId === 'home' ? '#' : '#' + pageId;
  history.replaceState(null, '', initialHash);
})();

// Handle browser back / forward navigation
window.addEventListener('hashchange', () => {
  const hash   = window.location.hash.replace('#', '').trim();
  const pageId = validPages.has(hash) ? hash : 'home';
  goTo(pageId, false);
});

// One-time modal initialisation (modals live at body level, always in the DOM)
initAnnouncementModal();

document.getElementById('menuToggle').addEventListener('click', () => {
  document.getElementById('primaryNav').classList.toggle('open');
});

// Member chip dropdown toggle
document.addEventListener('click', e => {
  const chip     = document.getElementById('navMemberPill');
  const dropdown = document.getElementById('navMemberDropdown');
  if (!chip || !dropdown) return;
  if (e.target.closest('#navMemberPill')) {
    const isOpen = dropdown.classList.toggle('open');
    chip.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  } else {
    dropdown.classList.remove('open');
    chip.setAttribute('aria-expanded', 'false');
  }
});

// Dropdown nav shortcuts
(function () {
  const myProfileBtn = document.getElementById('dropdownMyProfile');
  const myPrayersBtn = document.getElementById('dropdownMyPrayers');

  function closeDropdown() {
    const chip     = document.getElementById('navMemberPill');
    const dropdown = document.getElementById('navMemberDropdown');
    if (dropdown) dropdown.classList.remove('open');
    if (chip)     chip.setAttribute('aria-expanded', 'false');
  }

  if (myProfileBtn) {
    myProfileBtn.addEventListener('click', () => {
      closeDropdown();
      openProfileDrawer();
    });
  }

  if (myPrayersBtn) {
    myPrayersBtn.addEventListener('click', () => {
      closeDropdown();
      openPrayerDrawer();
    });
  }
})();

// Sign-out confirmation modal
(function () {
  const signOutBtn    = document.getElementById('signOutBtn');
  const modal         = document.getElementById('signOutModal');
  const backdrop      = document.getElementById('signOutBackdrop');
  const cancelBtn     = document.getElementById('signOutCancel');
  const confirmBtn    = document.getElementById('signOutConfirm');
  const form          = document.getElementById('signOutForm');

  if (!signOutBtn || !modal) return;

  function openSignOut() {
    const dropdown = document.getElementById('navMemberDropdown');
    const chip     = document.getElementById('navMemberPill');
    if (dropdown) dropdown.classList.remove('open');
    if (chip)     chip.setAttribute('aria-expanded', 'false');
    modal.hidden = false;
    lockScroll();
    cancelBtn.focus();
  }

  function closeSignOut() {
    modal.hidden = true;
    unlockScroll();
  }

  signOutBtn.addEventListener('click', openSignOut);
  cancelBtn.addEventListener('click',  closeSignOut);
  backdrop.addEventListener('click',   closeSignOut);
  confirmBtn.addEventListener('click', () => form.submit());

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !modal.hidden) closeSignOut();
  });
})();

// ─── Watch & Listen ───────────────────────────────────────────────────────────

let featuredMedia = null; // store featured for modal

async function loadWatch() {
  await loadMediaGrid(null);
  bindMediaFilters();
  initAddVideoModal();
  initEditVideoModal();
  initDeleteVideoModal();
}

async function loadFeatured() {
  const res = await apiFetch('/media/featured');
  if (res.status !== 'success' || !res.data) return;

  featuredMedia = res.data;
  const m = featuredMedia;

  document.getElementById('featuredTag').textContent  = m.series
    ? `${m.type.charAt(0).toUpperCase() + m.type.slice(1)} · Series: ${m.series}`
    : m.type;
  document.getElementById('featuredTitle').textContent = m.title;
  document.getElementById('featuredDesc').textContent  = m.description;
  document.getElementById('featuredBtn').innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:4px"><polygon points="6 3 20 12 6 21 6 3"/></svg> Watch now · ${m.duration_label}`;

  // Make entire featured card clickable
  const card = document.getElementById('mediaFeatured');
  card.addEventListener('click', () => openVideoModal(m));
}

async function loadMediaGrid(type) {
  const grid = document.getElementById('mediaGrid');
  grid.innerHTML = '<p style="color:var(--text-on-light-dim); padding:20px 0;">Loading…</p>';

  const query = type ? `?type=${encodeURIComponent(type)}` : '';
  const res   = await apiFetch('/media' + query);

  if (res.status !== 'success' || !res.data.length) {
    grid.innerHTML = '<p style="color:var(--text-on-light-dim); padding:20px 0;">No media found.</p>';
    return;
  }

  grid.innerHTML = res.data.map(m => {
    const thumbUrl       = resolveThumbnail(m);
    const ADMIN_DISPLAY  = 'Agape House Ministries';
    const ADMIN_LOGO_URL = window.APP_BASE_URL + '/public/images/agape1.jpg';
    const isAdminPoster  = !m.poster_username && (
      !m.posted_by ||
      m.posted_by === 'admin' ||
      m.posted_by === 'Agape House' ||
      m.posted_by === 'Agape House Ministries'
    );
    const authorName    = isAdminPoster ? ADMIN_DISPLAY : (m.posted_by || ADMIN_DISPLAY);
    const authorInitial = authorName[0].toUpperCase();
    const posterPic     = isAdminPoster ? ADMIN_LOGO_URL : (m.poster_picture || null);

    const avatarHtml = posterPic
      ? `<div class="mc-poster-avatar mc-poster-avatar--img"><img src="${escHtml(posterPic)}" alt="${escHtml(authorInitial)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;"></div>`
      : `<div class="mc-poster-avatar">${escHtml(authorInitial)}</div>`;

    const typeLabel = { sermon: 'Sermon', devotional: 'Devotional', testimony: 'Testimony', worship: 'Worship' }[m.type] || m.type;

    // Only show edit button to the member who uploaded this video
    const currentId = window.CURRENT_MEMBER?.id ?? null;
    const canEdit   = currentId !== null && m.member_id !== null && m.member_id === currentId;

    return `
    <div class="media-card" data-id="${m.id}">
      <div class="thumb" style="cursor:pointer;" role="button" aria-label="Play ${escHtml(m.title)}">
        ${thumbUrl ? `<img src="${escHtml(thumbUrl)}" alt="${escHtml(m.title)}" loading="lazy" onerror="this.style.display='none'">` : ''}
        <span class="thumb-tag">${escHtml(typeLabel)}</span>
        <div class="thumb-overlay">
          <div class="thumb-play"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3"/></svg></div>
        </div>
        <span class="duration">${escHtml(m.duration_label)}</span>
      </div>
      <div class="mc-poster-row">
        ${avatarHtml}
        <div class="mc-poster-info">
          <span class="mc-poster-name">${escHtml(authorName)}</span>
          <span class="mc-poster-meta">${escHtml(typeLabel)}${m.series ? ' · ' + escHtml(m.series) : ''}</span>
        </div>
      </div>
      <div class="body">
        <h3>${escHtml(m.title)}</h3>
        <p>${escHtml(m.description)}</p>
        ${canEdit ? `
        <div class="media-action-btns">
          <button class="media-edit-btn" data-id="${m.id}" title="Edit video">✎ Edit</button>
          <button class="media-delete-btn" data-id="${m.id}" title="Delete video">🗑 Delete</button>
        </div>` : ''}
      </div>
    </div>
  `
  }).join('');

  // Bind thumb click → play
  grid.querySelectorAll('.media-card .thumb').forEach(thumb => {
    thumb.addEventListener('click', () => {
      const id = parseInt(thumb.closest('.media-card').dataset.id, 10);
      const m  = res.data.find(x => x.id === id);
      if (m) openVideoModal(m);
    });
  });

  // Add "Show more" button for truncated descriptions
  grid.querySelectorAll('.media-card p').forEach(p => {
    // Check if text is actually truncated (scrollHeight > clientHeight)
    if (p.scrollHeight > p.clientHeight + 2) { // +2 for rounding tolerance
      const btn = document.createElement('button');
      btn.className = 'show-more-btn';
      btn.textContent = 'Show more';
      btn.addEventListener('click', () => {
        if (p.classList.contains('expanded')) {
          p.classList.remove('expanded');
          btn.textContent = 'Show more';
        } else {
          p.classList.add('expanded');
          btn.textContent = 'Show less';
        }
      });
      p.parentElement.insertBefore(btn, p.nextSibling);
    }
  });

  // Bind edit button → open edit modal pre-filled
  grid.querySelectorAll('.media-edit-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const id = parseInt(btn.dataset.id, 10);
      const m  = res.data.find(x => x.id === id);
      if (m) openEditVideoModal(m);
    });
  });

  // Bind delete button → open confirmation modal
  grid.querySelectorAll('.media-delete-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const id    = parseInt(btn.dataset.id, 10);
      const m     = res.data.find(x => x.id === id);
      const cardEl = btn.closest('.media-card');
      if (m) openDeleteVideoModal(m, cardEl);
    });
  });
}

function bindMediaFilters() {
  document.querySelectorAll('#mediaFilterRow .filter-pill').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('#mediaFilterRow .filter-pill')
        .forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      loadMediaGrid(pill.dataset.type || null);
    });
  });
}

// ── Add Video Modal ───────────────────────────────────────────────────────────

let _uploadedVideoUrl = null; // holds the server path after a successful file upload

function initAddVideoModal() {
  // Default publish date to now
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  const dateInput = document.getElementById('vidDate');
  if (dateInput) dateInput.value = now.toISOString().slice(0, 16);

  document.getElementById('openAddVideoBtn').addEventListener('click', openAddVideoModal);
  document.getElementById('addVideoModalClose').addEventListener('click', closeAddVideoModal);
  document.getElementById('addVideoCancelBtn').addEventListener('click', closeAddVideoModal);
  document.getElementById('addVideoModalBackdrop').addEventListener('click', closeAddVideoModal);
  document.getElementById('addVideoForm').addEventListener('submit', submitAddVideo);

  // Source tab switching
  document.getElementById('vidTabUrl').addEventListener('click', () => switchVidTab('url'));
  document.getElementById('vidTabFile').addEventListener('click', () => switchVidTab('file'));

  // Browse button
  document.getElementById('vidBrowseBtn').addEventListener('click', () => {
    document.getElementById('vidFile').click();
  });

  // File input change
  document.getElementById('vidFile').addEventListener('change', (e) => {
    if (e.target.files[0]) handleVideoFile(e.target.files[0]);
  });

  // Remove uploaded file
  document.getElementById('vidRemoveFile').addEventListener('click', resetFileUpload);

  // Drag & drop
  const area = document.getElementById('vidUploadArea');
  area.addEventListener('dragover', (e) => { e.preventDefault(); area.classList.add('drag-over'); });
  area.addEventListener('dragleave', () => area.classList.remove('drag-over'));
  area.addEventListener('drop', (e) => {
    e.preventDefault();
    area.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('video/')) handleVideoFile(file);
  });
}

function switchVidTab(tab) {
  const isUrl = tab === 'url';
  document.getElementById('vidTabUrl').classList.toggle('active', isUrl);
  document.getElementById('vidTabFile').classList.toggle('active', !isUrl);
  document.getElementById('vidPanelUrl').hidden = !isUrl;
  document.getElementById('vidPanelFile').hidden = isUrl;
  if (isUrl) _uploadedVideoUrl = null;
}

async function handleVideoFile(file) {
  const maxBytes = 500 * 1024 * 1024;
  const msg = document.getElementById('addVideoMsg');

  if (!file.type.startsWith('video/')) {
    showFormMsg(msg, 'Please select a valid video file.', 'error');
    return;
  }
  if (file.size > maxBytes) {
    showFormMsg(msg, 'File exceeds the 500 MB limit.', 'error');
    return;
  }

  // Show progress UI
  document.getElementById('vidUploadPlaceholder').hidden = true;
  document.getElementById('vidUploadProgress').hidden = false;
  document.getElementById('vidUploadDone').hidden = true;
  document.getElementById('vidProgressFill').style.width = '0%';
  document.getElementById('vidUploadStatus').textContent = 'Uploading…';

  const formData = new FormData();
  formData.append('video', file);

  try {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', `${BASE_URL}/media/upload`);

    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        const pct = Math.round((e.loaded / e.total) * 100);
        document.getElementById('vidProgressFill').style.width = pct + '%';
        document.getElementById('vidUploadStatus').textContent = `Uploading… ${pct}%`;
      }
    });

    xhr.onload = () => {
      const res = JSON.parse(xhr.responseText);
      if (res.status === 'success') {
        _uploadedVideoUrl = res.data.video_url;
        document.getElementById('vidUploadProgress').hidden = true;
        document.getElementById('vidUploadDone').hidden = false;
        document.getElementById('vidUploadFileName').textContent = file.name;
      } else {
        resetFileUpload();
        showFormMsg(msg, res.message || 'Upload failed.', 'error');
      }
    };
    xhr.onerror = () => {
      resetFileUpload();
      showFormMsg(msg, 'Network error during upload.', 'error');
    };
    xhr.send(formData);
  } catch (err) {
    resetFileUpload();
    showFormMsg(msg, 'Upload error. Please try again.', 'error');
  }
}

function resetFileUpload() {
  _uploadedVideoUrl = null;
  document.getElementById('vidFile').value = '';
  document.getElementById('vidUploadPlaceholder').hidden = false;
  document.getElementById('vidUploadProgress').hidden = true;
  document.getElementById('vidUploadDone').hidden = true;
  document.getElementById('vidProgressFill').style.width = '0%';
}

function openAddVideoModal() {
  document.getElementById('addVideoModal').hidden = false;
  lockScroll();
  document.getElementById('vidTitle').focus();
}

function closeAddVideoModal() {
  document.getElementById('addVideoModal').hidden = true;
  unlockScroll();
  const msg = document.getElementById('addVideoMsg');
  msg.hidden = true;
  msg.textContent = '';
  _uploadedVideoUrl = null;
  resetFileUpload();
  switchVidTab('url');
}

async function submitAddVideo(e) {
  e.preventDefault();
  const btn = document.getElementById('addVideoSubmitBtn');
  const msg = document.getElementById('addVideoMsg');

  const title        = document.getElementById('vidTitle').value.trim();
  const type         = document.getElementById('vidType').value;
  const series       = document.getElementById('vidSeries').value.trim();
  const description  = document.getElementById('vidDesc').value.trim();
  const published_at = document.getElementById('vidDate').value || null;

  // Resolve video_url from whichever tab is active
  const fileTabActive = !document.getElementById('vidPanelFile').hidden;
  const video_url = fileTabActive
    ? (_uploadedVideoUrl || '')
    : document.getElementById('vidUrl').value.trim();

  if (fileTabActive && !_uploadedVideoUrl) {
    showFormMsg(msg, 'Please wait for the upload to finish, or switch to URL tab.', 'error');
    return;
  }

  if (!title) { showFormMsg(msg, 'Title is required.', 'error'); return; }
  if (!type)  { showFormMsg(msg, 'Please choose a type.', 'error'); return; }

  btn.disabled    = true;
  btn.textContent = 'Publishing…';
  msg.hidden      = true;

  try {
    const res = await apiFetch('/media', {
      method: 'POST',
      body: JSON.stringify({
        title,
        type,
        series,
        description,
        video_url,
        duration:     0,
        published_at,
      }),
    });

    if (res.status === 'success') {
      const isPending = res.message && res.message.toLowerCase().includes('review');

      document.getElementById('addVideoForm').reset();
      _uploadedVideoUrl = null;
      resetFileUpload();
      switchVidTab('url');
      // Reset date field
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      document.getElementById('vidDate').value = now.toISOString().slice(0, 16);

      if (isPending) {
        // Member submission — close the form then show the pending approval modal
        closeAddVideoModal();
        showPendingApprovalModal(
          'Your video has been received and is waiting for admin approval.<br>It will appear publicly once it\'s reviewed.'
        );
        // Still refresh the grid in the background (pending items won't show, but keeps state fresh)
        const activePill = document.querySelector('#mediaFilterRow .filter-pill.active');
        const activeType = activePill ? (activePill.dataset.type || null) : null;
        loadMediaGrid(activeType);
      } else {
        // Admin post — show inline success then close
        showFormMsg(msg, 'Video published!', 'success');
        setTimeout(async () => {
          closeAddVideoModal();
          const activePill = document.querySelector('#mediaFilterRow .filter-pill.active');
          const activeType = activePill ? (activePill.dataset.type || null) : null;
          await loadMediaGrid(activeType);
        }, 800);
      }
    } else {
      showFormMsg(msg, res.message || 'Could not publish. Try again.', 'error');
    }
  } catch (err) {
    showFormMsg(msg, 'Network error. Please try again.', 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Publish Video';
  }
}

// ── Edit Video Modal ──────────────────────────────────────────────────────────

let _editingMediaId = null;

function openEditVideoModal(m) {
  _editingMediaId = m.id;

  // Pre-fill the edit form
  document.getElementById('editVidTitle').value    = m.title        || '';
  document.getElementById('editVidType').value     = m.type         || '';
  document.getElementById('editVidSeries').value   = m.series       || '';
  document.getElementById('editVidDesc').value     = m.description  || '';
  document.getElementById('editVidUrl').value      = m.video_url    || '';

  const msg = document.getElementById('editVideoMsg');
  msg.hidden = true; msg.textContent = '';

  document.getElementById('editVideoModal').hidden = false;
  lockScroll();
  document.getElementById('editVidTitle').focus();
}

function closeEditVideoModal() {
  document.getElementById('editVideoModal').hidden = true;
  unlockScroll();
  _editingMediaId = null;
}

function initEditVideoModal() {
  document.getElementById('editVideoModalClose').addEventListener('click', closeEditVideoModal);
  document.getElementById('editVideoCancelBtn').addEventListener('click', closeEditVideoModal);
  document.getElementById('editVideoModalBackdrop').addEventListener('click', closeEditVideoModal);
  document.getElementById('editVideoForm').addEventListener('submit', submitEditVideo);
}

async function submitEditVideo(e) {
  e.preventDefault();
  if (!_editingMediaId) return;

  const btn = document.getElementById('editVideoSubmitBtn');
  const msg = document.getElementById('editVideoMsg');

  const title        = document.getElementById('editVidTitle').value.trim();
  const type         = document.getElementById('editVidType').value;
  const series       = document.getElementById('editVidSeries').value.trim();
  const description  = document.getElementById('editVidDesc').value.trim();
  const video_url    = document.getElementById('editVidUrl').value.trim();

  if (!title) { showFormMsg(msg, 'Title is required.', 'error'); return; }
  if (!type)  { showFormMsg(msg, 'Please choose a type.', 'error'); return; }

  btn.disabled = true; btn.textContent = 'Saving…'; msg.hidden = true;

  try {
    const res = await apiFetch(`/media/${_editingMediaId}`, {
      method: 'PATCH',
      body: JSON.stringify({ title, type, series, description, video_url }),
    });

    if (res.status === 'success') {
      showFormMsg(msg, 'Video updated!', 'success');
      setTimeout(async () => {
        closeEditVideoModal();
        const activePill = document.querySelector('#mediaFilterRow .filter-pill.active');
        const activeType = activePill ? (activePill.dataset.type || null) : null;
        await Promise.all([loadFeatured(), loadMediaGrid(activeType)]);
      }, 700);
    } else {
      showFormMsg(msg, res.message || 'Could not update. Try again.', 'error');
    }
  } catch (err) {
    showFormMsg(msg, 'Network error. Please try again.', 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Save Changes';
  }
}

// ── Delete Video Modal ────────────────────────────────────────────────────────

let _deletingMedia = null; // { id, title, cardEl }

function openDeleteVideoModal(m, cardEl) {
  _deletingMedia = { id: m.id, title: m.title, cardEl };
  document.getElementById('deleteVideoTitle').textContent = m.title;
  const msg = document.getElementById('deleteVideoMsg');
  msg.hidden = true; msg.textContent = '';
  const confirmBtn = document.getElementById('deleteConfirmBtn');
  confirmBtn.disabled = false; confirmBtn.textContent = 'Yes, Delete';
  document.getElementById('deleteVideoModal').hidden = false;
  lockScroll();
}

function closeDeleteVideoModal() {
  document.getElementById('deleteVideoModal').hidden = true;
  unlockScroll();
  _deletingMedia = null;
}

async function confirmDeleteVideo() {
  if (!_deletingMedia) return;

  const { id, title, cardEl } = _deletingMedia;
  const confirmBtn = document.getElementById('deleteConfirmBtn');
  const msg        = document.getElementById('deleteVideoMsg');

  confirmBtn.disabled = true;
  confirmBtn.textContent = 'Deleting…';
  msg.hidden = true;

  try {
    const result = await apiFetch(`/media/${id}`, { method: 'DELETE' });

    if (result.status === 'success') {
      closeDeleteVideoModal();
      // Remove the card instantly, then reload grid if empty
      if (cardEl) cardEl.remove();
      const grid = document.getElementById('mediaGrid');
      if (grid && !grid.querySelector('.media-card')) {
        const activePill = document.querySelector('#mediaFilterRow .filter-pill.active');
        const activeType = activePill ? (activePill.dataset.type || null) : null;
        await loadMediaGrid(activeType);
      }
    } else {
      showFormMsg(msg, result.message || 'Could not delete. Please try again.', 'error');
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Yes, Delete';
    }
  } catch (err) {
    showFormMsg(msg, 'Network error. Please try again.', 'error');
    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Yes, Delete';
  }
}

function initDeleteVideoModal() {
  document.getElementById('deleteCancelBtn').addEventListener('click', closeDeleteVideoModal);
  document.getElementById('deleteVideoModalBackdrop').addEventListener('click', closeDeleteVideoModal);
  document.getElementById('deleteConfirmBtn').addEventListener('click', confirmDeleteVideo);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !document.getElementById('deleteVideoModal').hidden) {
      closeDeleteVideoModal();
    }
  });
}

// ── Edit Video Modal ───────────────────────────────────────────────────────────

function initVideoModal() {
  document.getElementById('videoModalClose').addEventListener('click', closeVideoModal);
  document.getElementById('videoModalBackdrop').addEventListener('click', closeVideoModal);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeVideoModal();
  });
}

function getYouTubeId(url) {
  if (!url) return null;
  // Matches youtu.be/ID and youtube.com/watch?v=ID and /embed/ID
  const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([a-zA-Z0-9_-]{11})/);
  return m ? m[1] : null;
}

/** Return a thumbnail URL: use stored one if present, otherwise derive from video_url */
function resolveThumbnail(media) {
  if (media.thumbnail) return media.thumbnail;
  const ytId = getYouTubeId(media.video_url);
  return ytId ? `https://img.youtube.com/vi/${ytId}/hqdefault.jpg` : '';
}

function openVideoModal(media) {
  const modal    = document.getElementById('videoModal');
  const player   = document.getElementById('videoModalPlayer');
  const title    = document.getElementById('videoModalTitle');
  const meta     = document.getElementById('videoModalMeta');
  const desc     = document.getElementById('videoModalDesc');

  title.textContent = media.title;
  meta.textContent  = [
    media.series || media.type,
    media.duration_label,
  ].filter(Boolean).join(' · ');
  desc.textContent  = media.description;

  // Build the player
  const ytId = getYouTubeId(media.video_url);

  if (ytId) {
    // YouTube embed — try embedding first, with better error detection
    const wrapperId = 'yt-player-wrap';
    player.innerHTML = `
      <div id="${wrapperId}" style="width:100%;height:100%;position:relative;">
        <iframe
          id="yt-iframe"
          src="https://www.youtube.com/embed/${ytId}?autoplay=1&rel=0&modestbranding=1&enablejsapi=1"
          allow="autoplay; encrypted-media; fullscreen"
          frameborder="0"
          title="${escHtml(media.title)}"
          style="width:100%;height:100%;display:block;"
        ></iframe>
      </div>
    `;

    // Track if we've shown the fallback to prevent multiple triggers
    let fallbackShown = false;

    // Listen for YouTube's postMessage API — only show fallback on explicit error codes
    // (101 / 150 = embedding disabled by channel owner)
    const ytErrorHandler = function(event) {
      if (fallbackShown || !event.data) return;
      
      let data = event.data;
      if (typeof data === 'string') {
        try { data = JSON.parse(data); } catch (e) { return; }
      }
      
      // YouTube error codes: 2=invalid param, 5=HTML5 error, 100=not found, 101/150=not embeddable
      if (data.event === 'onError' && data.info) {
        const errorCode = typeof data.info === 'object' ? data.info.errorCode : data.info;
        if ([2, 5, 100, 101, 150].includes(errorCode)) {
          fallbackShown = true;
          window.removeEventListener('message', ytErrorHandler);
          showVideoFallback(player, media.video_url, media.title);
        }
      }
    };
    window.addEventListener('message', ytErrorHandler);

    // Detect same-origin iframe load (happens when YouTube blocks embedding and
    // redirects to an error page on the same origin — contentDocument becomes readable)
    const iframe = document.getElementById('yt-iframe');
    iframe.addEventListener('load', () => {
      if (fallbackShown) return;
      try {
        // If contentDocument is accessible, YouTube redirected to a local error page
        const doc = iframe.contentDocument;
        if (doc) {
          fallbackShown = true;
          window.removeEventListener('message', ytErrorHandler);
          showVideoFallback(player, media.video_url, media.title);
        }
      } catch { /* cross-origin = YouTube loaded normally, do nothing */ }
    });

    // Clean up listener when modal closes
    player._ytCleanup = () => {
      window.removeEventListener('message', ytErrorHandler);
    };

  } else if (media.video_url) {
    // Direct video file (MP4, etc.)
    player.innerHTML = `
      <video controls autoplay preload="metadata">
        <source src="${escHtml(media.video_url)}" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    `;
  } else {
    // No URL — show placeholder
    player.innerHTML = `
      <div class="video-no-url">
        <span><svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3"/></svg></span>
        <p>No video URL configured for this item.</p>
      </div>
    `;
  }

  // Clear any leftover closing state so the open animation plays cleanly
  modal.classList.remove('modal-is-closing');
  modal.hidden = false;
  lockScroll();
  document.getElementById('videoModalClose').focus();
}

/**
 * Replace the player area with a friendly "video unavailable" message
 * that clearly redirects the user to watch directly on YouTube.
 */
function showVideoFallback(player, videoUrl, title) {
  const ytId    = getYouTubeId(videoUrl);
  // Build a direct YouTube watch URL (handles both watch URLs and embed URLs)
  const ytWatch = ytId
    ? `https://www.youtube.com/watch?v=${ytId}`
    : (videoUrl || null);

  const watchLink = ytWatch
    ? `<a href="${escHtml(ytWatch)}" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="margin-top:20px;display:inline-flex;align-items:center;gap:8px;font-size:1rem;padding:14px 28px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
        Watch on YouTube
      </a>`
    : '';

  player.innerHTML = `
    <div class="video-no-url" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;padding:32px 24px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.5;margin-bottom:16px;" aria-hidden="true">
        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3 8 3M12 3v4M10 12l5 3-5 3V12z"/>
      </svg>
      <p style="font-weight:600;font-size:1.1rem;margin:0;">This video can't be embedded here.</p>
      <p style="margin-top:8px;opacity:.65;font-size:.9rem;max-width:320px;">The channel has restricted embedding. You can still watch the full video directly on YouTube.</p>
      ${watchLink}
    </div>
  `;
}

function closeVideoModal() {
  const modal  = document.getElementById('videoModal');
  const player = document.getElementById('videoModalPlayer');

  // Clean up YouTube error listeners / timers if present
  if (typeof player._ytCleanup === 'function') {
    player._ytCleanup();
    delete player._ytCleanup;
  }

  // Stop video playback immediately by clearing the player
  player.innerHTML = '';

  animatedModalClose(modal, () => {
    unlockScroll();
  });
}

// ─── Read / Articles ──────────────────────────────────────────────────────────

async function loadArticles() {
  const list = document.getElementById('articleList');
  const res  = await apiFetch('/articles');

  // Always init the modal so the "+ Add Article" button works
  // regardless of whether any articles exist yet
  initArticleModal();
  initAddArticleModal();

  if (res.status !== 'success' || !res.data.length) {
    list.innerHTML = '<p style="color:var(--text-on-light-dim);padding:20px 0;">No articles found.</p>';
    return;
  }

  renderArticleList(res.data);
}

function renderArticleList(articles) {
  const list = document.getElementById('articleList');

  list.innerHTML = articles.map((a, i) => {
    const pubDate = new Date(a.published_at);
    const diffMs  = Date.now() - pubDate.getTime();
    const diffD   = Math.floor(diffMs / 86400000);
    const when    = diffD === 0 ? 'TODAY' : diffD === 1 ? 'YESTERDAY' : `${diffD} DAYS AGO`;

    const ADMIN_DISPLAY  = 'Agape House Ministries';
    const ADMIN_LOGO_URL = window.APP_BASE_URL + '/public/images/agape1.jpg';
    const isAdminPoster  = !a.poster_username && (
      !a.posted_by ||
      a.posted_by === 'admin' ||
      a.posted_by === 'Agape House' ||
      a.posted_by === 'Agape House Ministries'
    );
    const authorName    = isAdminPoster ? ADMIN_DISPLAY : (a.posted_by || ADMIN_DISPLAY);
    const authorInitial = authorName[0].toUpperCase();
    const posterPic     = isAdminPoster ? ADMIN_LOGO_URL : (a.poster_picture || null);

    const avatarHtml = posterPic
      ? `<div class="mc-poster-avatar mc-poster-avatar--img"><img src="${escHtml(posterPic)}" alt="${escHtml(authorInitial)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;"></div>`
      : `<div class="mc-poster-avatar">${escHtml(authorInitial)}</div>`;

    return `
      <div class="article-card" data-id="${a.id}" role="button" tabindex="0" aria-label="Read ${escHtml(a.title)}" style="cursor:pointer;">
        <div class="num">${String(i + 1).padStart(2, '0')}</div>
        <div class="article-card-body">
          <h3>${escHtml(a.title)}</h3>
          <p>${escHtml(a.excerpt)}</p>
          <span class="meta">${a.read_minutes} MIN READ · ${when}</span>
        </div>
        <div class="article-card-right">
          <div class="article-card-author">
            ${avatarHtml}
            <span class="article-card-author-name">${escHtml(authorName)}</span>
          </div>
          <div class="article-card-arrow" aria-hidden="true">→</div>
        </div>
      </div>
    `;
  }).join('');

  // Store articles for lookup
  list._articles = articles;

  // Click / keyboard handlers
  list.querySelectorAll('.article-card').forEach(card => {
    const open = () => {
      const id = parseInt(card.dataset.id, 10);
      const a  = articles.find(x => x.id === id);
      if (a) openArticleModal(a);
    };
    card.addEventListener('click', open);
    card.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
  });
}

// ── Article Reader Modal ──────────────────────────────────────────────────────

let _articleModalInited = false;

function initArticleModal() {
  if (_articleModalInited) return;
  _articleModalInited = true;

  document.getElementById('articleModalClose').addEventListener('click', closeArticleModal);
  document.getElementById('articleModalBackdrop').addEventListener('click', closeArticleModal);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !document.getElementById('articleModal').hidden) closeArticleModal();
  });
}

function openArticleModal(article) {
  const modal = document.getElementById('articleModal');
  const pubDate = new Date(article.published_at);
  const diffMs  = Date.now() - pubDate.getTime();
  const diffD   = Math.floor(diffMs / 86400000);
  const when    = diffD === 0 ? 'Today' : diffD === 1 ? 'Yesterday'
                : pubDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

  document.getElementById('articleModalMeta').textContent =
    `${article.read_minutes} MIN READ · ${when.toUpperCase()}`;
  document.getElementById('articleModalTitle').textContent = article.title;

  // Render body — preserve paragraph breaks, escape HTML
  const paragraphs = article.body
    .split(/\n{2,}/)
    .map(p => `<p>${escHtml(p.trim()).replace(/\n/g, '<br>')}</p>`)
    .filter(p => p !== '<p></p>')
    .join('');
  document.getElementById('articleModalBody').innerHTML = paragraphs || `<p>${escHtml(article.body)}</p>`;

  // Clear any leftover closing state so the open animation plays cleanly
  modal.classList.remove('modal-is-closing');
  modal.hidden = false;
  lockScroll();
  document.getElementById('articleModalClose').focus();
}

function closeArticleModal() {
  animatedModalClose(document.getElementById('articleModal'), () => {
    unlockScroll();
  });
}

// ── Add Article Modal ─────────────────────────────────────────────────────────

let _addArticleModalInited = false;

function initAddArticleModal() {
  if (_addArticleModalInited) return;
  _addArticleModalInited = true;

  // Set default datetime to now (local)
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  const dateInput = document.getElementById('artDate');
  if (dateInput) dateInput.value = now.toISOString().slice(0, 16);

  document.getElementById('openAddArticleBtn').addEventListener('click', openAddArticleModal);
  document.getElementById('addArticleModalClose').addEventListener('click', closeAddArticleModal);
  document.getElementById('addArticleCancelBtn').addEventListener('click', closeAddArticleModal);
  document.getElementById('addArticleModalBackdrop').addEventListener('click', closeAddArticleModal);
  document.getElementById('addArticleForm').addEventListener('submit', submitAddArticle);
}

function openAddArticleModal() {
  // Redirect to login if session has expired
  if (!window.CURRENT_MEMBER) {
    window.location.href = (window.APP_BASE_URL || '') + '/member/login';
    return;
  }
  document.getElementById('addArticleModal').hidden = false;
  lockScroll();
  document.getElementById('artTitle').focus();
}

function closeAddArticleModal() {
  document.getElementById('addArticleModal').hidden = true;
  unlockScroll();
  const msg = document.getElementById('addArticleMsg');
  msg.hidden = true;
  msg.textContent = '';
}

async function submitAddArticle(e) {
  e.preventDefault();
  const btn = document.getElementById('addArticleSubmitBtn');
  const msg = document.getElementById('addArticleMsg');

  const title        = document.getElementById('artTitle').value.trim();
  const excerpt      = document.getElementById('artExcerpt').value.trim();
  const body         = document.getElementById('artBody').value.trim();
  const read_minutes = 5; // default
  const published_at = document.getElementById('artDate').value || null;

  // Basic validation
  if (!title)   { showFormMsg(msg, 'Title is required.', 'error'); return; }
  if (!excerpt) { showFormMsg(msg, 'Excerpt is required.', 'error'); return; }
  if (!body)    { showFormMsg(msg, 'Article body is required.', 'error'); return; }

  btn.disabled    = true;
  btn.textContent = 'Publishing…';
  msg.hidden      = true;

  try {
    const res = await apiFetch('/articles', {
      method: 'POST',
      body:   JSON.stringify({ title, excerpt, body, read_minutes, published_at }),
    });

    if (res.status === 'success') {
      const isPending = res.message && res.message.toLowerCase().includes('review');

      document.getElementById('addArticleForm').reset();
      // Set default date again after reset
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      document.getElementById('artDate').value = now.toISOString().slice(0, 16);

      if (isPending) {
        // Member submission — close the form then show the pending approval modal
        closeAddArticleModal();
        showPendingApprovalModal(
          'Your article has been received and is waiting for admin approval.<br>It will appear publicly once it\'s reviewed.'
        );
      } else {
        // Admin post — show inline success then close and reload
        showFormMsg(msg, 'Article published!', 'success');
        setTimeout(async () => {
          closeAddArticleModal();
          const listRes = await apiFetch('/articles');
          if (listRes.status === 'success') renderArticleList(listRes.data);
        }, 1000);
      }
    } else if (res.status === 'error' && res.message && res.message.toLowerCase().includes('signed in')) {
      // Session expired — tell the user and redirect to login
      showFormMsg(msg, 'Your session has expired. Redirecting to sign in…', 'error');
      setTimeout(() => {
        window.location.href = (window.APP_BASE_URL || '') + '/member/login';
      }, 1800);
    } else {
      showFormMsg(msg, res.message || 'Could not publish. Try again.', 'error');
    }
  } catch (err) {
    showFormMsg(msg, 'Network error. Please try again.', 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Publish Article';
  }
}

function showFormMsg(el, text, type) {
  el.textContent    = text;
  el.className      = `form-msg form-msg--${type}`;
  el.hidden         = false;
}


// ─── Bible Search ─────────────────────────────────────────────────────────────

function looksLikeReference(q) {
  return BIBLE_REF_RE.test(q.trim());
}

function getVOTD() {
  // Use the verse injected by PHP (window.VERSE_OF_THE_DAY) so home and
  // bible always show the exact same verse on the same day.
  if (window.VERSE_OF_THE_DAY) return window.VERSE_OF_THE_DAY;
  // Fallback: derive from BIBLE_VERSES if the global is somehow missing
  const now   = new Date();
  const start = new Date(now.getFullYear(), 0, 0);
  const day   = Math.floor((now - start) / 86400000);
  return BIBLE_VERSES[day % BIBLE_VERSES.length];
}

function loadBible() {
  // Render verse of the day
  const VOTD = getVOTD();
  document.getElementById('votdText').textContent = `"${VOTD.text}"`;
  document.getElementById('votdRef').textContent  = VOTD.ref;

  // Show curated verses on first load
  renderBibleResults(BIBLE_VERSES);

  // Topic pills — always use local dataset
  document.querySelectorAll('#bibleTopicRow .topic-pill').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('#bibleTopicRow .topic-pill').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      bibleActiveTopic = pill.dataset.topic;
      document.getElementById('bibleQuery').value = '';
      runBibleSearch('', bibleActiveTopic);
    });
  });

  // Search on Enter key
  document.getElementById('bibleQuery').addEventListener('keydown', e => {
    if (e.key === 'Enter') triggerBibleSearch();
  });

  // Search button
  document.getElementById('bibleSearchBtn').addEventListener('click', triggerBibleSearch);
}

function triggerBibleSearch() {
  const q = document.getElementById('bibleQuery').value.trim();
  if (!q) return;
  // Deactivate all topic pills when doing a text search
  document.querySelectorAll('#bibleTopicRow .topic-pill').forEach(p => p.classList.remove('active'));
  document.querySelector('#bibleTopicRow .topic-pill[data-topic=""]').classList.add('active');
  bibleActiveTopic = '';
  runBibleSearch(q, '');
}

async function runBibleSearch(query, topic) {
  // Topic filter — use local dataset
  if (topic) {
    const results = BIBLE_VERSES.filter(v => v.topics.includes(topic));
    renderBibleResults(results, topic);
    return;
  }

  // No query — show full local set
  if (!query) {
    renderBibleResults(BIBLE_VERSES);
    return;
  }

  // Looks like a Bible reference → hit the live API
  if (looksLikeReference(query)) {
    await fetchFromApi(query);
    return;
  }

  // Keyword search — search text & refs in local dataset
  const q       = query.toLowerCase();
  const results = BIBLE_VERSES.filter(v =>
    v.ref.toLowerCase().includes(q) ||
    v.text.toLowerCase().includes(q)
  );

  if (results.length) {
    renderBibleResults(results, query);
  } else {
    // Nothing in local set — try the API as fallback
    await fetchFromApi(query);
  }
}

async function fetchFromApi(reference) {
  const container = document.getElementById('bibleResults');
  const meta      = document.getElementById('bibleResultsMeta');
  const countEl   = document.getElementById('bibleResultCount');

  // Show loading state
  meta.style.display  = 'block';
  countEl.textContent = 'Looking up…';
  container.innerHTML = `<div class="bible-loading">
    <div class="bible-spinner"></div>
    <p>Fetching from scripture…</p>
  </div>`;

  try {
    // bible-api.com — free, no key, returns WEB translation
    const encoded = encodeURIComponent(reference);
    const res     = await fetch(`https://bible-api.com/${encoded}?translation=web`);

    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const data = await res.json();

    if (data.error) {
      countEl.textContent = '0 verses';
      container.innerHTML = `<p class="bible-empty">Reference not found: "${escHtml(reference)}". Try a format like "John 3:16" or "Psalm 23".</p>`;
      return;
    }

    // Normalise the response — API returns verses array or single text
    const verses = (data.verses || []).map(v => ({
      ref:    `${v.book_name} ${v.chapter}:${v.verse}`,
      text:   v.text.trim(),
      topics: [],
    }));

    // If no verses array, fall back to the single text block
    if (!verses.length && data.text) {
      verses.push({ ref: data.reference, text: data.text.trim(), topics: [] });
    }

    countEl.textContent = `${verses.length} verse${verses.length !== 1 ? 's' : ''}`;
    renderBibleResults(verses, reference);

  } catch (err) {
    countEl.textContent = 'Error';
    container.innerHTML = `<p class="bible-empty">Could not reach the scripture API. Check your connection and try again.</p>`;
  }
}

function renderBibleResults(verses, highlight = '') {
  const container = document.getElementById('bibleResults');
  const meta      = document.getElementById('bibleResultsMeta');
  const countEl   = document.getElementById('bibleResultCount');

  meta.style.display  = 'block';
  countEl.textContent = `${verses.length} verse${verses.length !== 1 ? 's' : ''}`;

  if (!verses.length) {
    container.innerHTML = '<p class="bible-empty">No verses found. Try a different word or reference.</p>';
    return;
  }

  const hl = highlight.toLowerCase();

  container.innerHTML = verses.map((v) => {
    let text = escHtml(v.text);
    let ref  = escHtml(v.ref);

    // Highlight matching words
    if (hl) {
      const safeHl = hl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const re     = new RegExp(`(${safeHl})`, 'gi');
      text = text.replace(re, '<mark>$1</mark>');
      ref  = ref.replace(re,  '<mark>$1</mark>');
    }

    const topicTags = v.topics.map(t => `<span class="verse-topic">${escHtml(t)}</span>`).join('');
    const isSaved   = JSON.parse(localStorage.getItem('savedVerses') || '[]').includes(v.ref);

    return `
      <div class="verse-card">
        <div class="verse-ref">${ref}</div>
        <p class="verse-text">${text}</p>
        <div class="verse-footer">
          <div class="verse-topics">${topicTags}</div>
          <button class="verse-save ${isSaved ? 'saved' : ''}" data-ref="${escHtml(v.ref)}">${isSaved ? '♥ Saved' : '♡ Save'}</button>
        </div>
      </div>
    `;
  }).join('');

  // Bind save buttons after rendering
  container.querySelectorAll('.verse-save').forEach(btn => {
    btn.addEventListener('click', () => toggleSaveVerse(btn, btn.dataset.ref));
  });
}

function toggleSaveVerse(btn, ref) {
  const saved = JSON.parse(localStorage.getItem('savedVerses') || '[]');
  const idx   = saved.indexOf(ref);
  if (idx === -1) {
    saved.push(ref);
    btn.textContent = '♥ Saved';
    btn.classList.add('saved');
  } else {
    saved.splice(idx, 1);
    btn.textContent = '♡ Save';
    btn.classList.remove('saved');
  }
  localStorage.setItem('savedVerses', JSON.stringify(saved));
}
// ─── Quizzes ──────────────────────────────────────────────────────────────────

function loadQuizzes() {
  renderQuizGrid();

  // Filter pills
  document.querySelectorAll('#quizFilterRow .quiz-filter-pill').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('#quizFilterRow .quiz-filter-pill').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');

      // Animate existing cards out, then re-render with entrance animation
      const grid = document.getElementById('quizGrid');
      const existingCards = grid.querySelectorAll('.quiz-card');
      if (existingCards.length) {
        existingCards.forEach(card => card.classList.add('quiz-card--exit'));
        setTimeout(() => renderQuizGrid(pill.dataset.category, true), 180);
      } else {
        renderQuizGrid(pill.dataset.category, true);
      }
    });
  });

  document.getElementById('quizBackBtn').addEventListener('click', () => showQuizGrid());
  document.getElementById('quizRetryBtn').addEventListener('click', () => startQuiz(_quizActive));
  document.getElementById('quizAllBtn').addEventListener('click',   () => showQuizGrid());
}

function renderQuizGrid(filterCategory = '', animate = false) {
  const grid = document.getElementById('quizGrid');

  // Merge hardcoded quizzes with any published quizzes saved by admin
  const stored    = (() => { try { return JSON.parse(localStorage.getItem('adminQuizzes') || '[]'); } catch { return []; } })();
  const published = stored.filter(q => !q.isDraft);
  const all       = [...QUIZ_DATA, ...published];

  const filtered = filterCategory ? all.filter(q => q.category === filterCategory) : all;

  if (!filtered.length) {
    grid.innerHTML = '<p style="color:var(--ink-soft); font-size:14px; padding:12px 0;">No quizzes in this category yet.</p>';
    return;
  }

  grid.innerHTML = filtered.map(quiz => `
    <div class="quiz-card${quiz.id === 'daily-challenge' ? ' quiz-card--daily' : ''}" data-quiz-id="${escHtml(quiz.id)}">
      <div class="quiz-card-top">
        <span class="quiz-card-count">${quiz.questions.length} questions</span>
      </div>
      <h3 class="quiz-card-title">${escHtml(quiz.title)}</h3>
      <p class="quiz-card-desc">${escHtml(quiz.description)}</p>
      <button class="quiz-start-btn" data-quiz-id="${escHtml(quiz.id)}">
        Start quiz
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
      </button>
    </div>
  `).join('');

  // Staggered entrance animation when triggered by a filter click
  if (animate) {
    grid.querySelectorAll('.quiz-card').forEach((card, i) => {
      card.classList.add('quiz-card--enter');
      card.style.animationDelay = `${i * 45}ms`;
    });
  }

  grid.querySelectorAll('.quiz-start-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const quiz = all.find(q => q.id === btn.dataset.quizId);
      if (quiz) startQuiz(quiz);
    });
  });
}

function startQuiz(quiz) {
  _quizActive   = quiz;
  _quizIdx      = 0;
  _quizScore    = 0;
  _quizAnswered = false;

  document.getElementById('quizActiveTitle').textContent = quiz.title;
  document.getElementById('quizResultScreen').hidden     = true;
  document.getElementById('quizQuestionCard').hidden     = false;

  showQuizActive();
  renderQuestion();
}

function renderQuestion() {
  const q       = _quizActive.questions[_quizIdx];
  const total   = _quizActive.questions.length;
  const pct     = (_quizIdx / total) * 100;
  _quizAnswered = false;

  document.getElementById('quizProgressLabel').textContent = `Question ${_quizIdx + 1} of ${total}`;
  document.getElementById('quizScoreLabel').textContent    = `Score: ${_quizScore}`;
  document.getElementById('quizProgressFill').style.width  = pct + '%';
  document.getElementById('quizQuestionText').textContent  = q.q;

  const choicesEl = document.getElementById('quizChoices');
  choicesEl.innerHTML = q.choices.map((c, i) => `
    <button class="quiz-choice" data-idx="${i}">${escHtml(c)}</button>
  `).join('');

  choicesEl.querySelectorAll('.quiz-choice').forEach(btn => {
    btn.addEventListener('click', () => handleAnswer(parseInt(btn.dataset.idx, 10)));
  });
}

function handleAnswer(chosenIdx) {
  if (_quizAnswered) return;
  _quizAnswered = true;

  const q         = _quizActive.questions[_quizIdx];
  const correct   = chosenIdx === q.answer;
  if (correct) _quizScore++;

  // Visual feedback
  const choicesEl = document.getElementById('quizChoices');
  choicesEl.querySelectorAll('.quiz-choice').forEach(btn => {
    const idx = parseInt(btn.dataset.idx, 10);
    btn.disabled = true;
    if (idx === q.answer)  btn.classList.add('quiz-choice--correct');
    if (idx === chosenIdx && !correct) btn.classList.add('quiz-choice--wrong');
  });

  // Advance after short delay
  setTimeout(() => {
    _quizIdx++;
    if (_quizIdx < _quizActive.questions.length) {
      renderQuestion();
    } else {
      showQuizResult();
    }
  }, 900);
}

function showQuizResult() {
  const total  = _quizActive.questions.length;
  const ratio  = _quizScore / total;

  // Pick icon & heading
  let iconName, heading;
  if (ratio === 1)       { iconName = 'trophy';    heading = 'Perfect score!'; }
  else if (ratio >= 0.7) { iconName = 'star';      heading = 'Well done!'; }
  else if (ratio >= 0.4) { iconName = 'book-open'; heading = 'Keep studying!'; }
  else                   { iconName = 'heart-handshake'; heading = 'Keep going!'; }

  // Pick verse
  const verse = RESULT_VERSES.find(v => ratio >= v.score) || RESULT_VERSES[RESULT_VERSES.length - 1];

  document.getElementById('quizResultIcon').innerHTML  = `<i data-lucide="${iconName}"></i>`;
  document.getElementById('quizResultHeading').textContent = heading;
  document.getElementById('quizResultScore').textContent   = `You scored ${_quizScore} out of ${total}`;
  document.getElementById('quizResultVerse').textContent   = verse.text;

  // Re-init Lucide so the new icon renders
  if (typeof lucide !== 'undefined') lucide.createIcons();

  // Update progress bar to 100%
  document.getElementById('quizProgressFill').style.width  = '100%';
  document.getElementById('quizProgressLabel').textContent = `Finished!`;
  document.getElementById('quizScoreLabel').textContent    = `Score: ${_quizScore}`;

  document.getElementById('quizQuestionCard').hidden  = true;
  document.getElementById('quizResultScreen').hidden  = false;
}

function showQuizGrid() {
  document.getElementById('quizGridView').hidden   = false;
  document.getElementById('quizActiveView').hidden = true;
}

function showQuizActive() {
  document.getElementById('quizGridView').hidden   = true;
  document.getElementById('quizActiveView').hidden = false;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── Prayer Wall ──────────────────────────────────────────────────────────────

async function loadPrayerWall() {
  const wall = document.getElementById('prayerWall');
  const res  = await apiFetch('/prayers');

  if (res.status !== 'success') {
    wall.innerHTML = '<p style="color:var(--ember);">Could not load prayer requests. Please try again.</p>';
    return;
  }

  renderPrayerWall(res.data || []);
  bindPrayerForm();
  bindPrayerFilters();
  initCharCounter();
}

// ── Render ────────────────────────────────────────────────────────────────────

let _allPrayers = [];

function renderPrayerWall(items, filterCat = '') {
  _allPrayers = items;
  const wall = document.getElementById('prayerWall');

  const visible = filterCat
    ? items.filter(p => p.category === filterCat)
    : items;

  if (!visible.length) {
    wall.innerHTML = `
      <div class="prayer-empty">
        <i data-lucide="hand-heart" class="prayer-empty-icon"></i>
        <p>${filterCat ? `No ${filterCat} requests yet.` : 'No prayer requests yet. Be the first.'}</p>
      </div>`;
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [wall] });
    return;
  }

  // Track which IDs this user has already prayed for
  const prayedSet = getPrayedSet();

  wall.innerHTML = visible.map(p => {
    const already  = prayedSet.has(p.id);
    const timeAgo  = formatTimeAgo(p.created_at);
    return `
      <div class="prayer-item" data-id="${p.id}">
        <div class="head">
          <span class="who">${escHtml(p.name)}</span>
          <span class="cat">${escHtml(p.category)}</span>
        </div>
        <p>${escHtml(p.body)}</p>
        <div class="prayer-item-footer">
          <button class="pray-btn ${already ? 'done' : ''}" data-id="${p.id}" aria-pressed="${already}">
            <i data-lucide="hand-heart"></i> Praying (${p.pray_count})
          </button>
          <span class="prayer-time">${timeAgo}</span>
        </div>
      </div>
    `;
  }).join('');

  // Bind pray buttons
  wall.querySelectorAll('.pray-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (btn.classList.contains('done')) return;
      const id  = parseInt(btn.dataset.id, 10);
      const res = await apiFetch(`/prayers/${id}/pray`, { method: 'POST' });
      if (res.status === 'success') {
        btn.innerHTML = `<i data-lucide="hand-heart"></i> Praying (${res.data.pray_count})`;
        btn.classList.add('done');
        btn.setAttribute('aria-pressed', 'true');
        addToPrayedSet(id);
        if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [btn] });
      }
    });
  });

  // Init Lucide icons for newly rendered pray buttons
  if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: Array.from(wall.querySelectorAll('.pray-btn')) });
}

// ── Category filter pills ─────────────────────────────────────────────────────

function bindPrayerFilters() {
  document.querySelectorAll('#prayerFilterRow .filter-pill').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('#prayerFilterRow .filter-pill')
        .forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      renderPrayerWall(_allPrayers, pill.dataset.cat || '');
    });
  });
}

// ── Submit form ───────────────────────────────────────────────────────────────

function bindPrayerForm() {
  const btn = document.getElementById('prayerSubmitBtn');
  const msg = document.getElementById('prayerFormMsg');

  btn.addEventListener('click', async () => {
    const category = document.getElementById('pcat').value;
    const body     = document.getElementById('preq').value.trim();

    // Client-side validation
    if (body.length < 10) {
      showPrayerMsg(msg, 'Your request needs to be at least 10 characters.', 'error');
      return;
    }
    if (body.length > 1000) {
      showPrayerMsg(msg, 'Request must be 1000 characters or fewer.', 'error');
      return;
    }

    msg.style.display = 'none';
    btn.disabled      = true;
    btn.textContent   = 'Posting…';

    try {
      const res = await apiFetch('/prayers', {
        method: 'POST',
        body:   JSON.stringify({ category, body }),
      });

      if (res.status === 'success') {
        document.getElementById('preq').value  = '';
        document.getElementById('pcat').selectedIndex = 0;
        updateCharCounter(0);

        // Show modal confirmation (same pattern as Read & Watch)
        showPendingApprovalModal(
          'Your prayer request has been posted to the wall.<br>Others in the community can now pray alongside you.',
          { title: 'Request Posted!', icon: 'heart-handshake' }
        );

        // Reload the wall so the new request shows up immediately
        setTimeout(async () => {
          const wallRes = await apiFetch('/prayers');
          if (wallRes.status === 'success') {
            const activePill = document.querySelector('#prayerFilterRow .filter-pill.active');
            const activeCat  = activePill ? (activePill.dataset.cat || '') : '';
            renderPrayerWall(wallRes.data || [], activeCat);
          }
        }, 400);
      } else {
        showPrayerMsg(msg, res.message || 'Something went wrong.', 'error');
      }
    } catch (err) {
      showPrayerMsg(msg, 'Network error — please try again.', 'error');
    } finally {
      btn.disabled    = false;
      btn.textContent = 'Post to the wall';
    }
  });
}

function showPrayerMsg(el, text, type) {
  el.textContent  = text;
  el.className    = `form-note prayer-msg prayer-msg--${type}`;
  el.style.display = 'block';
}

// ── Character counter ─────────────────────────────────────────────────────────

function initCharCounter() {
  const textarea = document.getElementById('preq');
  const counter  = document.getElementById('preqCounter');
  if (!textarea || !counter) return;
  textarea.addEventListener('input', () => updateCharCounter(textarea.value.length));
}

function updateCharCounter(len) {
  const counter = document.getElementById('preqCounter');
  if (!counter) return;
  counter.textContent = `${len}/1000`;
  counter.className   = len > 900 ? 'char-counter char-counter--warn'
                      : len > 1000 ? 'char-counter char-counter--over'
                      : 'char-counter';
}

// ── Utilities ─────────────────────────────────────────────────────────────────

function getPrayedSet() {
  return new Set(JSON.parse(localStorage.getItem('prayedIds') || '[]'));
}

function addToPrayedSet(id) {
  const set = getPrayedSet();
  set.add(id);
  localStorage.setItem('prayedIds', JSON.stringify([...set]));
}

function formatTimeAgo(dateStr) {
  if (!dateStr) return '';
  const diff = Date.now() - new Date(dateStr).getTime();
  const m    = Math.floor(diff / 60000);
  if (m < 1)   return 'just now';
  if (m < 60)  return `${m}m ago`;
  const h = Math.floor(m / 60);
  if (h < 24)  return `${h}h ago`;
  const d = Math.floor(h / 24);
  if (d < 7)   return `${d}d ago`;
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

// ─── Events ───────────────────────────────────────────────────────────────────

async function loadEvents() {
  const [weeklyRes, upcomingRes] = await Promise.all([
    apiFetch('/events/weekly'),
    apiFetch('/events/upcoming'),
  ]);

  renderWeeklySchedule(weeklyRes.data || []);
  renderUpcomingEvents(upcomingRes.data || []);
  initJoinModal();
  initAddEventModal();
}

// ── Weekly schedule ───────────────────────────────────────────────────────────

function renderWeeklySchedule(items) {
  const el = document.getElementById('weeklySchedule');
  if (!items.length) {
    el.innerHTML = '<p style="color:var(--text-on-light-dim);padding:16px 0;">No recurring events scheduled.</p>';
    return;
  }

  el.innerHTML = items.map(e => `
    <div class="schedule-row">
      <span class="day">${escHtml(e.day_label)}</span>
      <div class="row-main">
        <h3>${escHtml(e.title)}</h3>
        <span class="meta">
          ${escHtml(e.location)}${e.has_livestream ? ' · <span class="live-tag">Livestream available</span>' : ''}
        </span>
      </div>
      <button class="btn btn-dark join-btn"
        data-id="${e.id}"
        data-title="${escHtml(e.title)}"
        data-desc="${escHtml(e.description)}"
        data-label="${escHtml(e.day_label)}"
        data-location="${escHtml(e.location)}"
        data-livestream="${e.has_livestream ? '1' : '0'}"
        data-recurring="1">Join</button>
    </div>
  `).join('');

  bindJoinButtons(el);
}

// ── Upcoming events ───────────────────────────────────────────────────────────

function renderUpcomingEvents(items) {
  const el = document.getElementById('upcomingEvents');
  if (!items.length) {
    el.innerHTML = '<p style="color:var(--text-on-light-dim);padding:16px 0;">No upcoming events. Check back soon.</p>';
    return;
  }

  el.innerHTML = items.map(e => {
    const d     = new Date(e.event_date + 'T00:00:00');
    const day   = d.getDate();
    const month = d.toLocaleString('default', { month: 'long' });
    const dow   = d.toLocaleString('default', { weekday: 'long' });
    const time  = formatTime(e.start_time);

    return `
      <div class="event-card">
        <div class="event-date-block">
          <span class="d">${day}</span>
          <div>
            <div class="m">${month}</div>
            <div class="m">${dow}</div>
          </div>
        </div>
        <div class="body">
          <h3>${escHtml(e.title)}</h3>
          <p>${escHtml(e.description)}</p>
          <div class="event-card-footer">
            <div class="event-meta-row">
              <i data-lucide="clock" class="event-meta-icon"></i> ${time}${e.location ? ` &nbsp;·&nbsp; <i data-lucide="map-pin" class="event-meta-icon"></i> ${escHtml(e.location)}` : ''}
              ${e.has_livestream ? ' &nbsp;·&nbsp; <span class="live-tag">Livestream</span>' : ''}
            </div>
            <button class="btn btn-dark join-btn"
              data-id="${e.id}"
              data-title="${escHtml(e.title)}"
              data-desc="${escHtml(e.description)}"
              data-label="${escHtml(dow)}, ${escHtml(month)} ${day} · ${time}"
              data-location="${escHtml(e.location)}"
              data-livestream="${e.has_livestream ? '1' : '0'}"
              data-recurring="0">Join</button>
          </div>
        </div>
      </div>
    `;
  }).join('');

  if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [el] });
  bindJoinButtons(el);
}

function formatTime(timeStr) {
  if (!timeStr) return '';
  const [h, m] = timeStr.split(':').map(Number);
  const ampm   = h >= 12 ? 'PM' : 'AM';
  const hour   = h % 12 || 12;
  return `${hour}:${String(m).padStart(2,'0')} ${ampm}`;
}

// ── Join Modal ────────────────────────────────────────────────────────────────

// Track which event is currently open in the join modal
let _joinEventId   = null;
let _joinRegistered = false; // current registration state
let _joinJoinType  = null;

function initJoinModal() {
  document.getElementById('joinEventModalClose').addEventListener('click', closeJoinModal);
  document.getElementById('joinEventModalBackdrop').addEventListener('click', closeJoinModal);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !document.getElementById('joinEventModal').hidden) closeJoinModal();
  });
}

function bindJoinButtons(container) {
  container.querySelectorAll('.join-btn').forEach(btn => {
    btn.addEventListener('click', () => openJoinModal(btn));
  });
}

async function openJoinModal(btn) {
  const eventId      = parseInt(btn.dataset.id, 10);
  const hasLivestream = btn.dataset.livestream === '1';
  const location     = btn.dataset.location;

  _joinEventId    = eventId;
  _joinRegistered = false;
  _joinJoinType   = null;

  document.getElementById('joinEventMeta').textContent  = btn.dataset.label;
  document.getElementById('joinEventTitle').textContent = btn.dataset.title;

  // Show loading state in modal body while we check registration status
  document.getElementById('joinEventBody').innerHTML = `
    <p style="margin-bottom:20px;">${escHtml(btn.dataset.desc) || '<em style="opacity:.6;">No description provided.</em>'}</p>
    <div class="join-options" id="joinOptions">
      <p style="color:var(--ink-soft);padding:12px 0;">Loading…</p>
    </div>
  `;

  document.getElementById('joinEventModal').hidden = false;
  lockScroll();
  document.getElementById('joinEventModalClose').focus();

  // If logged in, fetch the current registration status
  let registered = false;
  let currentJoinType = null;

  if (window.CURRENT_MEMBER) {
    try {
      const res = await apiFetch(`/events/${eventId}/register`);
      if (res.status === 'success') {
        registered     = res.data.registered;
        currentJoinType = res.data.join_type;
      }
    } catch (_) { /* ignore, show unregistered UI */ }
  }

  _joinRegistered = registered;
  _joinJoinType   = currentJoinType;

  renderJoinOptions(eventId, hasLivestream, location, btn.dataset.desc, registered, currentJoinType);
}

function renderJoinOptions(eventId, hasLivestream, location, desc, registered, currentJoinType) {
  const optionsEl = document.getElementById('joinOptions');
  if (!optionsEl) return;

  const isLoggedIn = !!window.CURRENT_MEMBER;

  // Already registered — show status + cancel option
  if (registered) {
    const typeLabel = currentJoinType === 'online' ? '📺 Online (Livestream)' : '📍 In Person';
    optionsEl.innerHTML = `
      <div class="join-registered-banner">
        <span class="join-registered-icon">✅</span>
        <div>
          <strong>You're registered!</strong>
          <p>Joining: ${typeLabel}</p>
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap;">
        ${hasLivestream ? `<button class="btn btn-primary" id="joinSwitchOnlineBtn">Switch to Online</button>` : ''}
        ${location      ? `<button class="btn btn-primary" id="joinSwitchInPersonBtn">Switch to In Person</button>` : ''}
        <button class="btn btn-ghost-dark" id="joinCancelRegBtn">Cancel registration</button>
      </div>
      <div class="join-msg" id="joinMsg" hidden></div>
    `;

    // Bind switch buttons
    document.getElementById('joinSwitchOnlineBtn')?.addEventListener('click', () => submitJoin(eventId, 'online'));
    document.getElementById('joinSwitchInPersonBtn')?.addEventListener('click', () => submitJoin(eventId, 'in_person'));
    document.getElementById('joinCancelRegBtn')?.addEventListener('click', () => cancelJoin(eventId));
    return;
  }

  // Not logged in
  if (!isLoggedIn) {
    optionsEl.innerHTML = `
      ${location ? `
        <div class="join-option">
          <div class="join-option-icon">📍</div>
          <div><strong>In person</strong><p>${escHtml(location)}</p></div>
        </div>` : ''}
      ${hasLivestream ? `
        <div class="join-option">
          <div class="join-option-icon">📺</div>
          <div><strong>Livestream</strong><p>Watch from anywhere — join the stream when it goes live.</p></div>
        </div>` : ''}
      <p class="join-login-prompt">
        <a href="#" id="joinLoginLink" style="color:var(--dusk);font-weight:600;">Sign in</a> to register for this event.
      </p>
      <div class="join-msg" id="joinMsg" hidden></div>
    `;
    document.getElementById('joinLoginLink')?.addEventListener('click', e => {
      e.preventDefault();
      closeJoinModal();
      navigateTo('login');
    });
    return;
  }

  // Logged in, not registered — show join options
  const hasOptions = location || hasLivestream;
  optionsEl.innerHTML = `
    ${!hasOptions ? `<p style="opacity:.6;">No joining details available yet.</p>` : ''}
    ${location ? `
      <div class="join-option">
        <div class="join-option-icon">📍</div>
        <div>
          <strong>In person</strong>
          <p>${escHtml(location)}</p>
          <button class="btn btn-primary join-action-btn" style="margin-top:10px;"
            data-type="in_person">Register — In Person</button>
        </div>
      </div>` : ''}
    ${hasLivestream ? `
      <div class="join-option">
        <div class="join-option-icon">📺</div>
        <div>
          <strong>Livestream</strong>
          <p>Watch from anywhere — join the stream when it goes live.</p>
          <button class="btn btn-primary join-action-btn" style="margin-top:10px;"
            data-type="online">Register — Online</button>
        </div>
      </div>` : ''}
    <div class="join-msg" id="joinMsg" hidden></div>
  `;

  optionsEl.querySelectorAll('.join-action-btn').forEach(btn => {
    btn.addEventListener('click', () => submitJoin(eventId, btn.dataset.type));
  });
}

async function submitJoin(eventId, joinType) {
  const msgEl = document.getElementById('joinMsg');
  // Disable all action buttons while submitting
  document.querySelectorAll('#joinOptions .btn').forEach(b => b.disabled = true);

  try {
    const res = await apiFetch(`/events/${eventId}/register`, {
      method: 'POST',
      body: JSON.stringify({ join_type: joinType }),
    });

    if (res.status === 'success') {
      _joinRegistered = true;
      _joinJoinType   = joinType;

      // Re-fetch and re-render to show updated state
      const statusRes = await apiFetch(`/events/${eventId}/register`);
      const reg        = statusRes?.data?.registered ?? true;
      const regType    = statusRes?.data?.join_type  ?? joinType;

      // Get the triggering button's data and re-render
      const btn = document.querySelector(`.join-btn[data-id="${eventId}"]`);
      if (btn) {
        renderJoinOptions(
          eventId,
          btn.dataset.livestream === '1',
          btn.dataset.location,
          btn.dataset.desc,
          reg,
          regType
        );
      } else {
        // Fallback: show success message inline
        if (msgEl) {
          msgEl.textContent = '✅ Registered successfully!';
          msgEl.style.color = '#2e7d32';
          msgEl.hidden = false;
        }
      }
      // Update the join button text in the list
      updateJoinButtonState(eventId, true);
    } else {
      if (msgEl) {
        msgEl.textContent = res.message || 'Could not save registration.';
        msgEl.style.color = '#c62828';
        msgEl.hidden = false;
      }
      document.querySelectorAll('#joinOptions .btn').forEach(b => b.disabled = false);
    }
  } catch (_) {
    if (msgEl) {
      msgEl.textContent = 'Network error. Please try again.';
      msgEl.style.color = '#c62828';
      msgEl.style.color = 'var(--danger)';
      msgEl.hidden = false;
    }
    document.querySelectorAll('#joinOptions .btn').forEach(b => b.disabled = false);
  }
}

async function cancelJoin(eventId) {
  if (!confirm('Cancel your registration for this event?')) return;

  const msgEl = document.getElementById('joinMsg');
  document.querySelectorAll('#joinOptions .btn').forEach(b => b.disabled = true);

  try {
    const res = await apiFetch(`/events/${eventId}/register`, { method: 'DELETE' });
    if (res.status === 'success') {
      _joinRegistered = false;
      _joinJoinType   = null;

      const btn = document.querySelector(`.join-btn[data-id="${eventId}"]`);
      renderJoinOptions(
        eventId,
        btn?.dataset?.livestream === '1',
        btn?.dataset?.location ?? '',
        btn?.dataset?.desc ?? '',
        false,
        null
      );
      updateJoinButtonState(eventId, false);
    } else {
      if (msgEl) {
        msgEl.textContent = res.message || 'Could not cancel registration.';
        msgEl.style.color = '#c62828';
        msgEl.hidden = false;
      }
      document.querySelectorAll('#joinOptions .btn').forEach(b => b.disabled = false);
    }
  } catch (_) {
    if (msgEl) {
      msgEl.textContent = 'Network error. Please try again.';
      msgEl.style.color = '#c62828';
      msgEl.hidden = false;
    }
    document.querySelectorAll('#joinOptions .btn').forEach(b => b.disabled = false);
  }
}

/** Update the Join button in the event list to reflect registered state */
function updateJoinButtonState(eventId, registered) {
  document.querySelectorAll(`.join-btn[data-id="${eventId}"]`).forEach(btn => {
    btn.textContent = registered ? 'Registered ✓' : 'Join';
    btn.style.background = registered ? '#2e7d32' : '';
  });
}

function closeJoinModal() {
  document.getElementById('joinEventModal').hidden = true;
  unlockScroll();
  _joinEventId = null;
}

// ── Add Event Modal ───────────────────────────────────────────────────────────

function initAddEventModal() {
  const openBtn = document.getElementById('openAddEventBtn');
  if (!openBtn) return;
  openBtn.addEventListener('click', openAddEventModal);
  document.getElementById('addEventModalClose').addEventListener('click', closeAddEventModal);
  document.getElementById('addEventCancelBtn').addEventListener('click', closeAddEventModal);
  document.getElementById('addEventModalBackdrop').addEventListener('click', closeAddEventModal);
  document.getElementById('addEventForm').addEventListener('submit', submitAddEvent);

  // Set default date to today
  const today = new Date().toISOString().slice(0, 10);
  document.getElementById('evtDate').value = today;
  document.getElementById('evtDate').min   = today;

  // Toggle recurring / date fields
  document.getElementById('evtRecurring').addEventListener('change', function () {
    document.getElementById('evtRecurDayGroup').style.display = this.checked ? '' : 'none';
    document.getElementById('evtDateGroup').style.display     = this.checked ? 'none' : '';
  });
}

function openAddEventModal() {
  document.getElementById('addEventModal').hidden = false;
  lockScroll();
  document.getElementById('evtTitle').focus();
}

function closeAddEventModal() {
  document.getElementById('addEventModal').hidden = true;
  unlockScroll();
  const msg = document.getElementById('addEventMsg');
  msg.hidden = true;
}

async function submitAddEvent(e) {
  e.preventDefault();
  const btn = document.getElementById('addEventSubmitBtn');
  const msg = document.getElementById('addEventMsg');

  const title        = document.getElementById('evtTitle').value.trim();
  const description  = document.getElementById('evtDesc').value.trim();
  const location     = document.getElementById('evtLocation').value.trim();
  const start_time   = document.getElementById('evtTime').value;
  const hasLivestream= document.getElementById('evtLivestream').checked;
  const isRecurring  = document.getElementById('evtRecurring').checked;
  const recurDay     = document.getElementById('evtRecurDay').value;
  const eventDate    = document.getElementById('evtDate').value;

  if (!title)      { showFormMsg(msg, 'Title is required.', 'error'); return; }
  if (!start_time) { showFormMsg(msg, 'Start time is required.', 'error'); return; }
  if (!isRecurring && !eventDate) { showFormMsg(msg, 'Event date is required.', 'error'); return; }

  btn.disabled    = true;
  btn.textContent = 'Saving…';
  msg.hidden      = true;

  try {
    const res = await apiFetch('/events', {
      method: 'POST',
      body:   JSON.stringify({
        title, description, location, start_time,
        has_livestream: hasLivestream,
        is_recurring:   isRecurring,
        recur_day:      isRecurring ? recurDay : '',
        event_date:     isRecurring ? '' : eventDate,
      }),
    });

    if (res.status === 'success') {
      showFormMsg(msg, 'Event saved!', 'success');
      document.getElementById('addEventForm').reset();
      document.getElementById('evtDate').value = new Date().toISOString().slice(0, 10);

      // Reload both lists
      setTimeout(async () => {
        closeAddEventModal();
        const [wRes, uRes] = await Promise.all([
          apiFetch('/events/weekly'),
          apiFetch('/events/upcoming'),
        ]);
        renderWeeklySchedule(wRes.data || []);
        renderUpcomingEvents(uRes.data || []);
      }, 700);
    } else {
      showFormMsg(msg, res.message || 'Could not save event.', 'error');
    }
  } catch (err) {
    showFormMsg(msg, 'Network error. Please try again.', 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Save Event';
  }
}

// ─── Connect ──────────────────────────────────────────────────────────────────

(function initConnect() {
  const form      = document.getElementById('contactForm');
  const submitBtn = document.getElementById('contactSubmitBtn');
  const msgBox    = document.getElementById('contactMsg');
  const cmsg      = document.getElementById('cmsg');
  const countEl   = document.getElementById('cmsgCount');

  // Only run if the contact form exists on this page
  if (!form || !submitBtn || !msgBox || !cmsg || !countEl) return;

  // Live character counter
  cmsg.addEventListener('input', () => {
    const len = cmsg.value.length;
    countEl.textContent = `${len} / 3000`;
    countEl.style.color = len > 2800 ? '#fca5a5' : 'rgba(251,246,236,.4)';
  });

  // Clear inline error on input (only for fields that exist)
  ['cname', 'cemail', 'cmsg'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('input', () => clearFieldError(id));
    }
  });

  form.addEventListener('submit', handleContactSubmit);

  // "Start a live chat" — scroll to message field
  const liveChatBtn = document.getElementById('liveChatBtn');
  if (liveChatBtn) {
    liveChatBtn.addEventListener('click', () => {
      cmsg.focus();
      cmsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  }
})();

// ── Donate Modal ──────────────────────────────────────────────────────────────

(function initDonateModal() {
  const openBtn  = document.getElementById('openDonateModalBtn');
  const modal    = document.getElementById('donateModal');
  const backdrop = document.getElementById('donateModalBackdrop');
  const closeBtn = document.getElementById('donateModalClose');

  if (!openBtn || !modal) return;

  function openDonate() {
    modal.hidden = false;
    lockScroll();
    closeBtn.focus();
  }

  function closeDonate() {
    modal.hidden = true;
    unlockScroll();
  }

  openBtn.addEventListener('click', openDonate);
  closeBtn.addEventListener('click', closeDonate);
  backdrop.addEventListener('click', closeDonate);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !modal.hidden) closeDonate();
  });
})();

function clearFieldError(id) {
  const el = document.getElementById(id);
  const errEl = document.getElementById(id + 'Error');
  if (el) el.classList.remove('invalid');
  if (errEl) errEl.textContent = '';
}

function setFieldError(id, msg) {
  const el = document.getElementById(id);
  const errEl = document.getElementById(id + 'Error');
  if (el) el.classList.add('invalid');
  if (errEl) errEl.textContent = msg;
}

async function handleContactSubmit(e) {
  e.preventDefault();

  const submitBtn = document.getElementById('contactSubmitBtn');
  const msgBox    = document.getElementById('contactMsg');

  // If member is logged in, use their data; otherwise read from form
  const isMember = window.CURRENT_MEMBER ? true : false;
  let name, email;

  if (isMember) {
    name  = window.CURRENT_MEMBER.display_name || window.CURRENT_MEMBER.username || 'Member';
    email = window.CURRENT_MEMBER.email;
  } else {
    name  = document.getElementById('cname').value.trim();
    email = document.getElementById('cemail').value.trim();
  }

  const reason  = document.getElementById('creason').value;
  const message = document.getElementById('cmsg').value.trim();

  // Clear previous errors
  if (!isMember) {
    ['cname', 'cemail'].forEach(clearFieldError);
  }
  clearFieldError('cmsg');
  msgBox.hidden = true;

  // Client-side validation
  let hasError = false;

  if (!isMember) {
    if (!name) {
      setFieldError('cname', 'Name is required.');
      hasError = true;
    }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      setFieldError('cemail', 'A valid email address is required.');
      hasError = true;
    }
  }

  if (message.length < 5) {
    setFieldError('cmsg', 'Message is too short (min 5 characters).');
    hasError = true;
  }
  if (hasError) return;

  // Loading state
  submitBtn.disabled    = true;
  submitBtn.textContent = 'Sending…';

  try {
    const res = await apiFetch('/contact', {
      method: 'POST',
      body: JSON.stringify({ name, email, reason, message }),
    });

    msgBox.hidden = false;
    if (res.status === 'success') {
      msgBox.className = 'form-msg form-msg--success';
      msgBox.textContent = res.data?.message || res.message || `Thanks, ${name}! We'll be in touch.`;
      // Reset form (textarea only, leave name/email for guests)
      document.getElementById('cmsg').value = '';
      document.getElementById('cmsgCount').textContent = '0 / 3000';
      document.getElementById('creason').value = 'Just saying hi';
    } else {
      msgBox.className = 'form-msg form-msg--error';
      msgBox.textContent = res.message || 'Something went wrong. Please try again.';
    }
  } catch (err) {
    msgBox.hidden = false;
    msgBox.className = 'form-msg form-msg--error';
    msgBox.textContent = 'Network error. Please check your connection and try again.';
  } finally {
    submitBtn.disabled    = false;
    submitBtn.textContent = 'Send message';
  }
}

// ─── Horizon Design: Reveal on scroll ────────────────────────────────────────

function initReveal() {
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el    = entry.target;
        const cards = el.parentElement ? Array.from(el.parentElement.querySelectorAll('.reveal')) : [];
        const idx   = cards.indexOf(el);
        el.style.transitionDelay = idx > 0 ? (idx * 0.08) + 's' : '0s';
        el.classList.add('in');
        io.unobserve(el);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.reveal').forEach(el => io.observe(el));
}

// Patch goTo to re-run reveal after page switch
const _goToOrig = goTo;
window._goToPatched = function(pageId) {
  _goToOrig(pageId);
  setTimeout(initReveal, 60);
};

// Monkey-patch: replace all existing [data-page] click listeners by
// cloning each button (removes old listeners) and re-adding a single one.
document.querySelectorAll('[data-page]').forEach(btn => {
  const clone = btn.cloneNode(true);
  btn.parentNode.replaceChild(clone, btn);
  clone.addEventListener('click', () => window._goToPatched(clone.dataset.page));
});

initReveal();

// ─── Home: play orb clicks through to Watch page ─────────────────────────────
document.addEventListener('click', e => {
  const orb = e.target.closest('.play-orb[data-page]');
  if (orb) { goTo(orb.dataset.page); return; }
  const fl = e.target.closest('.feat-link[data-page]');
  if (fl)  { goTo(fl.dataset.page); return; }
});


// ─── Announcements ────────────────────────────────────────────────────────────

let _allAnnouncements = [];

async function loadAnnouncements() {
  await Promise.all([loadPinnedAnnouncement(), loadAnnouncementList(null)]);
  bindAnnouncementFilters();
}

// ── Pinned banner ─────────────────────────────────────────────────────────────

async function loadPinnedAnnouncement() {
  const container = document.getElementById('annPinned');
  const res       = await apiFetch('/announcements/pinned');

  if (res.status !== 'success' || !res.data) {
    container.innerHTML = '';
    return;
  }

  const a = res.data;
  container.innerHTML = `
    <div class="ann-pinned" role="button" tabindex="0" aria-label="Read pinned announcement: ${escHtml(a.title)}" data-id="${a.id}">
      <div class="ann-pinned-icon" aria-hidden="true">★</div>
      <div class="ann-pinned-body">
        <div class="ann-pinned-label">Pinned</div>
        <div class="ann-pinned-title">${escHtml(a.title)}</div>
        <p class="ann-pinned-excerpt">${escHtml(a.body)}</p>
      </div>
      <button class="ann-pinned-btn">Read more</button>
    </div>
  `;

  const card = container.querySelector('.ann-pinned');
  const open = () => openAnnouncementModal(a);
  card.addEventListener('click', open);
  card.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
}

// ── List ──────────────────────────────────────────────────────────────────────

async function loadAnnouncementList(category) {
  const container = document.getElementById('annList');
  const countEl   = document.getElementById('annCount');

  container.innerHTML = '<p style="color:var(--ink-soft);padding:20px 0;">Loading…</p>';

  const query = category ? `?category=${encodeURIComponent(category)}` : '';
  const res   = await apiFetch('/announcements' + query);

  if (res.status !== 'success') {
    container.innerHTML = '<p style="color:var(--ink-soft);">Could not load announcements.</p>';
    return;
  }

  _allAnnouncements = res.data || [];
  const total       = res.total ?? _allAnnouncements.length;

  countEl.textContent = `${total} announcement${total !== 1 ? 's' : ''}`;

  if (!_allAnnouncements.length) {
    container.innerHTML = `
      <div class="ann-empty">
        <i data-lucide="megaphone" class="ann-empty-icon"></i>
        <p>No announcements yet. Check back soon.</p>
      </div>`;
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [container] });
    return;
  }

  container.innerHTML = _allAnnouncements.map(a => {
    const d     = new Date(a.published_at);
    const day   = d.getDate();
    const month = d.toLocaleString('default', { month: 'short' }).toUpperCase();

    return `
      <div class="ann-item" data-id="${a.id}">
        <div class="ann-date-block">
          <div class="d">${day}</div>
          <div class="m">${month}</div>
        </div>
        <div class="ann-item-body" role="button" tabindex="0" aria-label="Read: ${escHtml(a.title)}">
          <span class="ann-cat-badge ann-cat-${escHtml(a.category)}">${escHtml(a.category)}</span>
          <div class="ann-item-title">${escHtml(a.title)}</div>
          <p class="ann-item-excerpt">${escHtml(a.body)}</p>
        </div>
      </div>
    `;
  }).join('');

  // Bind body click → open detail modal
  container.querySelectorAll('.ann-item-body').forEach(body => {
    const id   = parseInt(body.closest('.ann-item').dataset.id, 10);
    const open = () => {
      const a = _allAnnouncements.find(x => x.id === id);
      if (a) openAnnouncementModal(a);
    };
    body.addEventListener('click', open);
    body.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
  });

}

// ── Filters ───────────────────────────────────────────────────────────────────

function bindAnnouncementFilters() {
  document.querySelectorAll('#annFilterRow .filter-pill').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('#annFilterRow .filter-pill').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      loadAnnouncementList(pill.dataset.cat || null);
    });
  });
}

// ── Modal ─────────────────────────────────────────────────────────────────────

function initAnnouncementModal() {
  // ── Detail modal ──────────────────────────────────────────────────────────
  document.getElementById('annModalClose').addEventListener('click', closeAnnouncementModal);
  document.getElementById('annModalBackdrop').addEventListener('click', closeAnnouncementModal);

  // ── Add modal ─────────────────────────────────────────────────────────────
  document.getElementById('addAnnModalClose').addEventListener('click', closeAddAnnModal);
  document.getElementById('addAnnModalBackdrop').addEventListener('click', closeAddAnnModal);
  document.getElementById('addAnnCancelBtn').addEventListener('click', closeAddAnnModal);
  document.getElementById('addAnnForm').addEventListener('submit', submitAddAnn);

  // ── Edit modal ────────────────────────────────────────────────────────────
  document.getElementById('editAnnModalClose').addEventListener('click', closeEditAnnModal);
  document.getElementById('editAnnModalBackdrop').addEventListener('click', closeEditAnnModal);
  document.getElementById('editAnnCancelBtn').addEventListener('click', closeEditAnnModal);
  document.getElementById('editAnnForm').addEventListener('submit', submitEditAnn);

  // ── Escape key closes whichever ann modal is open ─────────────────────────
  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (!document.getElementById('annModal').hidden)    { closeAnnouncementModal(); return; }
    if (!document.getElementById('addAnnModal').hidden) { closeAddAnnModal();       return; }
    if (!document.getElementById('editAnnModal').hidden){ closeEditAnnModal();      return; }
  });
}

function openAnnouncementModal(a) {
  const d      = new Date(a.published_at);
  const dateStr = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

  document.getElementById('annModalMeta').textContent  = `${a.category.toUpperCase()} · ${dateStr}`;
  document.getElementById('annModalTitle').textContent = a.title;

  const paragraphs = a.body
    .split(/\n{2,}/)
    .map(p => `<p>${escHtml(p.trim()).replace(/\n/g, '<br>')}</p>`)
    .filter(p => p !== '<p></p>')
    .join('');
  document.getElementById('annModalBody').innerHTML = paragraphs || `<p>${escHtml(a.body)}</p>`;

  const annModalEl = document.getElementById('annModal');
  annModalEl.classList.remove('modal-is-closing');
  annModalEl.hidden = false;
  lockScroll();
  document.getElementById('annModalClose').focus();
}

function closeAnnouncementModal() {
  animatedModalClose(document.getElementById('annModal'), () => {
    unlockScroll();
  });
}

// ── Add Announcement ──────────────────────────────────────────────────────────

function openAddAnnModal() {
  // Reset form
  document.getElementById('addAnnForm').reset();
  const msg = document.getElementById('addAnnMsg');
  msg.hidden = true; msg.textContent = '';
  document.getElementById('addAnnModal').hidden = false;
  lockScroll();
  document.getElementById('addAnnTitle').focus();
}

function closeAddAnnModal() {
  document.getElementById('addAnnModal').hidden = true;
  unlockScroll();
}

async function submitAddAnn(e) {
  e.preventDefault();
  const btn  = document.getElementById('addAnnSubmitBtn');
  const msg  = document.getElementById('addAnnMsg');
  const title    = document.getElementById('addAnnTitle').value.trim();
  const body     = document.getElementById('addAnnBody').value.trim();
  const category = document.getElementById('addAnnCategory').value;
  const is_pinned = document.getElementById('addAnnPinned').checked;

  if (!title) { showFormMsg(msg, 'Title is required.', 'error'); return; }
  if (!body)  { showFormMsg(msg, 'Body is required.',  'error'); return; }

  btn.disabled = true; btn.textContent = 'Posting…'; msg.hidden = true;

  try {
    const res = await apiFetch('/announcements', {
      method: 'POST',
      body:   JSON.stringify({ title, body, category, is_pinned }),
    });

    if (res.status === 'success') {
      showFormMsg(msg, '✓ Announcement posted!', 'success');
      document.getElementById('addAnnForm').reset();
      setTimeout(async () => {
        closeAddAnnModal();
        // Re-load both the pinned banner and the list
        await Promise.all([loadPinnedAnnouncement(), loadAnnouncementList(null)]);
        // Reset filter to All
        document.querySelectorAll('#annFilterRow .filter-pill').forEach(p => p.classList.remove('active'));
        document.querySelector('#annFilterRow .filter-pill[data-cat=""]').classList.add('active');
      }, 800);
    } else {
      showFormMsg(msg, res.message || 'Could not post. Try again.', 'error');
    }
  } catch (err) {
    showFormMsg(msg, 'Network error. Please try again.', 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Post Announcement';
  }
}

// ── Edit Announcement ─────────────────────────────────────────────────────────

function openEditAnnModal(a) {
  document.getElementById('editAnnId').value           = a.id;
  document.getElementById('editAnnTitle').value        = a.title;
  document.getElementById('editAnnBody').value         = a.body;
  document.getElementById('editAnnCategory').value     = a.category;
  document.getElementById('editAnnPinned').checked     = !!a.is_pinned;
  const msg = document.getElementById('editAnnMsg');
  msg.hidden = true; msg.textContent = '';
  document.getElementById('editAnnModal').hidden = false;
  lockScroll();
  document.getElementById('editAnnTitle').focus();
}

function closeEditAnnModal() {
  document.getElementById('editAnnModal').hidden = true;
  unlockScroll();
}

async function submitEditAnn(e) {
  e.preventDefault();
  const btn      = document.getElementById('editAnnSubmitBtn');
  const msg      = document.getElementById('editAnnMsg');
  const id       = parseInt(document.getElementById('editAnnId').value, 10);
  const title    = document.getElementById('editAnnTitle').value.trim();
  const body     = document.getElementById('editAnnBody').value.trim();
  const category = document.getElementById('editAnnCategory').value;
  const is_pinned = document.getElementById('editAnnPinned').checked;

  if (!title) { showFormMsg(msg, 'Title is required.', 'error'); return; }
  if (!body)  { showFormMsg(msg, 'Body is required.',  'error'); return; }

  btn.disabled = true; btn.textContent = 'Saving…'; msg.hidden = true;

  try {
    const res = await apiFetch(`/announcements/${id}`, {
      method: 'PATCH',
      body:   JSON.stringify({ title, body, category, is_pinned }),
    });

    if (res.status === 'success') {
      showFormMsg(msg, '✓ Saved!', 'success');
      setTimeout(async () => {
        closeEditAnnModal();
        await Promise.all([loadPinnedAnnouncement(), loadAnnouncementList(null)]);
        document.querySelectorAll('#annFilterRow .filter-pill').forEach(p => p.classList.remove('active'));
        document.querySelector('#annFilterRow .filter-pill[data-cat=""]').classList.add('active');
      }, 700);
    } else {
      showFormMsg(msg, res.message || 'Could not save. Try again.', 'error');
    }
  } catch (err) {
    showFormMsg(msg, 'Network error. Please try again.', 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Save Changes';
  }
}


// ─── Home Feed ────────────────────────────────────────────────────────────────

async function loadHomeFeed() {
  const feedEl  = document.getElementById('home-feed');
  const emptyEl = document.getElementById('home-feed-empty');
  if (!feedEl) return;

  try {
    // Fetch media, articles, and announcements in parallel
    const [mediaRes, articlesRes, annRes] = await Promise.all([
      apiFetch('/media'),
      apiFetch('/articles'),
      apiFetch('/announcements'),
    ]);

    const media   = (mediaRes.status   === 'success' ? mediaRes.data   : []) || [];
    const articles= (articlesRes.status === 'success' ? articlesRes.data : []) || [];
    const anns    = (annRes.status     === 'success' ? annRes.data     : []) || [];

    // Normalise each item into a common feed shape with a sortable date
    const items = [
      ...media.map(m => ({
        type:           'watch',
        id:             m.id,
        title:          m.title,
        excerpt:        m.description || '',
        date:           m.created_at || m.published_at || '',
        thumb:          resolveThumbnail(m),
        author:         m.posted_by || 'admin',
        authorUsername: m.poster_username || null,
        authorMemberId: m.member_id || null,
        commentCount:   m.comment_count || 0,
        raw:            m,
      })),
      ...articles.map(a => ({
        type:           'read',
        id:             a.id,
        title:          a.title,
        excerpt:        a.excerpt || '',
        date:           a.published_at || a.created_at || '',
        thumb:          null,
        author:         a.posted_by || 'admin',
        authorUsername: a.poster_username || null,
        authorMemberId: a.member_id || null,
        commentCount:   a.comment_count || 0,
        raw:            a,
      })),
      ...anns.map(a => ({
        type:           'notice',
        id:             a.id,
        title:          a.title,
        excerpt:        a.body || '',
        date:           a.created_at || '',
        thumb:          null,
        author:         a.posted_by || 'admin',
        authorUsername: null,
        authorMemberId: a.member_id || null,
        commentCount:   a.comment_count || 0,
        raw:            a,
      })),
    ];

    // Sort newest first
    items.sort((a, b) => new Date(b.date) - new Date(a.date));

    feedEl.innerHTML = '';

    if (!items.length) {
      feedEl.hidden  = true;
      emptyEl.hidden = false;
      return;
    }

    items.forEach(item => {
      const card = buildFeedCard(item);
      feedEl.appendChild(card);
    });

    // Re-run reveal on newly injected cards
    setTimeout(initReveal, 60);
    // Re-init Lucide icons injected by feed cards
    if (typeof lucide !== 'undefined') lucide.createIcons();

  } catch (err) {
    feedEl.innerHTML = `<p style="color:var(--ink-soft);padding:32px 0;grid-column:1/-1">Could not load feed. Please refresh.</p>`;
  }
}

function buildFeedCard(item) {
  const el = document.createElement('div');
  el.className = `feed-card feed-card--${item.type}`;
  el.dataset.itemId = item.id;

  const typeLabel  = { watch: '<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle"><polygon points="6 3 20 12 6 21 6 3"/></svg> Video', read: '📖 Article', notice: '📢 Announcement' }[item.type];

  const dateStr = item.date ? formatTimeAgo(item.date) : '';
  const excerpt = item.excerpt
    ? escHtml(item.excerpt.replace(/<[^>]+>/g, '').slice(0, 120)) + (item.excerpt.length > 120 ? '…' : '')
    : '';

  // Poster info — remap the raw "admin" account to the ministry brand
  const ADMIN_DISPLAY  = 'Agape House Ministries';
  const ADMIN_LOGO_URL = window.APP_BASE_URL + '/public/images/agape1.jpg';
  const isAdminPoster  = !item.authorUsername && (
    !item.author ||
    item.author === 'admin' ||
    item.author === 'Agape House' ||
    item.author === 'Agape House Ministries'
  );

  const authorName    = isAdminPoster ? ADMIN_DISPLAY : (item.author || ADMIN_DISPLAY);
  const authorInitial = authorName[0].toUpperCase();

  // Use the poster_picture from the API response directly (works for any member, not just the current user).
  // Fall back to the current member's picture only if they are the poster (handles the case where
  // the session has a freshly-uploaded avatar not yet reflected in the DB join).
  const currentMember = window.CURRENT_MEMBER;
  const currentName   = currentMember
    ? (currentMember.display_name || currentMember.username || '')
    : '';
  const apiPic = item.raw && (item.raw.poster_picture || null);
  const selfPic = (currentMember && currentName === authorName && currentMember.profile_picture)
    ? currentMember.profile_picture
    : null;
  const authorPic = isAdminPoster ? ADMIN_LOGO_URL : (apiPic || selfPic);

  const posterAvatarHtml = item.authorUsername
    ? (authorPic
        ? `<a href="/DigitalEvangelization/member/${escHtml(item.authorUsername)}" class="feed-poster-avatar feed-poster-avatar--img feed-poster-avatar--link"><img src="${escHtml(authorPic)}" alt="${escHtml(authorInitial)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;"></a>`
        : `<a href="/DigitalEvangelization/member/${escHtml(item.authorUsername)}" class="feed-poster-avatar feed-poster-avatar--link">${escHtml(authorInitial)}</a>`)
    : (authorPic
        ? `<div class="feed-poster-avatar feed-poster-avatar--img"><img src="${escHtml(authorPic)}" alt="${escHtml(authorInitial)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;"></div>`
        : `<div class="feed-poster-avatar">${escHtml(authorInitial)}</div>`);

  let thumbHtml = '';
  if (item.type === 'watch') {
    if (item.thumb) {
      thumbHtml = `<div class="feed-card-thumb"><img src="${escHtml(item.thumb)}" alt="" loading="lazy"></div>`;
    } else {
      thumbHtml = `<div class="feed-card-thumb-placeholder"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3"/></svg></div>`;
    }
  }

  // ── Like state: start with 0/false then hydrate from server ──
  // The DB is the source of truth; localStorage is gone.
  const targetApiType = { watch: 'media', read: 'article', notice: 'announcement' }[item.type] || item.type;

  const actionsHtml = `
    <div class="feed-card-actions">
      <button class="feed-action-btn feed-like-btn" title="Like" data-liked="0" data-count="0">
        <span class="feed-action-icon"><i data-lucide="heart" class="icon-heart-empty"></i></span>
        <span class="feed-like-count"></span>
        <span>Like</span>
      </button>
      <button class="feed-action-btn feed-comment-btn" title="Comment" data-count="${item.commentCount || 0}">
        <span class="feed-action-icon"><i data-lucide="message-circle"></i></span>
        ${item.commentCount > 0 ? `<span class="feed-comment-count">${item.commentCount}</span>` : ''}
        <span>Comment</span>
      </button>
      <button class="feed-action-btn feed-share-btn" title="Share">
        <span class="feed-action-icon"><i data-lucide="share-2"></i></span>
        <span>Share</span>
      </button>
    </div>`;

  const bodyHtml = `
    <div class="feed-card-body">
      <p class="feed-card-title">${escHtml(item.title)}</p>
      ${excerpt ? `<p class="feed-card-excerpt">${excerpt}</p>` : ''}
    </div>`;

  const posterHtml = `
    <div class="feed-card-poster">
      ${posterAvatarHtml}
      <div class="feed-poster-info">
        ${item.authorUsername
          ? `<a class="feed-poster-name feed-poster-name--link" href="/DigitalEvangelization/member/${escHtml(item.authorUsername)}">${escHtml(authorName)}</a>`
          : `<span class="feed-poster-name">${escHtml(authorName)}</span>`
        }
        <span class="feed-poster-meta"><span class="badge-dot"></span>${typeLabel}${dateStr ? ' · ' + dateStr : ''}</span>
      </div>
    </div>`;

  if (item.type === 'watch') {
    el.innerHTML = `
      ${thumbHtml}
      ${posterHtml}
      <div class="feed-card-content">
        ${bodyHtml}
        ${actionsHtml}
      </div>`;
  } else {
    el.innerHTML = `
      ${posterHtml}
      ${thumbHtml}
      ${bodyHtml}
      ${actionsHtml}`;
  }

  // Helper: apply liked visual state to the button
  function applyLikeState(btn, liked, count) {
    btn.dataset.liked = liked ? '1' : '0';
    btn.dataset.count = String(count);
    btn.classList.toggle('liked', liked);
    const iconEl    = btn.querySelector('.feed-action-icon svg');
    const countSpan = btn.querySelector('.feed-like-count');
    if (iconEl)    iconEl.style.fill   = liked ? 'currentColor' : 'none';
    if (countSpan) countSpan.textContent = count > 0 ? String(count) : '';
  }

  const likeBtn = el.querySelector('.feed-like-btn');

  // Hydrate like state from server (non-blocking)
  apiFetch(`/likes/${targetApiType}/${item.id}`)
    .then(res => {
      if (res.status === 'success') {
        applyLikeState(likeBtn, res.data.liked, res.data.like_count);
      }
    })
    .catch(() => {});

  // Like button — toggle via server
  likeBtn.addEventListener('click', e => {
    e.stopPropagation();
    if (!window.CURRENT_MEMBER) {
      // Prompt login
      window.location.href = (window.APP_BASE_URL || '') + '/member/login';
      return;
    }

    const wasLiked  = likeBtn.dataset.liked === '1';
    const prevCount = parseInt(likeBtn.dataset.count, 10) || 0;
    const newLiked  = !wasLiked;
    const optimisticCount = Math.max(0, prevCount + (newLiked ? 1 : -1));

    // Optimistic update
    applyLikeState(likeBtn, newLiked, optimisticCount);

    // ── Animations (only when liking, not unliking) ──
    if (newLiked) {
      // 1. Heart pop
      likeBtn.classList.remove('like-pop');
      void likeBtn.offsetWidth;
      likeBtn.classList.add('like-pop');
      likeBtn.addEventListener('animationend', () => likeBtn.classList.remove('like-pop'), { once: true });

      // 2. Ripple ring centered on the click point
      const rect   = likeBtn.getBoundingClientRect();
      const cx     = e.clientX - rect.left;
      const cy     = e.clientY - rect.top;
      const ripple = document.createElement('span');
      ripple.className = 'like-ripple-el';
      ripple.style.left = cx + 'px';
      ripple.style.top  = cy + 'px';
      likeBtn.appendChild(ripple);
      ripple.addEventListener('animationend', () => ripple.remove(), { once: true });

      // 3. Floating hearts burst
      const actionsBar = likeBtn.closest('.feed-card-actions');
      const barRect    = actionsBar ? actionsBar.getBoundingClientRect() : rect;
      const heartX     = e.clientX - barRect.left;
      const heartY     = e.clientY - barRect.top;
      [-14, 0, 14].forEach((drift, i) => {
        setTimeout(() => {
          const heart = document.createElement('span');
          heart.className = 'float-heart';
          heart.textContent = '❤️';
          heart.style.left  = (heartX + drift - 7) + 'px';
          heart.style.top   = (heartY - 4) + 'px';
          heart.style.setProperty('--drift', drift + 'px');
          heart.style.animationDelay = (i * 55) + 'ms';
          (actionsBar || likeBtn).appendChild(heart);
          heart.addEventListener('animationend', () => heart.remove(), { once: true });
        }, i * 40);
      });
    }

    // Persist to server (notification handled server-side)
    apiFetch(`/likes/${targetApiType}/${item.id}`, { method: 'POST' })
      .then(res => {
        if (res.status === 'success') {
          // Reconcile with server truth
          applyLikeState(likeBtn, res.data.liked, res.data.like_count);
          if (res.data.liked) window._refreshNotifBadge?.();
        } else {
          // Revert optimistic update on error
          applyLikeState(likeBtn, wasLiked, prevCount);
        }
      })
      .catch(() => {
        // Revert on network error
        applyLikeState(likeBtn, wasLiked, prevCount);
      });
  });

  // Comment button — open comment drawer
  const commentBtn = el.querySelector('.feed-comment-btn');
  commentBtn.addEventListener('click', e => {
    e.stopPropagation();
    openCommentDrawer(item.type, item.id, item.title, item.authorMemberId || null);
  });

  // Share button — Web Share API with clipboard fallback
  const shareBtn = el.querySelector('.feed-share-btn');
  shareBtn.addEventListener('click', async e => {
    e.stopPropagation();
    const shareData = {
      title: item.title,
      text:  item.excerpt.replace(/<[^>]+>/g, '').slice(0, 100),
      url:   window.location.href,
    };
    if (navigator.share) {
      try { await navigator.share(shareData); } catch (_) {}
    } else {
      await navigator.clipboard.writeText(shareData.url);
      const icon = shareBtn.querySelector('.feed-action-icon');
      const orig = icon.textContent;
      icon.textContent = '✓';
      setTimeout(() => { icon.textContent = orig; }, 1800);
    }

    // ── Notify the post author on share (fire-and-forget) ──
    if (window.CURRENT_MEMBER && item.authorMemberId && item.authorMemberId !== window.CURRENT_MEMBER.id) {
      const notifType = { watch: 'media', read: 'article', notice: 'announcement' }[item.type] || item.type;
      apiFetch('/notifications/share', {
        method: 'POST',
        body: JSON.stringify({
          target_type:  notifType,
          target_id:    item.id,
          target_title: item.title,
          recipient_id: item.authorMemberId,
        }),
      }).then(() => window._refreshNotifBadge?.()).catch(() => {});
    }
  });

  // Click on the card body opens the content
  el.querySelector('.feed-card-body').addEventListener('click', () => {
    if (item.type === 'watch')  { openVideoModal(item.raw); return; }
    if (item.type === 'read')   { openArticleModal(item.raw); return; }
    if (item.type === 'notice') { openAnnouncementModal(item.raw); return; }
  });

  // Click on the thumbnail also opens the video
  if (item.type === 'watch') {
    const thumb = el.querySelector('.feed-card-thumb, .feed-card-thumb-placeholder');
    if (thumb) thumb.addEventListener('click', () => openVideoModal(item.raw));
  }

  return el;
}

// Load feed on initial home view
loadHomeFeed();

// Init video modal close/backdrop listeners once at startup
// (needed when videos are opened from the home feed before the Watch page loads)
initVideoModal();

// Init article modal close/backdrop listeners once at startup
// (needed when articles are opened from the home feed before the Read page loads)
initArticleModal();


// ─── Comment Drawer ───────────────────────────────────────────────────────────

let _commentTarget = null; // { type, id, title, authorMemberId }

function openCommentDrawer(type, id, title, authorMemberId) {
  // Map feed type keys to API target types
  const typeMap = { watch: 'media', read: 'article', notice: 'announcement' };
  const apiType = typeMap[type] || type;

  _commentTarget = { type: apiType, id, title, authorMemberId: authorMemberId || null };

  const drawer    = document.getElementById('commentDrawer');
  const listEl    = document.getElementById('commentList');
  const formWrap  = document.getElementById('commentFormWrap');
  const titleEl   = drawer.querySelector('.comment-drawer-title');

  titleEl.textContent = 'Comments';
  listEl.innerHTML    = '<div class="comment-loading"><span class="feed-spinner"></span> Loading…</div>';
  drawer.hidden       = false;
  lockScroll();

  // Render form or login prompt
  renderCommentForm(formWrap);

  // Load existing comments
  loadComments(apiType, id, listEl);
}

function closeCommentDrawer() {
  const drawer = document.getElementById('commentDrawer');
  if (!drawer || drawer.hidden) return;
  drawer.classList.add('is-closing');
  setTimeout(() => {
    drawer.classList.remove('is-closing');
    drawer.hidden = true;
    unlockScroll();
    _commentTarget = null;
  }, 280);
}

// Close on backdrop click or X button
document.getElementById('commentDrawerClose').addEventListener('click', closeCommentDrawer);
document.getElementById('commentDrawer').querySelector('.comment-drawer-backdrop')
  .addEventListener('click', closeCommentDrawer);

// Close on Escape key
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && _commentTarget) closeCommentDrawer();
});

async function loadComments(type, id, listEl) {
  try {
    const res = await apiFetch(`/comments/${type}/${id}`);
    renderCommentList(res.data || [], listEl);
  } catch {
    listEl.innerHTML = '<p class="comment-empty">Could not load comments.</p>';
  }
}

function renderCommentList(comments, listEl) {
  if (!comments.length) {
    listEl.innerHTML = `
      <div class="comment-empty">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>No comments yet. Be the first!</span>
      </div>`;
    return;
  }

  listEl.innerHTML = '';
  comments.forEach(c => {
    listEl.appendChild(buildCommentItem(c, listEl));
  });
  // Scroll to bottom so newest is visible
  listEl.scrollTop = listEl.scrollHeight;
}

function buildCommentItem(c, listEl, rootId) {
  // rootId is the top-level comment id; replies are always stored flat under it
  const _rootId = rootId || c.id;
  const wrap    = document.createElement('div');
  wrap.className = 'comment-item';
  wrap.dataset.commentId = c.id;

  const name    = c.member_display_name || c.member_username || '?';
  const initial = name[0].toUpperCase();
  const pic     = c.member_profile_picture;
  const isOwn   = window.CURRENT_MEMBER && window.CURRENT_MEMBER.id === c.member_id;
  const isLoggedIn = !!window.CURRENT_MEMBER;

  // Like state (localStorage per member)
  const uid      = window.CURRENT_MEMBER ? window.CURRENT_MEMBER.id : 'guest';
  const likedKey = `cmt_liked_${uid}_${c.id}`;
  // If the server says like_count is 0, clear any stale localStorage entry
  // (e.g. from a previously deleted comment that happened to get the same DB ID).
  if ((c.like_count || 0) === 0) {
    localStorage.removeItem(likedKey);
  }
  // You can't have already liked your own brand-new comment.
  const isOwnFreshComment = window.CURRENT_MEMBER && c.member_id === window.CURRENT_MEMBER.id && (c.like_count || 0) === 0;
  if (isOwnFreshComment) {
    localStorage.removeItem(likedKey);
  }
  let   liked    = localStorage.getItem(likedKey) === '1';
  let   likes    = c.like_count || 0;

  const avatarHtml = pic
    ? `<div class="comment-avatar comment-avatar--img"><img src="${escHtml(pic)}" alt="${escHtml(initial)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;"></div>`
    : `<div class="comment-avatar">${escHtml(initial)}</div>`;

  // Flat layout: avatar + content column (name+time, body, actions)
  wrap.innerHTML = `
    ${avatarHtml}
    <div class="comment-bubble">
      <div class="comment-bubble-inner">
        <div class="comment-head">
          <span class="comment-author">${escHtml(name)}</span>
          <span class="comment-time">${formatTimeAgo(c.created_at)}</span>
        </div>
        <div class="comment-body">${escHtml(c.body)}</div>
      </div>
      <div class="comment-actions">
        <button class="comment-action-btn cmt-like-btn${liked ? ' liked' : ''}" title="Like">
          <svg viewBox="0 0 24 24" fill="${liked ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          <span class="cmt-like-count">${likes > 0 ? likes : ''}</span>
        </button>
        ${isLoggedIn ? `<button class="comment-action-btn cmt-reply-btn" title="Reply">Reply</button>` : ''}
        ${isOwn ? `<button class="comment-delete-btn" data-id="${c.id}">Delete</button>` : ''}
      </div>
    </div>
  `;

  // ── Like ──
  const likeBtn   = wrap.querySelector('.cmt-like-btn');
  const likeCount = wrap.querySelector('.cmt-like-count');
  likeBtn.addEventListener('click', async () => {
    if (!window.CURRENT_MEMBER) return;
    liked = !liked;
    likes = Math.max(0, likes + (liked ? 1 : -1));
    localStorage.setItem(likedKey, liked ? '1' : '0');
    likeBtn.classList.toggle('liked', liked);
    const svg = likeBtn.querySelector('svg');
    if (svg) svg.setAttribute('fill', liked ? 'currentColor' : 'none');
    likeCount.textContent = likes > 0 ? likes : '';

    // Update DB like count
    apiFetch(`/comments/${c.id}/like`, {
      method: 'POST',
      body: JSON.stringify({ liked }),
    }).catch(() => {});

    // Notify comment author (if not yourself and _commentTarget exists)
    if (c.member_id && c.member_id !== window.CURRENT_MEMBER.id && _commentTarget) {
      apiFetch('/notifications/comment-like', {
        method: 'POST',
        body: JSON.stringify({
          comment_id:   c.id,
          comment_body: c.body,
          recipient_id: c.member_id,
          target_type:  _commentTarget.type,
          target_id:    _commentTarget.id,
          target_title: _commentTarget.title,
          liked,
        }),
      }).then(() => { if (liked) window._refreshNotifBadge?.(); }).catch(() => {});
    }
  });

  // ── Reply ──
  const replyBtn = wrap.querySelector('.cmt-reply-btn');
  if (replyBtn) {
    replyBtn.addEventListener('click', () => {
      // Toggle: if form already open, close it
      const existing = wrap.querySelector(`.comment-reply-form[data-for="${c.id}"]`);
      if (existing) { existing.remove(); return; }

      const form = document.createElement('div');
      form.className = 'comment-reply-form';
      form.dataset.for = c.id;

      const replyName    = window.CURRENT_MEMBER.display_name || window.CURRENT_MEMBER.username || '?';
      const replyInitial = replyName[0].toUpperCase();
      const replyPic     = window.CURRENT_MEMBER.profile_picture;
      const replyAvatar  = replyPic
        ? `<div class="comment-avatar comment-avatar--img"><img src="${escHtml(replyPic)}" alt="${escHtml(replyInitial)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;"></div>`
        : `<div class="comment-avatar">${escHtml(replyInitial)}</div>`;

      form.innerHTML = `
        ${replyAvatar}
        <div class="comment-input-wrap">
          <input type="text" class="comment-input" placeholder="Reply to ${escHtml(name)}…" autocomplete="off">
          <button class="comment-submit-btn" aria-label="Post reply">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
          </button>
        </div>
      `;

      const textarea  = form.querySelector('input');
      const submitBtn = form.querySelector('button');

      textarea.addEventListener('input', () => {
        submitBtn.classList.toggle('active', textarea.value.trim().length > 0);
      });
      textarea.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); if (submitBtn.classList.contains('active')) doReply(); }
      });
      submitBtn.addEventListener('click', () => { if (submitBtn.classList.contains('active')) doReply(); });

      // Append inside wrap so it is a full-width block below the row
      wrap.appendChild(form);
      textarea.focus();

      async function doReply() {
        const text = textarea.value.trim();
        if (!text || !_commentTarget) return;
        submitBtn.classList.remove('active');
        submitBtn.style.opacity = '.4';
        submitBtn.style.pointerEvents = 'none';
        try {
          const res = await apiFetch('/comments', {
            method: 'POST',
            body: JSON.stringify({
              target_type: _commentTarget.type,
              target_id:   _commentTarget.id,
              body:        text,
              parent_id:   _rootId,
            }),
          });
          if (res.status === 'success') {
            form.remove();
            // Always append into the root (top-level) comment's replies container
            const rootWrap = listEl.querySelector(`.comment-item[data-comment-id="${_rootId}"]`);
            const targetWrap = rootWrap || wrap;
            let repliesEl = targetWrap.querySelector(':scope > .comment-replies');
            if (!repliesEl) {
              repliesEl = document.createElement('div');
              repliesEl.className = 'comment-replies';
              targetWrap.appendChild(repliesEl);
            }
            repliesEl.appendChild(buildCommentItem(res.data, listEl, _rootId));

            // Notify the parent comment author (fire-and-forget)
            if (c.member_id && c.member_id !== window.CURRENT_MEMBER.id && _commentTarget) {
              apiFetch('/notifications/comment-reply', {
                method: 'POST',
                body: JSON.stringify({
                  comment_id:   c.id,
                  comment_body: c.body,
                  recipient_id: c.member_id,
                  target_type:  _commentTarget.type,
                  target_id:    _commentTarget.id,
                  target_title: _commentTarget.title,
                }),
              }).then(() => window._refreshNotifBadge?.()).catch(() => {});
            }
          } else {
            alert(res.message || 'Could not post reply.');
          }
        } catch { alert('Network error. Try again.'); }
        finally {
          submitBtn.style.opacity = '';
          submitBtn.style.pointerEvents = '';
          if (textarea.value.trim().length > 0) submitBtn.classList.add('active');
        }
      }
    });
  }

  // ── Nested replies (pre-existing) ──
  if (c.replies && c.replies.length) {
    const repliesEl = document.createElement('div');
    repliesEl.className = 'comment-replies';
    c.replies.forEach(r => repliesEl.appendChild(buildCommentItem(r, listEl, _rootId)));
    wrap.appendChild(repliesEl);
  }

  if (isOwn) {
    wrap.querySelector('.comment-delete-btn').addEventListener('click', () => deleteComment(c.id, wrap));
  }

  return wrap;
}

async function deleteComment(commentId, itemEl) {
  if (!confirm('Delete your comment?')) return;
  try {
    const res = await apiFetch(`/comments/${commentId}`, { method: 'DELETE' });
    if (res.status === 'success') {
      itemEl.remove();
      const listEl = document.getElementById('commentList');
      if (!listEl.querySelector('.comment-item')) {
        listEl.innerHTML = `
          <div class="comment-empty">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>No comments yet. Be the first!</span>
          </div>`;
      }
    }
  } catch {
    alert('Could not delete comment. Try again.');
  }
}

function renderCommentForm(formWrap) {
  if (!window.CURRENT_MEMBER) {
    formWrap.innerHTML = `
      <div class="comment-login-prompt">
        <a href="${window.APP_BASE_URL}/member/login">Sign in</a> to leave a comment.
      </div>
    `;
    return;
  }

  const name    = window.CURRENT_MEMBER.display_name || window.CURRENT_MEMBER.username || '?';
  const initial = name[0].toUpperCase();
  const pic     = window.CURRENT_MEMBER.profile_picture;

  const avatarHtml = pic
    ? `<div class="comment-form-avatar"><img src="${escHtml(pic)}" alt="${escHtml(initial)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;"></div>`
    : `<div class="comment-form-avatar">${escHtml(initial)}</div>`;

  formWrap.innerHTML = `
    <div class="comment-form">
      ${avatarHtml}
      <div class="comment-input-wrap">
        <input type="text" class="comment-input" id="commentInput" placeholder="Write a comment…" autocomplete="off">
        <button class="comment-submit-btn" id="commentSubmitBtn" aria-label="Send">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
        </button>
      </div>
    </div>
  `;

  const input  = formWrap.querySelector('#commentInput');
  const submit = formWrap.querySelector('#commentSubmitBtn');

  // Send button starts inactive; activates when text is present
  input.addEventListener('input', () => {
    submit.classList.toggle('active', input.value.trim().length > 0);
  });

  input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      if (submit.classList.contains('active')) submitComment();
    }
  });

  submit.addEventListener('click', () => {
    if (submit.classList.contains('active')) submitComment();
  });
}

async function submitComment() {
  if (!_commentTarget || !window.CURRENT_MEMBER) return;

  const input  = document.getElementById('commentInput');
  const submit = document.getElementById('commentSubmitBtn');
  const body   = input.value.trim();

  if (!body) return;

  submit.classList.remove('active');
  submit.style.opacity = '.4';
  submit.style.pointerEvents = 'none';

  try {
    const res = await apiFetch('/comments', {
      method: 'POST',
      body: JSON.stringify({
        target_type: _commentTarget.type,
        target_id:   _commentTarget.id,
        body,
      }),
    });

    if (res.status === 'success') {
      input.value = '';

      const listEl = document.getElementById('commentList');
      // Remove empty state if present
      const emptyEl = listEl.querySelector('.comment-empty');
      if (emptyEl) emptyEl.remove();

      const item = buildCommentItem(res.data, listEl);
      listEl.appendChild(item);
      listEl.scrollTop = listEl.scrollHeight;

      // Update comment count on the card button
      updateCommentCount(_commentTarget.type, _commentTarget.id, 1);

      // ── Notify the post author (fire-and-forget) ──
      if (_commentTarget.authorMemberId && window.CURRENT_MEMBER &&
          _commentTarget.authorMemberId !== window.CURRENT_MEMBER.id) {
        apiFetch('/notifications/comment', {
          method: 'POST',
          body: JSON.stringify({
            target_type:  _commentTarget.type,
            target_id:    _commentTarget.id,
            target_title: _commentTarget.title,
            recipient_id: _commentTarget.authorMemberId,
          }),
        }).then(() => window._refreshNotifBadge?.()).catch(() => {});
      }
    } else {
      alert(res.message || 'Could not post comment.');
    }
  } catch {
    alert('Network error. Try again.');
  } finally {
    submit.style.opacity = '';
    submit.style.pointerEvents = '';
    // Re-activate if user typed something while submitting
    if (input.value.trim().length > 0) submit.classList.add('active');
  }
}

function updateCommentCount(apiType, id, delta) {
  // Map api type back to feed type
  const feedTypeMap = { media: 'watch', article: 'read', announcement: 'notice' };
  const feedType = feedTypeMap[apiType] || apiType;

  // Find the matching feed card and update its comment button label
  const cards = document.querySelectorAll(`#home-feed .feed-card--${feedType}`);
  cards.forEach(card => {
    // We store item id on the card via data attribute added below
    if (String(card.dataset.itemId) === String(id)) {
      const btn   = card.querySelector('.feed-comment-btn');
      const count = parseInt(btn.dataset.count || '0', 10) + delta;
      btn.dataset.count = count;
      const icon = btn.querySelector('.feed-action-icon');
      btn.innerHTML = `<span class="feed-action-icon"><i data-lucide="message-circle"></i></span>${count > 0 ? `<span class="feed-comment-count">${count}</span>` : ''}<span>Comment</span>`;
      if (typeof lucide !== 'undefined') lucide.createIcons({ nameAttr: 'data-lucide', attrs: { class: '' } });
    }
  });
}


// ─── Sidebar: Quick nav items ─────────────────────────────────────────────────
document.querySelectorAll('.sidebar-nav-item[data-page]').forEach(btn => {
  btn.addEventListener('click', () => window._goToPatched(btn.dataset.page));
});

// ─── Sidebar: Right — all widgets ────────────────────────────────────────────
async function loadSidebar() {
  await Promise.all([
    loadSidebarServices(),
    loadSidebarEvents(),
    loadSidebarPrayers(),
    loadSidebarSeries(),
    loadSidebarAnnouncements(),
  ]);
}

/* Left sidebar — live service indicator */
async function loadSidebarServices() {
  const statusEl   = document.getElementById('liveStatusText');
  const dot        = document.getElementById('sidebarLiveDot');
  const upcomingEl = document.getElementById('sidebarUpcomingServices');
  if (!statusEl) return;

  try {
    const res    = await apiFetch('/events/upcoming');
    const events = (res.status === 'success' ? res.data : []) || [];
    const now    = new Date();
    const todayStr = now.toISOString().slice(0, 10);

    const liveNow = events.find(e => {
      if (e.event_date !== todayStr) return false;
      const [h, m] = (e.start_time || '00:00').split(':').map(Number);
      const start  = new Date(now); start.setHours(h, m, 0, 0);
      const end    = new Date(start.getTime() + 2 * 60 * 60 * 1000);
      return now >= start && now <= end;
    });

    if (liveNow) {
      dot && dot.classList.remove('offline');
      statusEl.innerHTML = `<strong style="color:#4caf50">🔴 Live now</strong> — ${escHtml(liveNow.title)}`;
    } else {
      dot && dot.classList.add('offline');
      const next = events[0];
      if (next) {
        const d    = new Date(next.event_date + 'T' + (next.start_time || '00:00'));
        const when = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        const time = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        statusEl.innerHTML = `Next: <strong>${escHtml(next.title)}</strong> · ${when} ${time}`;
      } else {
        statusEl.textContent = 'No upcoming services';
      }
    }

    const slice = events.slice(0, 3);
    if (upcomingEl && slice.length) {
      upcomingEl.innerHTML = slice.map(e => {
        const d    = new Date(e.event_date + 'T' + (e.start_time || '00:00'));
        const when = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        const time = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        return `<div class="sidebar-service-item">
          <strong>${escHtml(e.title)}</strong>
          ${when} · ${time}${e.location ? ' · ' + escHtml(e.location) : ''}
        </div>`;
      }).join('');
    }
  } catch {
    if (statusEl) { statusEl.textContent = 'Could not load'; }
    if (dot) dot.classList.add('offline');
  }
}

/* Right sidebar — upcoming events */
async function loadSidebarEvents() {
  const el = document.getElementById('rsidebarEvents');
  if (!el) return;
  try {
    const res    = await apiFetch('/events/upcoming');
    const events = (res.status === 'success' ? res.data : []) || [];
    if (!events.length) { el.innerHTML = '<p style="font-size:12px;color:var(--ink-soft)">No upcoming events.</p>'; return; }

    el.innerHTML = events.slice(0, 3).map(e => {
      const d    = new Date(e.event_date + 'T' + (e.start_time || '00:00'));
      const when = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
      const time = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
      return `<div class="rside-event-item">
        <div class="rside-event-date">${when} · ${time}</div>
        <div class="rside-event-title">${escHtml(e.title)}</div>
        ${e.location ? `<div class="rside-event-location"><i data-lucide="map-pin"></i> ${escHtml(e.location)}</div>` : ''}
      </div>`;
    }).join('');
    if (typeof lucide !== 'undefined') lucide.createIcons({ nameAttr: 'data-lucide', attrs: { class: '' } });
  } catch { el.innerHTML = ''; }
}

/* Right sidebar — prayer wall preview */
async function loadSidebarPrayers() {
  const el = document.getElementById('rsidebarPrayers');
  if (!el) return;
  try {
    const res     = await apiFetch('/prayers');
    const prayers = (res.status === 'success' ? res.data : []) || [];
    if (!prayers.length) { el.innerHTML = '<p style="font-size:12px;color:var(--ink-soft)">No requests yet.</p>'; return; }

    el.innerHTML = prayers.slice(0, 3).map(p => {
      const body = p.body.slice(0, 80) + (p.body.length > 80 ? '…' : '');
      return `<div class="rside-prayer-item">
        <div class="rside-prayer-name">${escHtml(p.name || 'Anonymous')} · ${escHtml(p.category)}</div>
        <div class="rside-prayer-body">${escHtml(body)}</div>
        <button class="rside-pray-btn" data-id="${p.id}"><i data-lucide="hand-heart"></i> Pray (${p.pray_count})</button>
      </div>`;
    }).join('');

    el.querySelectorAll('.rside-pray-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id  = btn.dataset.id;
        const res = await apiFetch(`/prayers/${id}/pray`, { method: 'POST' });
        if (res.status === 'success') {
          btn.innerHTML = `<i data-lucide="hand-heart"></i> Praying (${res.data.pray_count})`;
          btn.classList.add('prayed');
          btn.disabled = true;
          if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [btn] });
        }
      });
    });

    // Init Lucide icons for sidebar pray buttons
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: Array.from(el.querySelectorAll('.rside-pray-btn')) });
  } catch { el.innerHTML = ''; }
}

/* Right sidebar — latest sermon series */
async function loadSidebarSeries() {
  const card = document.getElementById('rsidebarSeriesCard');
  const el   = document.getElementById('rsidebarSeries');
  if (!el || !card) return;
  try {
    const res  = await apiFetch('/media/featured');
    const m    = res.status === 'success' ? res.data : null;
    if (!m) return;

    el.innerHTML = `<div class="rside-series-wrap" id="rsidebarSeriesBtn">
      <div class="rside-series-art">🎬</div>
      <div>
        <div class="rside-series-title">${escHtml(m.series || m.title)}</div>
        <div class="rside-series-sub">Latest: ${escHtml(m.title)}</div>
      </div>
    </div>`;
    card.hidden = false;

    document.getElementById('rsidebarSeriesBtn').addEventListener('click', () => openVideoModal(m));
  } catch {}
}

/* Right sidebar — announcements digest */
async function loadSidebarAnnouncements() {
  const el = document.getElementById('rsidebarAnn');
  if (!el) return;
  try {
    const res  = await apiFetch('/announcements');
    const anns = (res.status === 'success' ? res.data : []) || [];
    if (!anns.length) { el.innerHTML = '<p style="font-size:12px;color:var(--ink-soft)">No announcements.</p>'; return; }

    el.innerHTML = anns.slice(0, 5).map(a => `
      <div class="rside-ann-item" data-id="${a.id}">
        <div class="rside-ann-title">${escHtml(a.title)}</div>
        <div class="rside-ann-date">${formatTimeAgo(a.created_at)}</div>
      </div>
    `).join('');

    const annMap = Object.fromEntries(anns.map(a => [String(a.id), a]));
    el.querySelectorAll('.rside-ann-item').forEach(item => {
      item.addEventListener('click', () => {
        const a = annMap[item.dataset.id];
        if (a) openAnnouncementModal(a);
      });
    });
  } catch { el.innerHTML = ''; }
}

loadSidebar();


// ─── Sidebar independent scroll (capture wheel events) ───────────────────────
document.querySelectorAll('.feed-sidebar-inner').forEach(el => {
  el.addEventListener('wheel', function(e) {
    const atTop    = this.scrollTop === 0;
    const atBottom = this.scrollTop + this.clientHeight >= this.scrollHeight - 1;

    // Only prevent page scroll if the sidebar itself can still scroll in that direction
    if ((e.deltaY < 0 && !atTop) || (e.deltaY > 0 && !atBottom)) {
      e.stopPropagation();
    }
  }, { passive: true });
});


// ─── My Prayer Requests Drawer ────────────────────────────────────────────────

/**
 * localStorage key for member's own prayer submissions.
 * Stored as: [ { id, category, body, status, submitted_at } ]
 */
function myPrayerStorageKey() {
  const uid = window.CURRENT_MEMBER ? window.CURRENT_MEMBER.id : 'guest';
  return `my_prayers_${uid}`;
}

function getMyPrayers() {
  try {
    return JSON.parse(localStorage.getItem(myPrayerStorageKey()) || '[]');
  } catch { return []; }
}

function saveMyPrayer(prayer) {
  const list = getMyPrayers();
  list.unshift(prayer); // newest first
  localStorage.setItem(myPrayerStorageKey(), JSON.stringify(list));
}

function removeMyPrayer(id) {
  const list = getMyPrayers().filter(p => String(p.id) !== String(id));
  localStorage.setItem(myPrayerStorageKey(), JSON.stringify(list));
  renderMyPrayerList();
}

// Open / close drawer
function openPrayerDrawer() {
  const drawer = document.getElementById('prayerDrawer');
  if (!drawer) return;
  drawer.hidden = false;
  lockScroll();
  renderMyPrayerList();
  document.getElementById('drawerPreq')?.focus();
}

function closePrayerDrawer() {
  const drawer = document.getElementById('prayerDrawer');
  if (!drawer || drawer.hidden) return;
  drawer.classList.add('is-closing');
  setTimeout(() => {
    drawer.classList.remove('is-closing');
    drawer.hidden = true;
    unlockScroll();
  }, 280); // matches animation duration
}

// Render the list of this member's saved prayers
function renderMyPrayerList() {
  const listEl    = document.getElementById('myPrayerList');
  const subtitle  = document.getElementById('prayerDrawerSubtitle');
  const countEl   = document.getElementById('myPrayerListCount');
  if (!listEl) return;
  const prayers = getMyPrayers();

  // Update count badges
  if (subtitle) subtitle.textContent = `${prayers.length} total`;
  if (countEl)  countEl.textContent  = String(prayers.length);

  const statusMeta = {
    approved: { cls: 'approved', label: 'On the wall',
      icon: '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' },
    pending:  { cls: 'pending',  label: 'Pending',
      icon: '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
    answered: { cls: 'answered', label: 'Answered',
      icon: '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>' },
    rejected: { cls: 'rejected', label: 'Not posted',
      icon: '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' },
  };

  const listHead = `
    <div class="prayer-list-head">
      <span class="prayer-list-label">Your submitted requests</span>
      <span class="prayer-list-count" id="myPrayerListCount">${prayers.length}</span>
    </div>`;

  if (!prayers.length) {
    listEl.innerHTML = listHead + '<p class="prayer-drawer-empty">You haven\'t submitted any requests yet.</p>';
    return;
  }

  const cards = prayers.map(p => {
    const meta    = statusMeta[p.status] || statusMeta.pending;
    const anonBadge = p.anonymous
      ? `<span class="my-prayer-anon-badge">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Anonymous
        </span>` : '';
    return `
    <div class="my-prayer-item" data-prayer-id="${escHtml(String(p.id))}">
      <div class="my-prayer-item-header">
        <div class="my-prayer-item-header-left">
          <span class="my-prayer-cat">${escHtml(p.category)}</span>
          ${anonBadge}
        </div>
        <span class="my-prayer-time">${formatTimeAgo(p.submitted_at)}</span>
      </div>
      <p class="my-prayer-body">${escHtml(p.body)}</p>
      <div class="my-prayer-item-footer">
        <span class="my-prayer-status ${meta.cls}">${meta.icon} ${meta.label}</span>
        <div class="my-prayer-actions">
          <button class="my-prayer-icon-btn my-prayer-delete-trigger" aria-label="Remove">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
          </button>
        </div>
      </div>
      <div class="my-prayer-confirm-row">
        <span>Remove this request?</span>
        <div class="my-prayer-confirm-actions">
          <button class="my-prayer-confirm-cancel">Cancel</button>
          <button class="my-prayer-confirm-delete" data-delete-id="${escHtml(String(p.id))}">Remove</button>
        </div>
      </div>
    </div>`;
  }).join('');

  listEl.innerHTML = listHead + cards;
}

// Submit a new prayer from the drawer
async function submitDrawerPrayer() {
  const catEl   = document.getElementById('drawerPcat');
  const reqEl   = document.getElementById('drawerPreq');
  const msgEl   = document.getElementById('drawerPrayerMsg');
  const btnEl   = document.getElementById('drawerPrayerSubmitBtn');
  const anonEl  = document.getElementById('drawerAnonToggle');

  const category  = catEl ? catEl.value : 'Healing';
  const body      = reqEl ? reqEl.value.trim() : '';
  const anonymous = anonEl ? anonEl.checked : false;

  // Validation
  msgEl.style.display = 'none';
  msgEl.className     = 'prayer-drawer-form-note';

  if (body.length < 10) {
    msgEl.textContent   = 'Request must be at least 10 characters.';
    msgEl.className    += ' error';
    msgEl.style.display = 'block';
    return;
  }

  btnEl.disabled    = true;
  btnEl.textContent = 'Posting…';

  const memberName = anonymous
    ? 'Anonymous'
    : (window.CURRENT_MEMBER
        ? (window.CURRENT_MEMBER.display_name || window.CURRENT_MEMBER.username || 'Anonymous')
        : 'Anonymous');

  try {
    const res = await apiFetch('/prayers', {
      method: 'POST',
      body: JSON.stringify({ name: memberName, category, body, anonymous }),
    });

    if (res.status === 'success') {
      saveMyPrayer({
        id:           res.data?.id ?? Date.now(),
        category,
        body,
        anonymous,
        status:       'approved',
        submitted_at: new Date().toISOString(),
      });

      reqEl.value = '';
      const counter = document.getElementById('drawerPreqCounter');
      const hint    = document.getElementById('drawerPreqHint');
      if (counter) { counter.textContent = '0/1000'; counter.classList.remove('ok'); }
      if (hint)    { hint.textContent = 'Minimum 10 characters to post.'; hint.classList.remove('ok'); }
      btnEl.disabled = true;
      if (anonEl) anonEl.checked = false;

      renderMyPrayerList();

      // Close the drawer first, then show the shared "Post Submitted" modal
      closePrayerDrawer();
      setTimeout(() => {
        showPendingApprovalModal(
          'Your prayer request has been posted to the wall.<br>Others in the community can now pray alongside you.',
          { title: 'Request Posted!', icon: 'heart-handshake' }
        );
      }, 300);
    } else {
      msgEl.textContent   = res.message || 'Could not post. Try again.';
      msgEl.className     = 'prayer-drawer-form-note error';
      msgEl.style.display = 'block';
    }
  } catch {
    msgEl.textContent   = 'Network error. Please try again.';
    msgEl.className     = 'prayer-drawer-form-note error';
    msgEl.style.display = 'block';
  } finally {
    if (btnEl.disabled && document.getElementById('drawerPreq')?.value.trim().length >= 10) {
      btnEl.disabled = false;
    }
    btnEl.textContent = 'Post to the wall';
  }
}

/** Fetch follow counts for the logged-in member and update sidebar stats. */
async function updateFollowStats() {
  if (!window.CURRENT_MEMBER) return;
  try {
    const res = await apiFetch(`/member/${window.CURRENT_MEMBER.id}/follow`);
    if (res.status === 'success') {
      const followingEl = document.getElementById('leftStatFollowing');
      const followersEl = document.getElementById('leftStatFollowers');
      if (followingEl) followingEl.textContent = String(res.data.following_count ?? 0);
      if (followersEl) followersEl.textContent = String(res.data.follower_count  ?? 0);
    }
  } catch { /* silent */ }
}

// Wire up events — only when the drawer elements exist in DOM
(function initPrayerDrawer() {
  const openBtn   = document.getElementById('openMyPrayersBtn');
  const closeBtn  = document.getElementById('prayerDrawerClose');
  const backdrop  = document.querySelector('#prayerDrawer .prayer-drawer-backdrop');
  const submitBtn = document.getElementById('drawerPrayerSubmitBtn');
  const reqEl     = document.getElementById('drawerPreq');
  const counter   = document.getElementById('drawerPreqCounter');
  const hint      = document.getElementById('drawerPreqHint');

  if (!document.getElementById('prayerDrawer')) return; // only present when member is logged in

  if (openBtn)   openBtn.addEventListener('click', openPrayerDrawer);
  if (closeBtn)  closeBtn.addEventListener('click', closePrayerDrawer);
  if (backdrop)  backdrop.addEventListener('click', closePrayerDrawer);
  if (submitBtn) submitBtn.addEventListener('click', submitDrawerPrayer);

  // Character counter + hint + button enable/disable
  if (reqEl && counter) {
    reqEl.addEventListener('input', () => {
      const len = reqEl.value.length;
      counter.textContent = `${len}/1000`;
      if (len >= 10) {
        counter.classList.add('ok');
        if (hint)    { hint.textContent = 'Looks good.'; hint.classList.add('ok'); }
        if (submitBtn) submitBtn.disabled = false;
      } else {
        counter.classList.remove('ok');
        if (hint)    { hint.textContent = 'Minimum 10 characters to post.'; hint.classList.remove('ok'); }
        if (submitBtn) submitBtn.disabled = true;
      }
      if (len > 900) counter.style.color = '#b91c1c';
      else           counter.style.color = '';
    });
  }

  // Event delegation for confirm-delete in the list
  const listEl = document.getElementById('myPrayerList');
  if (listEl) {
    listEl.addEventListener('click', e => {
      // Trash icon — show confirm row
      const deleteTrigger = e.target.closest('.my-prayer-delete-trigger');
      if (deleteTrigger) {
        const card = deleteTrigger.closest('.my-prayer-item');
        if (card) card.querySelector('.my-prayer-confirm-row')?.classList.add('show');
        return;
      }

      // Cancel — hide confirm row
      const cancelBtn = e.target.closest('.my-prayer-confirm-cancel');
      if (cancelBtn) {
        cancelBtn.closest('.my-prayer-confirm-row')?.classList.remove('show');
        return;
      }

      // Confirm delete
      const deleteBtn = e.target.closest('.my-prayer-confirm-delete');
      if (deleteBtn) {
        const id   = deleteBtn.dataset.deleteId;
        const card = deleteBtn.closest('.my-prayer-item');
        if (card) {
          card.style.transition = 'opacity .25s ease, transform .25s ease';
          card.style.opacity    = '0';
          card.style.transform  = 'translateX(-8px)';
          setTimeout(() => {
            if (id) removeMyPrayer(id);
          }, 220);
        } else if (id) {
          removeMyPrayer(id);
        }
      }
    });
  }

  // Close on Escape
  document.addEventListener('keydown', e => {
    const drawer = document.getElementById('prayerDrawer');
    if (e.key === 'Escape' && drawer && !drawer.hidden) closePrayerDrawer();
  });

  // Seed the left-sidebar follow stats from the API
  updateFollowStats();
})();


// ─── My Profile Drawer ────────────────────────────────────────────────────────

function openProfileDrawer() {
  const drawer = document.getElementById('profileDrawer');
  if (!drawer) return;
  drawer.hidden = false;
  lockScroll();
  loadProfileData();
}

function closeProfileDrawer() {
  const drawer = document.getElementById('profileDrawer');
  if (!drawer || drawer.hidden) return;
  drawer.classList.add('is-closing');
  setTimeout(() => {
    drawer.classList.remove('is-closing');
    drawer.hidden = true;
    unlockScroll();
  }, 280); // matches animation duration
}

async function loadProfileData() {
  try {
    const res = await apiFetch('/member/profile');
    if (res.status !== 'success') return;
    const d = res.data;

    // Avatar
    const initial  = (d.display_name || d.username || '?')[0].toUpperCase();
    const avatarEl = document.getElementById('pdAvatar');
    if (avatarEl) {
      if (d.profile_picture) {
        avatarEl.innerHTML = `<img src="${escHtml(d.profile_picture)}" alt="${escHtml(initial)}" style="width:60px;height:60px;border-radius:50%;object-fit:cover;display:block;">`;
      } else {
        avatarEl.textContent = initial;
      }
    }

    // Hero name / username
    const dnEl = document.getElementById('pdDisplayName');
    if (dnEl) dnEl.textContent = d.display_name || d.username;
    const unEl = document.getElementById('pdUsername');
    if (unEl) unEl.textContent = '@' + (d.username || '');

    // Info rows
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '—'; };
    set('pdEmail',       d.email);
    set('pdUsernameVal', '@' + (d.username || ''));
    set('pdMemberSince', d.member_since);
    set('pdLastLogin',   d.last_login);

    // Pre-fill the edit form
    const nameIn = document.getElementById('pdNameInput');
    const userIn = document.getElementById('pdUsernameInput');
    const mailIn = document.getElementById('pdEmailInput');
    if (nameIn) nameIn.value = d.display_name || '';
    if (userIn) userIn.value = d.username     || '';
    if (mailIn) mailIn.value = d.email        || '';

    // Activity stats — follow counts from API
    const statFollowingEl = document.getElementById('pdStatFollowing');
    const statFollowersEl = document.getElementById('pdStatFollowers');
    if (statFollowingEl) statFollowingEl.textContent = String(d.following_count ?? 0);
    if (statFollowersEl) statFollowersEl.textContent = String(d.follower_count  ?? 0);

  } catch { /* silent */ }
}

/** Score a password and return { level, label } */
function scorePassword(pass) {
  let score = 0;
  if (pass.length >= 8)          score++;
  if (pass.length >= 12)         score++;
  if (/[A-Z]/.test(pass))        score++;
  if (/[0-9]/.test(pass))        score++;
  if (/[^A-Za-z0-9]/.test(pass)) score++;
  if (score <= 1) return { level: 'weak',   label: 'Weak' };
  if (score === 2) return { level: 'fair',   label: 'Fair' };
  if (score === 3) return { level: 'good',   label: 'Good' };
  return              { level: 'strong', label: 'Strong' };
}

async function submitProfileForm(e) {
  e.preventDefault();

  const btn         = document.getElementById('pdSaveBtn');
  const msgEl       = document.getElementById('pdSaveMsg');
  const displayName = (document.getElementById('pdNameInput')?.value   || '').trim();
  const username    = (document.getElementById('pdUsernameInput')?.value || '').trim();
  const email       = (document.getElementById('pdEmailInput')?.value   || '').trim();
  const currentPass = document.getElementById('pdCurrentPass')?.value  || '';
  const newPass     = document.getElementById('pdNewPass')?.value       || '';
  const confirmPass = document.getElementById('pdConfirmPass')?.value   || '';

  // Reset message
  msgEl.style.display = 'none';
  msgEl.className     = 'pd-save-msg';

  // Client-side validation
  if (!displayName) {
    return showPdMsg('Display name cannot be empty.', true);
  }
  if (!username || !/^[a-z0-9_]+$/i.test(username)) {
    return showPdMsg('Username can only contain letters, numbers, and underscores.', true);
  }
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    return showPdMsg('Enter a valid email address.', true);
  }
  if (newPass || confirmPass) {
    if (newPass.length < 8) {
      return showPdMsg('New password must be at least 8 characters.', true);
    }
    if (newPass !== confirmPass) {
      return showPdMsg('New passwords do not match.', true);
    }
  }

  btn.disabled    = true;
  btn.textContent = 'Saving…';

  try {
    const res = await apiFetch('/member/profile/update', {
      method: 'POST',
      body: JSON.stringify({
        display_name:     displayName,
        username,
        email,
        current_password: currentPass,
        new_password:     newPass,
        confirm_password: confirmPass,
      }),
    });

    if (res.status === 'success') {
      const d = res.data;

      // Update hero block
      const dnEl = document.getElementById('pdDisplayName');
      if (dnEl) dnEl.textContent = d.display_name;
      const unEl = document.getElementById('pdUsername');
      if (unEl) unEl.textContent = '@' + d.username;

      // Update info rows
      const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '—'; };
      set('pdEmail',       d.email);
      set('pdUsernameVal', '@' + d.username);

      // Update avatar initial if no image
      const initial   = d.display_name[0].toUpperCase();
      const avatarEl  = document.getElementById('pdAvatar');
      if (avatarEl && !avatarEl.querySelector('img')) avatarEl.textContent = initial;

      // Update sidebar
      const sidebarName = document.querySelector('.sidebar-profile-name');
      if (sidebarName) sidebarName.textContent = d.display_name;
      const sidebarAvatar = document.querySelector('.sidebar-avatar-lg');
      if (sidebarAvatar && !sidebarAvatar.querySelector('img')) sidebarAvatar.textContent = initial;

      // Update nav pill
      const navName = document.querySelector('.nav-member-name');
      if (navName) navName.textContent = d.display_name;
      const navAvatar = document.querySelector('.nav-member-avatar');
      if (navAvatar && !navAvatar.querySelector('img')) navAvatar.textContent = initial;

      // Sync CURRENT_MEMBER
      if (window.CURRENT_MEMBER) {
        window.CURRENT_MEMBER.display_name = d.display_name;
        window.CURRENT_MEMBER.username     = d.username;
        window.CURRENT_MEMBER.email        = d.email;
      }

      // Clear password fields
      ['pdCurrentPass','pdNewPass','pdConfirmPass'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });
      const strengthEl = document.getElementById('pdPassStrength');
      if (strengthEl) strengthEl.hidden = true;

      showPdMsg('✓ Profile updated!', false);
    } else {
      showPdMsg(res.message || 'Could not save. Try again.', true);
      // Focus the relevant field for common errors
      const msg = (res.message || '').toLowerCase();
      if (msg.includes('username')) document.getElementById('pdUsernameInput')?.focus();
      else if (msg.includes('email')) document.getElementById('pdEmailInput')?.focus();
      else if (msg.includes('current password') || msg.includes('incorrect')) document.getElementById('pdCurrentPass')?.focus();
    }
  } catch {
    showPdMsg('Network error. Please try again.', true);
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Save changes';
  }
}

function showPdMsg(text, isError) {
  const msgEl = document.getElementById('pdSaveMsg');
  if (!msgEl) return;
  msgEl.textContent   = text;
  msgEl.className     = 'pd-save-msg ' + (isError ? 'error' : 'success');
  msgEl.style.display = 'block';
  if (!isError) setTimeout(() => { msgEl.style.display = 'none'; }, 4000);
}

// Wire up profile drawer
(function initProfileDrawer() {
  const openBtn  = document.getElementById('openMyProfileBtn');
  const closeBtn = document.getElementById('profileDrawerClose');
  const backdrop = document.querySelector('#profileDrawer .profile-drawer-backdrop');
  const form     = document.getElementById('pdEditForm');

  if (!document.getElementById('profileDrawer')) return; // only present when member is logged in

  if (openBtn)   openBtn.addEventListener('click', openProfileDrawer);
  if (closeBtn)  closeBtn.addEventListener('click', closeProfileDrawer);
  if (backdrop)  backdrop.addEventListener('click', closeProfileDrawer);
  if (form)      form.addEventListener('submit', submitProfileForm);

  // Password collapsible toggle
  const pwToggle = document.getElementById('pdPwToggle');
  const pwFields = document.getElementById('pdPwFields');
  if (pwToggle && pwFields) {
    pwToggle.addEventListener('click', () => {
      pwToggle.classList.toggle('open');
      pwFields.classList.toggle('open');
    });
  }

  // Password strength indicator
  const newPassEl  = document.getElementById('pdNewPass');
  const strengthEl = document.getElementById('pdPassStrength');
  const fillEl     = document.getElementById('pdPassFill');
  const labelEl    = document.getElementById('pdPassLabel');
  if (newPassEl && strengthEl) {
    newPassEl.addEventListener('input', () => {
      const val = newPassEl.value;
      if (!val) { strengthEl.hidden = true; return; }
      const { level, label } = scorePassword(val);
      strengthEl.hidden    = false;
      fillEl.className     = 'pd-pass-fill ' + level;
      labelEl.className    = 'pd-pass-label ' + level;
      labelEl.textContent  = label;
    });
  }

  // Drawer sign-out
  const pdSignOutBtn = document.getElementById('pdSignOutBtn');
  if (pdSignOutBtn) {
    pdSignOutBtn.addEventListener('click', () => {
      closeProfileDrawer();
      const modal = document.getElementById('signOutModal');
      if (modal) {
        modal.hidden = false;
        lockScroll();
        document.getElementById('signOutCancel')?.focus();
      } else {
        document.getElementById('signOutForm')?.submit();
      }
    });
  }

  // Close on Escape
  document.addEventListener('keydown', e => {
    const drawer = document.getElementById('profileDrawer');
    if (e.key === 'Escape' && drawer && !drawer.hidden) closeProfileDrawer();
  });
})();

// ─── Profile Picture Upload ────────────────────────────────────────────────────
(function initAvatarUpload() {
  const input = document.getElementById('pdAvatarInput');
  if (!input) return;

  input.addEventListener('change', async function () {
    const file = this.files[0];
    if (!file) return;

    // Client-side validation
    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) {
      showProfileMsg('Only JPEG, PNG, GIF, or WebP images are allowed.', true);
      this.value = '';
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      showProfileMsg('Image must be 5 MB or smaller.', true);
      this.value = '';
      return;
    }

    const wrap = document.querySelector('.pd-avatar-wrap');
    if (wrap) wrap.classList.add('pd-avatar-uploading');

    const fd = new FormData();
    fd.append('avatar', file);

    try {
      const BASE = (window.APP_BASE_URL || '');
      const res  = await fetch(BASE + '/api/member/avatar', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.status === 'success') {
        const url = data.data.profile_picture;

        // Update drawer avatar
        const avatarEl = document.getElementById('pdAvatar');
        if (avatarEl) {
          const initial = (window.CURRENT_MEMBER
            ? (window.CURRENT_MEMBER.display_name || window.CURRENT_MEMBER.username || '?')[0].toUpperCase()
            : '?');
          avatarEl.innerHTML = `<img src="${url}" alt="${initial}" style="width:60px;height:60px;border-radius:50%;object-fit:cover;display:block;">`;
        }

        // Update sidebar large avatar
        const sidebarAvatar = document.querySelector('.sidebar-avatar-lg');
        if (sidebarAvatar) {
          sidebarAvatar.innerHTML = `<img src="${url}" alt="avatar" style="width:46px;height:46px;border-radius:50%;object-fit:cover;display:block;">`;
        }

        // Update nav pill avatar
        const navAvatar = document.querySelector('.nav-member-avatar');
        if (navAvatar) {
          navAvatar.innerHTML = `<img src="${url}" alt="avatar" style="width:22px;height:22px;border-radius:50%;object-fit:cover;display:block;">`;
        }

        if (window.CURRENT_MEMBER) window.CURRENT_MEMBER.profile_picture = url;

        showProfileMsg('Profile picture updated!', false);
      } else {
        showProfileMsg(data.message || 'Upload failed. Please try again.', true);
      }
    } catch {
      showProfileMsg('Network error. Please try again.', true);
    } finally {
      if (wrap) wrap.classList.remove('pd-avatar-uploading');
      this.value = '';
    }
  });

  function showProfileMsg(msg, isError) {
    const msgEl = document.getElementById('pdSaveMsg');
    if (!msgEl) return;
    msgEl.textContent   = (isError ? '✗ ' : '✓ ') + msg;
    msgEl.className     = 'pd-save-msg ' + (isError ? 'error' : 'success');
    msgEl.style.display = 'block';
    setTimeout(() => { msgEl.style.display = 'none'; }, 3500);
  }
})();


// ═══════════════════════════════════════════════════════════════════════════════
//  MEMBER PROFILE MODAL
// ═══════════════════════════════════════════════════════════════════════════════

(function initMemberProfileModal() {
  const modal = document.getElementById('memberProfileModal');
  if (!modal) return;

  const backdrop = document.getElementById('memberProfileModalBackdrop');
  const closeBtn = document.getElementById('memberProfileModalClose');

  // Close handlers
  function closeMemberProfile() {
    modal.hidden = true;
    unlockScroll();
  }

  closeBtn?.addEventListener('click', closeMemberProfile);
  backdrop?.addEventListener('click', closeMemberProfile);

  // Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.hidden) closeMemberProfile();
  });

  // Intercept member profile links
  document.addEventListener('click', async (e) => {
    const link = e.target.closest('a[href*="/member/"]');
    if (!link) return;

    const href = link.getAttribute('href');

    // Never intercept auth routes — let the browser navigate normally
    if (/\/member\/(login|register|logout)/.test(href)) return;

    const match = href.match(/\/member\/([a-zA-Z0-9_]+)$/);
    if (!match) return;

    e.preventDefault();
    const username = match[1];

    // Open modal and fetch data
    openMemberProfile(username);
  });

  async function openMemberProfile(username) {
    modal.hidden = false;
    lockScroll();

    // Reset content
    document.getElementById('mpmName').textContent = 'Loading...';
    document.getElementById('mpmUsername').textContent = '';
    document.getElementById('mpmSince').textContent = '';
    document.getElementById('mpmAvatar').innerHTML = '?';
    document.getElementById('mpmDisplayName').textContent = '—';
    document.getElementById('mpmUsernameVal').textContent = '—';
    document.getElementById('mpmMemberSince').textContent = '—';
    document.getElementById('mpmFollowingCount').textContent = '—';
    document.getElementById('mpmFollowerCount').textContent = '—';
    const followBtn = document.getElementById('mpmFollowBtn');
    if (followBtn) followBtn.hidden = true;
    const messageBtn = document.getElementById('mpmMessageBtn');
    if (messageBtn) messageBtn.hidden = true;

    try {
      const BASE = window.APP_BASE_URL || '';
      const res = await fetch(`${BASE}/api/member/public/${username}`);
      const data = await res.json();

      if (data.status !== 'success' || !data.data) {
        document.getElementById('mpmName').textContent = 'Member not found';
        return;
      }

      const m = data.data;
      const displayName = m.display_name || m.username;
      const initial = displayName[0].toUpperCase();
      const memberSince = m.created_at ? formatMemberSince(m.created_at) : '—';

      // Update modal content
      document.getElementById('mpmName').textContent = displayName;
      document.getElementById('mpmUsername').textContent = `@${m.username}`;
      document.getElementById('mpmSince').textContent = `Member since ${memberSince}`;
      document.getElementById('mpmDisplayName').textContent = displayName;
      document.getElementById('mpmUsernameVal').textContent = `@${m.username}`;
      document.getElementById('mpmMemberSince').textContent = memberSince;

      // Avatar
      const avatarEl = document.getElementById('mpmAvatar');
      if (m.profile_picture) {
        avatarEl.innerHTML = `<img src="${escHtml(m.profile_picture)}" alt="${escHtml(initial)}">`;
      } else {
        avatarEl.textContent = initial;
      }

      // Follow stats + button (only show button when logged in and viewing someone else)
      const isLoggedIn = !!window.CURRENT_MEMBER;
      const isOwnProfile = isLoggedIn && window.CURRENT_MEMBER.id === m.id;

      const messageBtn = document.getElementById('mpmMessageBtn');

      if (isLoggedIn && !isOwnProfile && followBtn) {
        // Fetch follow status + counts
        try {
          const fRes = await apiFetch(`/member/${m.id}/follow`);
          if (fRes.status === 'success') {
            let isFollowing = fRes.data.following;
            document.getElementById('mpmFollowingCount').textContent = String(fRes.data.following_count ?? 0);
            document.getElementById('mpmFollowerCount').textContent  = String(fRes.data.follower_count  ?? 0);

            followBtn.textContent = isFollowing ? 'Unfollow' : 'Follow';
            followBtn.classList.toggle('mpm-follow-btn--following', isFollowing);
            followBtn.hidden = false;

            // Show message button
            if (messageBtn) messageBtn.hidden = false;

            // Remove old listener by replacing the button node
            const newBtn = followBtn.cloneNode(true);
            followBtn.parentNode.replaceChild(newBtn, followBtn);

            newBtn.addEventListener('click', async () => {
              newBtn.disabled = true;
              try {
                const method = isFollowing ? 'DELETE' : 'POST';
                const tRes   = await apiFetch(`/member/${m.id}/follow`, { method });
                if (tRes.status === 'success') {
                  isFollowing = tRes.data.following;
                  newBtn.textContent = isFollowing ? 'Unfollow' : 'Follow';
                  newBtn.classList.toggle('mpm-follow-btn--following', isFollowing);
                  document.getElementById('mpmFollowerCount').textContent = String(tRes.data.follower_count ?? 0);
                  // Refresh sidebar stat for the logged-in user's following count
                  const followingEl = document.getElementById('leftStatFollowing');
                  if (followingEl) followingEl.textContent = String(tRes.data.following_count ?? 0);

                  if (isFollowing) {
                    // Just followed — fire notification (backend decides follow vs follow_back)
                    apiFetch('/notifications/follow', {
                      method: 'POST',
                      body: JSON.stringify({ recipient_id: m.id }),
                    }).catch(() => {});
                  } else {
                    // Unfollowed — remove the follow notification (fire-and-forget)
                    apiFetch('/notifications/follow', {
                      method: 'DELETE',
                      body: JSON.stringify({ recipient_id: m.id }),
                    }).catch(() => {});
                  }
                }
              } catch { /* silent */ }
              finally { newBtn.disabled = false; }
            });
          }
        } catch { /* follow stats unavailable */ }
      } else {
        // Not logged in or own profile — just show counts without button
        try {
          const fRes = await apiFetch(`/member/${m.id}/follow`);
          if (fRes.status === 'success') {
            document.getElementById('mpmFollowingCount').textContent = String(fRes.data.following_count ?? 0);
            document.getElementById('mpmFollowerCount').textContent  = String(fRes.data.follower_count  ?? 0);
          }
        } catch { /* silent */ }
      }

    } catch (err) {
      console.error('Failed to load member profile:', err);
      document.getElementById('mpmName').textContent = 'Failed to load profile';
    }
  }

  function formatMemberSince(dateStr) {
    const d = new Date(dateStr);
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    return `${months[d.getMonth()]} ${d.getFullYear()}`;
  }

  function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
  
  // Expose globally so notification clicks can call this
  window.openMemberProfile = openMemberProfile;
})();


// ═══════════════════════════════════════════════════════════════════════════
// GROUPED NAV DROPDOWNS (Media / Community / About)
// ═══════════════════════════════════════════════════════════════════════════
(function initNavGroups() {
  const groups = document.querySelectorAll('.nav-group');

  groups.forEach(group => {
    const toggle = group.querySelector('.nav-group-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', function(e) {
      e.stopPropagation();
      const isOpen = group.classList.contains('open');
      // Close all groups first
      groups.forEach(g => { g.classList.remove('open'); g.querySelector('.nav-group-toggle')?.classList.remove('group-open'); });
      // Toggle this one
      if (!isOpen) {
        group.classList.add('open');
        toggle.classList.add('group-open');
      }
    });
  });

  // Close all groups when clicking outside
  document.addEventListener('click', function() {
    groups.forEach(g => { g.classList.remove('open'); g.querySelector('.nav-group-toggle')?.classList.remove('group-open'); });
  });

  // Clicking a dropdown item closes the group
  document.querySelectorAll('.nav-dropdown button[data-page]').forEach(btn => {
    btn.addEventListener('click', function() {
      groups.forEach(g => { g.classList.remove('open'); g.querySelector('.nav-group-toggle')?.classList.remove('group-open'); });
    });
  });
})();


// ═══════════════════════════════════════════════════════════════════════════
//  NOTIFICATION BELL
// ═══════════════════════════════════════════════════════════════════════════
(function initNotificationBell() {
  const bellWrap  = document.getElementById('notifBellWrap');
  const bellBtn   = document.getElementById('notifBellBtn');
  const dropdown  = document.getElementById('notifDropdown');
  const badge     = document.getElementById('notifBadge');
  const listEl    = document.getElementById('notifList');
  const markAllBtn= document.getElementById('notifMarkAllBtn');
  const clearBtn  = document.getElementById('notifClearBtn');

  // Only active when member is logged in
  if (!bellBtn || !window.CURRENT_MEMBER) return;

  let _isOpen    = false;
  let _pollTimer = null;

  // ── Open / Close ──────────────────────────────────────────────────────────
  function openDropdown() {
    _isOpen = true;
    dropdown.removeAttribute('hidden');
    // Force a reflow so the transition fires from the closed state
    void dropdown.offsetWidth;
    dropdown.classList.add('open');
    bellBtn.setAttribute('aria-expanded', 'true');
    loadNotifications();
  }

  function closeDropdown() {
    _isOpen = false;
    dropdown.classList.remove('open');
    bellBtn.setAttribute('aria-expanded', 'false');
  }

  bellBtn.addEventListener('click', e => {
    e.stopPropagation();
    _isOpen ? closeDropdown() : openDropdown();
  });

  // Close when clicking outside
  document.addEventListener('click', e => {
    if (_isOpen && !bellWrap.contains(e.target)) closeDropdown();
  });

  // Close on Escape
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && _isOpen) closeDropdown();
  });

  // ── Mark all read ─────────────────────────────────────────────────────────
  markAllBtn.addEventListener('click', async () => {
    try {
      await apiFetch('/notifications/read-all', { method: 'POST' });
      setBadge(0);
      // Mark all items in the DOM as read
      listEl.querySelectorAll('.notif-item.unread').forEach(el => {
        el.classList.remove('unread');
        el.querySelector('.notif-unread-dot')?.remove();
      });
    } catch { /* silent */ }
  });

  // ── Clear all ─────────────────────────────────────────────────────────────
  clearBtn.addEventListener('click', async () => {
    try {
      // Animate items out with staggered delay, then clear
      const items = [...listEl.querySelectorAll('.notif-item')];
      items.forEach((el, i) => {
        el.style.animationDelay = `${i * 35}ms`;
        el.classList.add('clearing');
      });
      const wait = items.length ? items.length * 35 + 200 : 0;
      await apiFetch('/notifications/clear-all', { method: 'POST' });
      setTimeout(() => {
        setBadge(0);
        listEl.innerHTML = '<div class="notif-empty"><i data-lucide="check-circle" class="notif-empty-icon"></i><span>You\'re all caught up</span></div>';
        lucide.createIcons({ nodes: [listEl] });
      }, wait);
    } catch { /* silent */ }
  });

  // ── Fetch & render ────────────────────────────────────────────────────────
  async function loadNotifications() {
    listEl.innerHTML = '<div class="notif-empty">Loading…</div>';
    try {
      const res = await apiFetch('/notifications');
      if (res.status !== 'success') throw new Error();
      const { notifications, unread } = res.data;
      setBadge(unread);
      renderList(notifications);
    } catch {
      listEl.innerHTML = '<div class="notif-empty">Could not load notifications.</div>';
    }
  }

  function renderList(notifications) {
    if (!notifications || !notifications.length) {
      listEl.innerHTML = '<div class="notif-empty"><i data-lucide="check-circle" class="notif-empty-icon"></i><span>You\'re all caught up</span></div>';
      lucide.createIcons({ nodes: [listEl] });
      return;
    }
    listEl.innerHTML = '';
    notifications.forEach((n, i) => {
      const el = buildItem(n);
      // Stagger each item's animation delay
      el.style.animationDelay = `${i * 40}ms`;
      listEl.appendChild(el);
    });
  }

  function buildItem(n) {
    const wrap = document.createElement('div');
    wrap.className = 'notif-item' + (n.is_read == 0 ? ' unread' : '');

    const name    = n.actor_name || 'Someone';
    const initial = name[0].toUpperCase();
    const pic     = n.actor_picture;

    // Type-specific icon badge overlaid on the avatar — Lucide outline style
    const typeIconMap = {
      like:             { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>', color: '#e53935' },
      comment:          { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>', color: '#1E88E5' },
      share:            { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>', color: '#43A047' },
      comment_like:     { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>', color: '#e53935' },
      comment_reply:    { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>', color: '#8E24AA' },
      follow:           { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>', color: '#00897B' },
      follow_back:      { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>', color: '#F57C00' },
      new_event:        { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>', color: '#039BE5' },
      new_announcement: { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>', color: '#F59E0B' },
      contact_reply:    { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>', color: '#e07b3a' },
    };
    const typeIcon = typeIconMap[n.type] || { svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>', color: '#757575' };
    const isBroadcast = n.type === 'new_event' || n.type === 'new_announcement' || n.type === 'contact_reply';

    // Broadcast notifications (new_event / new_announcement) use the church logo + name
    let avatarHtml;
    if (isBroadcast) {
      const logoSrc = (window.APP_BASE_URL || '') + '/public/images/agape1.jpg';
      avatarHtml = `<div class="notif-avatar-wrap">
           <div class="notif-avatar notif-avatar-system">
             <img src="${logoSrc}" alt="Agape House Ministries">
           </div>
         </div>`;
    } else if (pic) {
      avatarHtml = `<div class="notif-avatar-wrap">
           <div class="notif-avatar"><img src="${_esc(pic)}" alt="${_esc(initial)}"></div>
           <span class="notif-type-icon" style="color:${typeIcon.color}">${typeIcon.svg}</span>
         </div>`;
    } else {
      avatarHtml = `<div class="notif-avatar-wrap">
           <div class="notif-avatar">${_esc(initial)}</div>
           <span class="notif-type-icon" style="color:${typeIcon.color}">${typeIcon.svg}</span>
         </div>`;
    }

    const actionText = {
      like:             'liked your post',
      comment:          'commented on your post',
      share:            'shared your post',
      comment_like:     'liked your comment',
      comment_reply:    'replied to your comment',
      follow:           'started following you',
      follow_back:      'followed you back',
      new_event:        'New Event',
      new_announcement: 'New Announcement',
      contact_reply:    'Admin replied to your message',
    }[n.type] || 'interacted with your post';

    // For post-level actions show post title; for comment actions show the comment snippet
    const isCommentAction   = n.type === 'comment_like' || n.type === 'comment_reply';
    const isFollowAction    = n.type === 'follow' || n.type === 'follow_back';
    const contextLabel      = isCommentAction
      ? `"${_esc(n.target_title)}"`
      : isFollowAction
        ? ''
        : `<em>${_esc(n.target_title)}</em>`;

    const timeStr = typeof formatTimeAgo === 'function' ? formatTimeAgo(n.created_at) : '';

    // Broadcast: "Agape House Ministries" as sender + type label + title
    const mainText = isBroadcast
      ? `<strong>Agape House Ministries</strong> · ${actionText}${contextLabel ? ': ' + contextLabel : ''}`
      : `<strong>${_esc(name)}</strong> ${actionText}${contextLabel ? ': ' + contextLabel : ''}`;

    wrap.innerHTML = `
      ${avatarHtml}
      <div class="notif-text">
        <div class="notif-text-main">${mainText}</div>
        <div class="notif-text-time">${timeStr}</div>
      </div>
      ${n.is_read == 0 ? '<span class="notif-unread-dot"></span>' : ''}
    `;

    // Click marks it read and opens the post (or actor profile for follow notifications)
    wrap.addEventListener('click', async () => {
      if (n.is_read == 0) {
        wrap.classList.remove('unread');
        wrap.querySelector('.notif-unread-dot')?.remove();
        n.is_read = 1;
        // Decrement badge
        const cur = parseInt(badge.textContent, 10) || 0;
        setBadge(Math.max(0, cur - 1));
        apiFetch(`/notifications/${n.id}/read`, { method: 'POST' }).catch(() => {});
      }
      closeDropdown();
      if (n.type === 'follow' || n.type === 'follow_back') {
        // Open the actor's profile modal
        if (n.actor_username) openMemberProfile(n.actor_username);
      } else if (n.type === 'contact_reply') {
        // Open member live chat modal for this contact thread
        openMemberChatModal(n.target_id);
      } else {
        await openNotifTarget(n.target_type, n.target_id);
      }
    });

    return wrap;
  }

  // ── Badge ─────────────────────────────────────────────────────────────────
  let _lastBadgeCount = 0;
  function setBadge(count) {
    if (count > 0) {
      const isNew = count > _lastBadgeCount;
      badge.textContent = count > 99 ? '99+' : String(count);
      badge.hidden = false;
      if (isNew) {
        // Pop badge
        badge.classList.remove('pop');
        void badge.offsetWidth; // reflow
        badge.classList.add('pop');
        badge.addEventListener('animationend', () => badge.classList.remove('pop'), { once: true });
        // Ring the bell
        bellBtn.classList.remove('ringing');
        void bellBtn.offsetWidth;
        bellBtn.classList.add('ringing');
        bellBtn.addEventListener('animationend', () => bellBtn.classList.remove('ringing'), { once: true });
      }
    } else {
      badge.hidden = true;
    }
    _lastBadgeCount = count;
  }

  // ── Poll for unread count every 30 s ─────────────────────────────────────
  async function pollUnread() {
    if (!window.CURRENT_MEMBER) return;
    try {
      const raw = await fetch(BASE_URL + '/notifications/unread-count', {
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
      });
      // Session expired — stop polling and reload so PHP renders the correct auth state
      if (raw.status === 401) {
        clearInterval(_pollTimer);
        _pollTimer = null;
        window.location.href = (window.APP_BASE_URL || '') + '/member/login';
        return;
      }
      const res = await raw.json();
      if (res.status === 'success') setBadge(res.data.unread);
    } catch { /* silent — network error */ }
  }

  // Initial count fetch (lightweight — no full list)
  // Defer slightly so the page session is fully established before the first poll
  setTimeout(pollUnread, 500);
  _pollTimer = setInterval(pollUnread, 30_000);

  // Expose pollUnread globally so notification-sending code can refresh the
  // badge immediately after firing a notification (instead of waiting 30s).
  window._refreshNotifBadge = pollUnread;

  // ── Open the post in the unified Post Detail Modal ───────────────────────
  async function openNotifTarget(targetType, targetId) {
    try {
      let res, data;

      if (targetType === 'article') {
        res = await apiFetch(`/articles/${targetId}`);
        if (res.status !== 'success' || !res.data) return;
        data = res.data;
        openPostDetailModal({
          type:    'article',
          id:      data.id,
          meta:    `${data.read_minutes} min read · ${_fmtDate(data.published_at)}`,
          title:   data.title,
          content: _renderBody(data.body),
        });

      } else if (targetType === 'media') {
        res = await apiFetch(`/media/${targetId}`);
        if (res.status !== 'success' || !res.data) return;
        data = res.data;
        const ytId = _ytId(data.video_url);
        const videoHtml = ytId
          ? `<div class="pdm-video-wrap" id="pdm-yt-wrap-${ytId}">
               <iframe
                 id="pdm-yt-iframe-${ytId}"
                 src="https://www.youtube.com/embed/${ytId}?rel=0&modestbranding=1&enablejsapi=1&origin=${encodeURIComponent(location.origin)}"
                 allow="encrypted-media; fullscreen"
                 title="${_esc(data.title)}"></iframe>
               <div class="pdm-yt-fallback" id="pdm-yt-fallback-${ytId}" hidden>
                 <div class="pdm-yt-fallback-inner">
                   <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="17 2 12 7 7 2"/></svg>
                   <p>This video can't be played here.</p>
                   <a class="pdm-yt-watch-btn" href="https://www.youtube.com/watch?v=${ytId}" target="_blank" rel="noopener noreferrer">
                     <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1C24 15.9 24 12 24 12s0-3.9-.5-5.8zM9.8 15.5V8.5l6.3 3.5-6.3 3.5z"/></svg>
                     Watch on YouTube
                   </a>
                 </div>
               </div>
             </div>
             <div class="pdm-video-meta">${_esc(data.series || data.type || '')} · ${_esc(data.duration_label || '')}</div>`
          : (data.video_url
              ? `<div class="pdm-video-wrap" style="background:#111;display:flex;align-items:center;justify-content:center;">
                   <a class="pdm-yt-watch-btn" href="${_esc(data.video_url)}" target="_blank" rel="noopener noreferrer" style="margin:auto;">
                     <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1C24 15.9 24 12 24 12s0-3.9-.5-5.8zM9.8 15.5V8.5l6.3 3.5-6.3 3.5z"/></svg>
                     Watch on YouTube
                   </a>
                 </div>`
              : `<p style="color:var(--ink-soft)">Video unavailable.</p>`);
        openPostDetailModal({
          type:    'media',
          id:      data.id,
          meta:    (data.series || data.type || '').toUpperCase(),
          title:   data.title,
          content: videoHtml + (data.description ? `<p style="margin-top:12px;font-size:14px;color:var(--ink-soft)">${_esc(data.description)}</p>` : ''),
        });

      } else if (targetType === 'announcement') {
        res = await apiFetch(`/announcements/${targetId}`);
        if (res.status !== 'success' || !res.data) return;
        data = res.data;
        openPostDetailModal({
          type:    'announcement',
          id:      data.id,
          meta:    `${(data.category || 'Announcement').toUpperCase()} · ${_fmtDate(data.published_at || data.created_at)}`,
          title:   data.title,
          content: _renderBody(data.body),
        });

      } else if (targetType === 'event') {
        // Navigate to the Events section so the member can see the event
        navigateTo('events');

      } else if (targetType === 'contact_message') {
        openMemberChatModal(targetId);
      }
    } catch { /* silent — post may be deleted */ }
  }

  // ── Post Detail Modal ─────────────────────────────────────────────────────
  const _pdmModal     = document.getElementById('postDetailModal');
  const _pdmClose     = document.getElementById('pdmClose');
  const _pdmBackdrop  = document.getElementById('pdmBackdrop');
  const _pdmMetaEl    = document.getElementById('pdmMeta');
  const _pdmTitleEl   = document.getElementById('pdmTitle');
  const _pdmContentEl = document.getElementById('pdmContent');
  const _pdmListEl    = document.getElementById('pdmCommentList');
  const _pdmFormWrap  = document.getElementById('pdmCommentFormWrap');

  let _pdmTarget = null;

  function openPostDetailModal({ type, id, meta, title, content }) {
    _pdmMetaEl.textContent    = meta;
    _pdmTitleEl.textContent   = title;
    _pdmContentEl.innerHTML   = content;
    _pdmListEl.innerHTML      = '<div class="comment-loading"><span class="feed-spinner"></span> Loading…</div>';
    _pdmFormWrap.innerHTML    = '';

    _pdmTarget = { type, id, title };

    // Sync into _commentTarget so buildCommentItem's reply form works inside this modal
    _commentTarget = { type, id, title };

    // Clear any leftover closing state so the open animation plays cleanly
    _pdmModal.classList.remove('modal-is-closing');
    _pdmModal.hidden = false;
    lockScroll();

    // Detect YouTube embedding block: if the iframe fails to load properly,
    // swap to the fallback "Watch on YouTube" button after a short timeout.
    const iframe = _pdmContentEl.querySelector('iframe[id^="pdm-yt-iframe-"]');
    if (iframe) {
      const ytId = iframe.id.replace('pdm-yt-iframe-', '');
      const fallback = document.getElementById(`pdm-yt-fallback-${ytId}`);
      if (fallback) {
        // YouTube sends a postMessage when the player errors (e.g. embedding disabled)
        function onYtMsg(e) {
          try {
            const d = typeof e.data === 'string' ? JSON.parse(e.data) : e.data;
            // YouTube playerError codes: 2, 5, 100, 101, 150
            // Code 150/101 = "not playable on embedded players"
            if (d && d.event === 'infoDelivery' && d.info && d.info.playerState === 0) return; // ended — ok
            if (d && (d.event === 'onError' || (d.info && (d.info === 101 || d.info === 150 || d.info === 100)))) {
              showYtFallback(iframe, fallback);
              window.removeEventListener('message', onYtMsg);
            }
          } catch { /* not a YouTube message */ }
        }
        window.addEventListener('message', onYtMsg);

        // Also set a timeout fallback: if the iframe src is still the YouTube embed
        // but we get no positive signal in 4s, show the button (handles silent failures)
        const timer = setTimeout(() => {
          // Check if the iframe loaded correctly by trying to access contentDocument
          try {
            // If same-origin = blocked; cross-origin = YouTube loaded fine = no access = expected
            const doc = iframe.contentDocument;
            // If we can access contentDocument, YouTube was blocked and redirected to a same-origin error page
            if (doc) {
              showYtFallback(iframe, fallback);
              window.removeEventListener('message', onYtMsg);
            }
          } catch { /* cross-origin = YouTube loaded, all good */ }
        }, 4000);

        iframe.addEventListener('load', () => {
          try {
            // Same as above: if we can read the iframe's document, embedding was blocked
            const doc = iframe.contentDocument;
            if (doc) {
              showYtFallback(iframe, fallback);
              window.removeEventListener('message', onYtMsg);
              clearTimeout(timer);
            }
          } catch { /* cross-origin = YouTube loaded normally */ }
        });
      }
    }

    _pdmLoadComments();
    _pdmRenderForm();
  }

  function showYtFallback(iframe, fallback) {
    iframe.hidden = true;
    fallback.hidden = false;
  }

  function _pdmCloseModal() {
    _pdmTarget = null;
    // Stop video audio by resetting iframe src
    const iframe = _pdmContentEl.querySelector('iframe');
    if (iframe) { const s = iframe.src; iframe.src = ''; iframe.src = s; }
    animatedModalClose(_pdmModal, () => {
      unlockScroll();
    });
  }

  _pdmClose.addEventListener('click',    _pdmCloseModal);
  _pdmBackdrop.addEventListener('click', _pdmCloseModal);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !_pdmModal.hidden) _pdmCloseModal();
  });

  async function _pdmLoadComments() {
    if (!_pdmTarget) return;
    try {
      const res = await apiFetch(`/comments/${_pdmTarget.type}/${_pdmTarget.id}`);
      const comments = (res.status === 'success' ? res.data : []) || [];
      if (!comments.length) {
        _pdmListEl.innerHTML = '<p class="comment-empty">No comments yet. Be the first!</p>';
        return;
      }
      _pdmListEl.innerHTML = '';
      comments.forEach(c => _pdmListEl.appendChild(buildCommentItem(c, _pdmListEl)));
      _pdmListEl.scrollTop = _pdmListEl.scrollHeight;
    } catch {
      _pdmListEl.innerHTML = '<p class="comment-empty">Could not load comments.</p>';
    }
  }

  function _pdmBuildComment(c) {
    return buildCommentItem(c, _pdmListEl);
  }

  function _pdmRenderForm() {
    if (!window.CURRENT_MEMBER) {
      _pdmFormWrap.innerHTML = `
        <div class="comment-login-prompt">
          <a href="${window.APP_BASE_URL}/member/login">Sign in</a> to leave a comment.
        </div>`;
      return;
    }

    const name    = window.CURRENT_MEMBER.display_name || window.CURRENT_MEMBER.username || '?';
    const initial = name[0].toUpperCase();
    const pic     = window.CURRENT_MEMBER.profile_picture;

    const avatarHtml = pic
      ? `<div class="comment-avatar comment-avatar--img" style="flex-shrink:0"><img src="${_esc(pic)}" alt="${_esc(initial)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;"></div>`
      : `<div class="comment-avatar" style="flex-shrink:0">${_esc(initial)}</div>`;

    _pdmFormWrap.innerHTML = `
      <div class="comment-form">
        ${avatarHtml}
        <div class="comment-input-wrap">
          <input type="text" class="comment-input" id="pdmCommentInput" placeholder="Write a comment…" autocomplete="off">
          <button class="comment-submit-btn" id="pdmCommentSubmit" aria-label="Send">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
          </button>
        </div>
      </div>`;

    const input  = _pdmFormWrap.querySelector('#pdmCommentInput');
    const submit = _pdmFormWrap.querySelector('#pdmCommentSubmit');

    input.addEventListener('input', () => {
      submit.classList.toggle('active', input.value.trim().length > 0);
    });
    input.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); if (submit.classList.contains('active')) _pdmSubmitComment(); }
    });
    submit.addEventListener('click', () => { if (submit.classList.contains('active')) _pdmSubmitComment(); });
  }

  async function _pdmSubmitComment() {
    if (!_pdmTarget || !window.CURRENT_MEMBER) return;
    const input  = document.getElementById('pdmCommentInput');
    const submit = document.getElementById('pdmCommentSubmit');
    const body   = input.value.trim();
    if (!body) return;

    submit.classList.remove('active');
    submit.style.opacity = '.4';
    submit.style.pointerEvents = 'none';

    try {
      const res = await apiFetch('/comments', {
        method: 'POST',
        body: JSON.stringify({ target_type: _pdmTarget.type, target_id: _pdmTarget.id, body }),
      });
      if (res.status === 'success') {
        input.value = '';
        const empty = _pdmListEl.querySelector('.comment-empty');
        if (empty) empty.remove();
        _pdmListEl.appendChild(buildCommentItem(res.data, _pdmListEl));
        _pdmListEl.scrollTop = _pdmListEl.scrollHeight;
        updateCommentCount(_pdmTarget.type, _pdmTarget.id, 1);
      } else {
        alert(res.message || 'Could not post comment.');
      }
    } catch { alert('Network error. Try again.'); }
    finally {
      submit.style.opacity = '';
      submit.style.pointerEvents = '';
      if (input.value.trim().length > 0) submit.classList.add('active');
    }
  }

  // ── Helpers ───────────────────────────────────────────────────────────────
  function _renderBody(text) {
    if (!text) return '';
    return text.split(/\n{2,}/)
      .map(p => `<p>${_esc(p.trim()).replace(/\n/g, '<br>')}</p>`)
      .filter(p => p !== '<p></p>')
      .join('') || `<p>${_esc(text)}</p>`;
  }

  function _fmtDate(str) {
    if (!str) return '';
    return new Date(str).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
  }

  function _ytId(url) {
    if (!url) return null;
    const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([a-zA-Z0-9_-]{11})/);
    return m ? m[1] : null;
  }

  // ── Tiny escaper (scoped to avoid conflict) ───────────────────────────────
  function _esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  }
})();


// ═══════════════════════════════════════════════════════════════════════════
//  MESSAGES DROPDOWN
// ═══════════════════════════════════════════════════════════════════════════
(function initMessagesDropdown() {
  const msgWrap    = document.getElementById('msgBellWrap');
  const msgBtn     = document.getElementById('msgBellBtn');
  const msgDropdown= document.getElementById('msgDropdown');
  const msgBadge   = document.getElementById('msgBadge');
  const msgList    = document.getElementById('msgList');
  const msgSearchInput = document.getElementById('msgSearchInput');
  const msgPanelTitle = document.getElementById('msgPanelTitle');

  // Only active when member is logged in
  if (!msgBtn || !window.CURRENT_MEMBER) return;

  let _isOpen    = false;
  let _pollTimer = null;
  let _allThreads = []; // Store all threads for search filtering
  let _searchMode = false; // Track if we're in search mode

  // ── Open / Close ──────────────────────────────────────────────────────────
  function openDropdown() {
    _isOpen = true;
    msgDropdown.removeAttribute('hidden');
    void msgDropdown.offsetWidth;
    msgDropdown.classList.add('open');
    msgBtn.setAttribute('aria-expanded', 'true');
    loadMessages();
  }

  function closeDropdown() {
    _isOpen = false;
    msgDropdown.classList.remove('open');
    msgBtn.setAttribute('aria-expanded', 'false');
  }

  msgBtn.addEventListener('click', e => {
    e.stopPropagation();
    _isOpen ? closeDropdown() : openDropdown();
  });

  // Close when clicking outside
  document.addEventListener('click', e => {
    if (_isOpen && !msgWrap.contains(e.target)) closeDropdown();
  });

  // Close on Escape
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && _isOpen) closeDropdown();
  });

  // ── Fetch & render ────────────────────────────────────────────────────────
  async function loadMessages() {
    msgList.innerHTML = '<div class="msg-empty">Loading…</div>';
    try {
      // Fetch both admin threads and direct message conversations
      const [contactRes, dmRes] = await Promise.all([
        apiFetch('/contact/threads'),
        apiFetch('/messages/conversations')
      ]);
      
      const contactThreads = contactRes.status === 'success' ? (contactRes.data || []) : [];
      const dmConversations = dmRes.status === 'success' ? (dmRes.data || []) : [];
      
      // Combine and sort by most recent activity
      const allThreads = [
        ...contactThreads.map(t => ({ ...t, type: 'contact' })),
        ...dmConversations.map(c => ({ ...c, type: 'dm' }))
      ];
      
      // Sort by last activity (most recent first)
      allThreads.sort((a, b) => {
        const aTime = new Date(a.last_message_at || a.last_activity || a.created_at).getTime();
        const bTime = new Date(b.last_message_at || b.last_activity || b.created_at).getTime();
        return bTime - aTime;
      });
      
      _allThreads = allThreads; // Store for search
      renderList(allThreads);
      updateBadge(contactThreads, dmConversations);
    } catch (err) {
      console.error('Failed to load messages:', err);
      msgList.innerHTML = '<div class="msg-empty">Could not load messages.</div>';
    }
  }

  function renderList(threads) {
    if (!threads || !threads.length) {
      msgList.innerHTML = '<div class="msg-empty"><i data-lucide="inbox" class="msg-empty-icon"></i><span>No messages yet</span></div>';
      lucide.createIcons({ nodes: [msgList] });
      return;
    }
    msgList.innerHTML = '';
    threads.forEach((t, i) => {
      const el = buildItem(t);
      el.style.animationDelay = `${i * 40}ms`;
      msgList.appendChild(el);
    });
  }

  // ── Search functionality ──────────────────────────────────────────────────
  let _searchTimeout = null;
  if (msgSearchInput) {
    msgSearchInput.addEventListener('input', e => {
      const query = e.target.value.trim();
      
      // Clear previous timeout
      if (_searchTimeout) {
        clearTimeout(_searchTimeout);
      }
      
      if (!query) {
        // Show all threads when search is empty
        _searchMode = false;
        if (msgPanelTitle) {
          msgPanelTitle.textContent = 'Your Messages';
        }
        renderList(_allThreads);
        lucide.createIcons();
        return;
      }

      // Enter search mode
      _searchMode = true;
      if (msgPanelTitle) {
        msgPanelTitle.textContent = 'Searching...';
      }

      // Debounce search API call
      _searchTimeout = setTimeout(async () => {
        try {
          const res = await apiFetch(`/member/search?q=${encodeURIComponent(query)}`);
          if (res.status !== 'success') {
            console.error('Search error:', res.message || 'Unknown error');
            throw new Error(res.message || 'Search failed');
          }
          
          const members = res.data || [];
          
          // Update title with results count
          if (msgPanelTitle) {
            if (members.length === 0) {
              msgPanelTitle.textContent = 'No members found';
            } else {
              msgPanelTitle.textContent = `Found ${members.length} member${members.length !== 1 ? 's' : ''}`;
            }
          }

          // Render member search results
          if (members.length === 0) {
            msgList.innerHTML = '<div class="msg-empty"><i data-lucide="search-x" class="msg-empty-icon"></i><span>No members match your search</span></div>';
            lucide.createIcons({ nodes: [msgList] });
          } else {
            renderMemberSearchResults(members);
            lucide.createIcons();
          }
        } catch (err) {
          console.error('Member search failed:', err);
          msgList.innerHTML = '<div class="msg-empty">Could not search members.</div>';
        }
      }, 300); // Wait 300ms after user stops typing
    });

    // Clear search when dropdown closes
    msgDropdown.addEventListener('transitionend', () => {
      if (!_isOpen && msgSearchInput) {
        msgSearchInput.value = '';
        _searchMode = false;
        if (msgPanelTitle) {
          msgPanelTitle.textContent = 'Your Messages';
        }
      }
    });
  }

  // ── Render member search results ──────────────────────────────────────────
  function renderMemberSearchResults(members) {
    msgList.innerHTML = '';
    members.forEach((member, i) => {
      const el = buildMemberItem(member);
      el.style.animationDelay = `${i * 40}ms`;
      msgList.appendChild(el);
    });
  }

  function buildMemberItem(member) {
    const wrap = document.createElement('div');
    wrap.className = 'msg-item';

    // Generate avatar or initial
    let avatarContent;
    if (member.profile_picture) {
      avatarContent = `<img src="${member.profile_picture}" alt="${_esc(member.display_name)}" />`;
    } else {
      const initial = (member.display_name || member.username || '?').charAt(0).toUpperCase();
      avatarContent = `<div class="msg-initial">${initial}</div>`;
    }

    const memberSince = member.created_at ? formatTimeAgo(member.created_at) : '';

    wrap.innerHTML = `
      <div class="msg-item-icon">
        ${avatarContent}
      </div>
      <div class="msg-item-content">
        <div class="msg-item-reason">${_esc(member.display_name)}</div>
        <div class="msg-item-preview">@${_esc(member.username)}</div>
        <div class="msg-item-time">Member since ${memberSince}</div>
      </div>
    `;

    wrap.addEventListener('click', async () => {
      closeDropdown();
      // Start a conversation with this member
      try {
        const res = await apiFetch(`/messages/start/${member.id}`, { method: 'POST' });
        if (res.status === 'success' && res.data?.conversation_id) {
          // Open the direct message modal if available
          if (typeof window.openDirectMessageModal === 'function') {
            // Pass both conversation_id and other_member data
            window.openDirectMessageModal(res.data.conversation_id, res.data.other_member);
          } else {
            // Fallback: navigate to messages page if available
            window.location.href = `${window.APP_BASE_URL || ''}/messages`;
          }
        } else {
          console.error('Failed to start conversation:', res);
          alert(res.message || 'Could not start conversation. Please try again.');
        }
      } catch (err) {
        console.error('Failed to start conversation:', err);
        alert('Could not start conversation. Please try again.');
      }
    });

    return wrap;
  }

  function buildItem(thread) {
    const wrap = document.createElement('div');
    
    // Check if it's a direct message or contact thread
    const isDM = thread.type === 'dm';
    const isUnread = isDM ? (thread.unread_count > 0) : (thread.unread_admin_replies > 0);
    
    wrap.className = 'msg-item' + (isUnread ? ' unread' : '');

    let iconHtml, title, preview, timeStr;

    if (isDM) {
      // Direct message from another member
      const otherMember = {
        id: thread.other_member_id,
        display_name: thread.other_member_name,
        username: thread.other_member_username,
        profile_picture: thread.other_member_picture
      };
      
      if (otherMember.profile_picture) {
        iconHtml = `<img src="${otherMember.profile_picture}" alt="${_esc(otherMember.display_name)}" />`;
      } else {
        const initial = (otherMember.display_name || otherMember.username || '?').charAt(0).toUpperCase();
        iconHtml = `<div class="msg-initial">${initial}</div>`;
      }
      
      title = otherMember.display_name || otherMember.username;
      preview = thread.last_message_body ? 
        (thread.last_message_body.length > 80 ? thread.last_message_body.substring(0, 80) + '…' : thread.last_message_body) :
        'No messages yet';
      timeStr = typeof formatTimeAgo === 'function' ? formatTimeAgo(thread.last_message_at || thread.created_at) : '';
      
      // Store other_member for click handler
      thread._otherMember = otherMember;
    } else {
      // Contact/admin thread
      const logoSrc = (window.APP_BASE_URL || '') + '/public/images/agape1.jpg';
      iconHtml = `<img src="${logoSrc}" alt="Agape House" />`;
      title = thread.reason;
      preview = thread.message.length > 80 ? thread.message.substring(0, 80) + '…' : thread.message;
      timeStr = typeof formatTimeAgo === 'function' ? formatTimeAgo(thread.last_activity || thread.created_at) : '';
    }

    wrap.innerHTML = `
      <div class="msg-item-icon">
        ${iconHtml}
      </div>
      <div class="msg-item-content">
        <div class="msg-item-reason">${_esc(title)}</div>
        <div class="msg-item-preview">${_esc(preview)}</div>
        <div class="msg-item-time">${timeStr}</div>
      </div>
      ${isUnread ? '<span class="msg-unread-dot"></span>' : ''}
    `;

    wrap.addEventListener('click', () => {
      closeDropdown();
      
      if (isDM) {
        // Open direct message modal
        if (typeof window.openDirectMessageModal === 'function') {
          window.openDirectMessageModal(thread.id, thread._otherMember);
          // Refresh messages after opening
          setTimeout(loadMessages, 500);
        }
      } else {
        // Open the contact chat modal with this thread
        if (typeof window.openMemberChatModal === 'function') {
          window.openMemberChatModal(thread.id);
          // Refresh badge after opening (user will see replies)
          setTimeout(loadMessages, 500);
        }
      }
    });

    return wrap;
  }

  function updateBadge(contactThreads, dmConversations) {
    const contactUnread = contactThreads.reduce((sum, t) => sum + (t.unread_admin_replies || 0), 0);
    const dmUnread = dmConversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
    const totalUnread = contactUnread + dmUnread;
    setBadge(totalUnread);
  }

  // ── Badge ─────────────────────────────────────────────────────────────────
  let _lastBadgeCount = 0;
  function setBadge(count) {
    if (count > 0) {
      const isNew = count > _lastBadgeCount;
      msgBadge.textContent = count > 99 ? '99+' : String(count);
      msgBadge.hidden = false;
      if (isNew) {
        msgBadge.classList.remove('pop');
        void msgBadge.offsetWidth;
        msgBadge.classList.add('pop');
        msgBadge.addEventListener('animationend', () => msgBadge.classList.remove('pop'), { once: true });
        
        msgBtn.classList.remove('ringing');
        void msgBtn.offsetWidth;
        msgBtn.classList.add('ringing');
        msgBtn.addEventListener('animationend', () => msgBtn.classList.remove('ringing'), { once: true });
      }
    } else {
      msgBadge.hidden = true;
    }
    _lastBadgeCount = count;
  }

  // ── Poll for updates every 30 s ───────────────────────────────────────────
  async function pollThreads() {
    if (!window.CURRENT_MEMBER) return;
    try {
      // Fetch both admin threads and direct message conversations
      const [contactRes, dmRes] = await Promise.all([
        apiFetch('/contact/threads'),
        apiFetch('/messages/conversations')
      ]);
      
      const contactThreads = contactRes.status === 'success' ? (contactRes.data || []) : [];
      const dmConversations = dmRes.status === 'success' ? (dmRes.data || []) : [];
      
      updateBadge(contactThreads, dmConversations);
    } catch { /* silent */ }
  }

  // Load badge count immediately on page load
  pollThreads();
  
  // Then poll every 30 seconds
  _pollTimer = setInterval(pollThreads, 30000);

  // Expose refresh function globally so chat modal can call it after sending a message
  window._refreshMessagesBadge = pollThreads;

  // Helper
  function _esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  }
})();


// ─── Member Live Chat Modal ───────────────────────────────────────────────────
// Opened when a member clicks a contact_reply notification.
// Shows the original message + full reply thread + an input for follow-ups.
(function initMemberChat() {

  // Modal element is injected into layout.php — see views/layout.php
  const modal    = document.getElementById('memberChatModal');
  if (!modal) return;   // only exists when member is logged in

  const backdrop = document.getElementById('memberChatBackdrop');
  const closeBtn = document.getElementById('memberChatClose');
  const titleEl  = document.getElementById('memberChatTitle');
  const metaEl   = document.getElementById('memberChatMeta');
  const origEl   = document.getElementById('memberChatOriginal');
  const threadEl = document.getElementById('memberChatThread');
  const inputEl  = document.getElementById('memberChatInput');
  const sendBtn  = document.getElementById('memberChatSend');
  const msgEl    = document.getElementById('memberChatMsg');

  let _activeId = null;

  function buildBubble(msg) {
    const isAdmin = msg.sender_type === 'admin';
    const name    = isAdmin ? 'Agape House Admin' : 'You';
    const time    = new Date(msg.created_at).toLocaleString('en-US', {
      month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit'
    });
    const logoSrc = (window.APP_BASE_URL || '') + '/public/images/agape1.jpg';
    const avatarHtml = isAdmin
      ? `<img src="${logoSrc}" alt="Admin" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">`
      : '';

    return `
      <div style="display:flex;flex-direction:column;align-items:${isAdmin ? 'flex-start' : 'flex-end'};gap:2px;">
        ${isAdmin ? `<div style="display:flex;align-items:center;gap:6px;">${avatarHtml}<span style="font-size:10px;color:var(--ink-soft);">Agape House</span></div>` : ''}
        <div style="display:inline-block;max-width:70%;background:${isAdmin ? '#fff5ec' : '#CD7642'};
                    color:${isAdmin ? 'var(--ink,#1a1a1a)' : '#fff'};
                    border:${isAdmin ? '1px solid #f0d8c0' : 'none'};
                    border-radius:${isAdmin ? '3px 10px 10px 10px' : '10px 10px 3px 10px'};
                    padding:6px 10px;font-size:12px;line-height:1.4;word-wrap:break-word;overflow-wrap:break-word;">${escHtml(msg.body).trim()}</div>
        <span style="font-size:10px;color:var(--ink-soft);opacity:0.7;">${time}</span>
      </div>`;
  }

  window.openMemberChatModal = async function(contactMessageId) {
    _activeId     = contactMessageId;
    inputEl.value = '';
    msgEl.textContent = '';
    titleEl.textContent = 'Your conversation';
    metaEl.textContent  = '';
    origEl.textContent  = '';
    threadEl.innerHTML  = '<div style="color:var(--ink-soft);font-size:13px;padding:8px 0;">Loading…</div>';

    modal.hidden = false;
    lockScroll();

    try {
      const res  = await apiFetch(`/contact/${contactMessageId}/thread`);
      if (res.status !== 'success') throw new Error();
      const { contact, messages } = res.data;

      metaEl.textContent  = contact.reason;
      origEl.textContent  = contact.message;

      if (messages.length) {
        threadEl.innerHTML = messages.map(buildBubble).join('');
      } else {
        threadEl.innerHTML = '<div style="color:var(--ink-soft);font-size:13px;">No replies yet.</div>';
      }
      threadEl.scrollTop = threadEl.scrollHeight;
    } catch {
      threadEl.innerHTML = '<div style="color:var(--ink-soft);font-size:13px;">Could not load conversation.</div>';
    }
  };

  function closeModal() {
    animatedModalClose(modal, () => {
      unlockScroll();
      _activeId = null;
    });
  }

  closeBtn.addEventListener('click',   closeModal);
  backdrop.addEventListener('click',   closeModal);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });

  sendBtn.addEventListener('click', sendMessage);
  inputEl.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); sendMessage(); }
  });

  async function sendMessage() {
    const body = inputEl.value.trim();
    if (!body || !_activeId) return;

    sendBtn.disabled    = true;
    msgEl.textContent   = '';

    try {
      const res = await apiFetch(`/contact/${_activeId}/message`, {
        method: 'POST',
        body:   JSON.stringify({ body }),
      });
      if (res.status === 'success') {
        const hasPlaceholder = threadEl.querySelector('div[style*="No replies"]');
        if (hasPlaceholder) threadEl.innerHTML = '';
        threadEl.insertAdjacentHTML('beforeend', buildBubble(res.data));
        threadEl.scrollTop = threadEl.scrollHeight;
        inputEl.value       = '';
        msgEl.textContent   = '✓ Sent';
        setTimeout(() => { msgEl.textContent = ''; }, 2000);
      } else {
        msgEl.textContent = res.message || 'Could not send.';
      }
    } catch {
      msgEl.textContent = 'Network error. Try again.';
    } finally {
      sendBtn.disabled    = false;
    }
  }

})();


// ─── Hero Verse Carousel ─────────────────────────────────────────────────────
// Fades verses upward in and out one at a time, cycling indefinitely.
// Starts when the home hero becomes visible (hero-animate class is added).
(function initVerseCarousel() {
  const DISPLAY_MS  = 4000;  // how long each verse stays fully visible
  const EXIT_MS     = 600;   // matches verseOut animation duration

  let _timer   = null;
  let _current = 0;
  let _busy    = false;      // prevent overlap during transition

  function getCarousel() {
    return document.getElementById('heroVerseCarousel');
  }

  function goToVerse(idx) {
    if (_busy) return;
    const carousel = getCarousel();
    if (!carousel) return;

    const items = carousel.querySelectorAll('.hero-verse-item');
    const dots  = carousel.querySelectorAll('.hvd-dot');
    const count = items.length;
    if (!count) return;

    idx = ((idx % count) + count) % count;
    _busy = true;

    const outItem = items[_current];

    // Step 1: play exit animation on current verse
    outItem.classList.remove('hero-verse-item--active');
    outItem.classList.add('hero-verse-item--leaving');

    setTimeout(() => {
      // Step 2: clean up leaving, show next verse
      outItem.classList.remove('hero-verse-item--leaving');
      _current = idx;

      items[_current].classList.add('hero-verse-item--active');

      // Update dots
      dots.forEach((dot, i) => {
        dot.classList.toggle('hvd-dot--active', i === _current);
      });

      _busy = false;
    }, EXIT_MS);
  }

  function advance() {
    goToVerse(_current + 1);
  }

  function startCycle() {
    if (_timer) return;
    _timer = setInterval(advance, DISPLAY_MS + EXIT_MS);
  }

  function stopCycle() {
    if (_timer) {
      clearInterval(_timer);
      _timer = null;
    }
  }

  function resetCarousel() {
    stopCycle();
    _busy    = false;
    _current = 0;
    const carousel = getCarousel();
    if (!carousel) return;
    carousel.querySelectorAll('.hero-verse-item').forEach((item, i) => {
      item.classList.remove('hero-verse-item--active', 'hero-verse-item--leaving');
      if (i === 0) item.classList.add('hero-verse-item--active');
    });
    carousel.querySelectorAll('.hvd-dot').forEach((dot, i) => {
      dot.classList.toggle('hvd-dot--active', i === 0);
    });
  }

  // Watch for hero-animate being added/removed on #page-home
  const pageHome = document.getElementById('page-home');
  if (!pageHome) return;

  const observer = new MutationObserver(() => {
    if (pageHome.classList.contains('hero-animate')) {
      resetCarousel();
      setTimeout(startCycle, 900);
    } else {
      stopCycle();
    }
  });

  observer.observe(pageHome, { attributes: true, attributeFilter: ['class'] });

  // Start immediately if already active on load
  if (pageHome.classList.contains('active') || pageHome.classList.contains('hero-animate')) {
    setTimeout(startCycle, 900);
  }
})();


// ─── Member Direct Messages (Member-to-Member) ────────────────────────────────
/**
 * Opens a direct message conversation with another member.
 * This is called when clicking the "Message" button on a member's profile.
 * Unlike openMemberChatModal (which is for member-to-admin), this opens
 * a member-to-member conversation.
 */
window.openMemberDirectChat = async function(targetMemberId, targetMemberName) {
  // First, start or get the conversation with this member
  try {
    const startRes = await apiFetch(`/messages/start/${targetMemberId}`, {
      method: 'POST',
    });

    if (startRes.status !== 'success') {
      alert(startRes.message || 'Could not start conversation.');
      return;
    }

    const conversationId = startRes.data.conversation_id;
    const otherMember = startRes.data.other_member;

    // Now open a modal showing this conversation
    openDirectMessageModal(conversationId, otherMember);
  } catch (error) {
    console.error('Failed to start direct message:', error);
    alert('Network error. Please try again.');
  }
};

/**
 * Opens the direct message modal UI
 */
function openDirectMessageModal(conversationId, otherMember) {
  console.log('[openDirectMessageModal] Called with:', { conversationId, otherMember });
  
  // Check if we already have a direct message modal in the DOM
  let modal = document.getElementById('directMessageModal');
  console.log('[openDirectMessageModal] Existing modal:', modal ? 'Found' : 'Not found');
  
  if (!modal) {
    // Create the modal on demand
    console.log('[openDirectMessageModal] Creating modal...');
    createDirectMessageModal();
    modal = document.getElementById('directMessageModal');
    console.log('[openDirectMessageModal] Modal created:', modal ? 'Success' : 'Failed');
  }

  // Populate modal with conversation data
  const titleEl = document.getElementById('dmTitle');
  const metaEl = document.getElementById('dmMeta');
  const threadEl = document.getElementById('dmThread');
  const inputEl = document.getElementById('dmInput');
  const sendBtn = document.getElementById('dmSend');
  const closeBtn = document.getElementById('dmClose');
  const backdrop = document.getElementById('dmBackdrop');
  const msgEl = document.getElementById('dmMsg');
  
  if (titleEl) titleEl.textContent = otherMember.display_name || otherMember.username;
  if (metaEl) metaEl.textContent = `@${otherMember.username}`;
  
  // Store conversation ID for later use
  modal.dataset.conversationId = conversationId;
  modal.dataset.otherMemberId = otherMember.id;

  // Load conversation messages
  loadDirectMessages(conversationId, threadEl, otherMember);

  // Show modal
  modal.hidden = false;
  lockScroll();

  // Event handlers
  const sendMessage = async () => {
    const body = inputEl.value.trim();
    if (!body) return;

    sendBtn.disabled = true;
    msgEl.textContent = '';

    try {
      const res = await apiFetch(`/messages/conversation/${conversationId}`, {
        method: 'POST',
        body: JSON.stringify({ body }),
      });

      if (res.status === 'success') {
        // Add message to thread
        threadEl.insertAdjacentHTML('beforeend', buildDirectMessageBubble(res.data, true));
        threadEl.scrollTop = threadEl.scrollHeight;
        inputEl.value = '';
        msgEl.textContent = '✓ Sent';
        setTimeout(() => { msgEl.textContent = ''; }, 2000);
      } else {
        msgEl.textContent = res.message || 'Could not send.';
      }
    } catch {
      msgEl.textContent = 'Network error. Try again.';
    } finally {
      sendBtn.disabled = false;
    }
  };

  const closeModal = () => {
    animatedModalClose(modal, () => {
      unlockScroll();
      // Mark as read when closing
      apiFetch(`/messages/conversation/${conversationId}/read`, { method: 'POST' }).catch(() => {});
    });
  };

  // Wire up handlers (remove old ones first to avoid duplicates)
  sendBtn.replaceWith(sendBtn.cloneNode(true));
  closeBtn.replaceWith(closeBtn.cloneNode(true));
  backdrop.replaceWith(backdrop.cloneNode(true));
  inputEl.replaceWith(inputEl.cloneNode(true));

  // Get fresh references after cloning
  const freshSendBtn = document.getElementById('dmSend');
  const freshCloseBtn = document.getElementById('dmClose');
  const freshBackdrop = document.getElementById('dmBackdrop');
  const freshInputEl = document.getElementById('dmInput');
  
  // Updated sendMessage to use fresh input reference
  const sendMessageFresh = async () => {
    const body = freshInputEl.value.trim();
    if (!body) return;

    freshSendBtn.disabled = true;
    msgEl.textContent = '';

    try {
      const res = await apiFetch(`/messages/conversation/${conversationId}`, {
        method: 'POST',
        body: JSON.stringify({ body }),
      });

      if (res.status === 'success') {
        // Add message to thread
        threadEl.insertAdjacentHTML('beforeend', buildDirectMessageBubble(res.data, true));
        threadEl.scrollTop = threadEl.scrollHeight;
        freshInputEl.value = '';
        msgEl.textContent = '✓ Sent';
        setTimeout(() => { msgEl.textContent = ''; }, 2000);
      } else {
        msgEl.textContent = res.message || 'Could not send.';
      }
    } catch {
      msgEl.textContent = 'Network error. Try again.';
    } finally {
      freshSendBtn.disabled = false;
    }
  };
  
  freshSendBtn.addEventListener('click', sendMessageFresh);
  freshCloseBtn.addEventListener('click', closeModal);
  freshBackdrop.addEventListener('click', closeModal);
  freshInputEl.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
      e.preventDefault();
      sendMessageFresh();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  }, { once: true });
}

/**
 * Load messages for a direct conversation
 */
async function loadDirectMessages(conversationId, threadEl, otherMember) {
  threadEl.innerHTML = '<div style="color:var(--ink-soft);font-size:13px;padding:8px 0;">Loading…</div>';

  try {
    const res = await apiFetch(`/messages/conversation/${conversationId}`);
    
    if (res.status !== 'success') {
      threadEl.innerHTML = '<div style="color:var(--ink-soft);font-size:13px;">Could not load messages.</div>';
      return;
    }

    const messages = res.data.messages || [];
    
    if (messages.length === 0) {
      threadEl.innerHTML = '<div style="color:var(--ink-soft);font-size:13px;">No messages yet. Start the conversation!</div>';
    } else {
      threadEl.innerHTML = messages.map(msg => buildDirectMessageBubble(msg, false)).join('');
      threadEl.scrollTop = threadEl.scrollHeight;
    }
    
    // Mark as read immediately when conversation is opened
    apiFetch(`/messages/conversation/${conversationId}/read`, { method: 'POST' })
      .then(() => {
        // Refresh the messages badge to reflect the updated unread count
        if (typeof window._refreshMessagesBadge === 'function') {
          window._refreshMessagesBadge();
        }
      })
      .catch(() => {});
  } catch {
    threadEl.innerHTML = '<div style="color:var(--ink-soft);font-size:13px;">Network error.</div>';
  }
}

/**
 * Build HTML for a direct message bubble
 */
function buildDirectMessageBubble(msg, isNew = false) {
  // Check if this message is from the current logged-in member
  const currentMember = window.CURRENT_MEMBER || {};
  const isMine = msg.sender_id === (currentMember.id || 0);
  const senderName = msg.sender_name || msg.sender_username || 'Member';
  const initial = senderName.charAt(0).toUpperCase();
  const pic = msg.sender_picture;
  
  const bubbleClass = isMine ? 'dm-bubble-mine' : 'dm-bubble-other';
  const time = new Date(msg.created_at).toLocaleTimeString('en-US', { 
    hour: 'numeric', 
    minute: '2-digit' 
  });

  return `
    <div class="dm-message ${bubbleClass}">
      ${!isMine ? `
        <div class="dm-avatar">
          ${pic ? `<img src="${escHtml(pic)}" alt="${escHtml(initial)}">` : initial}
        </div>
      ` : ''}
      <div class="dm-content">
        <div class="dm-bubble">
          ${!isMine ? `<div class="dm-sender-name">${escHtml(senderName)}</div>` : ''}
          <div class="dm-body">${escHtml(msg.body)}</div>
        </div>
        <div class="dm-time">${time}</div>
      </div>
    </div>
  `;
}

/**
 * Create the direct message modal DOM structure
 */
function createDirectMessageModal() {
  const modal = document.createElement('div');
  modal.id = 'directMessageModal';
  modal.className = 'member-chat-overlay';
  modal.hidden = true;
  modal.setAttribute('role', 'dialog');
  modal.setAttribute('aria-modal', 'true');
  
  modal.innerHTML = `
    <div class="member-chat-backdrop" id="dmBackdrop"></div>
    <div class="member-chat-box">
      <div class="member-chat-header">
        <div style="display:flex;align-items:center;gap:10px;">
          <div>
            <h2 class="member-chat-title" id="dmTitle">Direct Message</h2>
            <p class="member-chat-meta" id="dmMeta"></p>
          </div>
        </div>
        <button class="member-chat-close" id="dmClose" aria-label="Close">✕</button>
      </div>
      <div class="member-chat-thread" id="dmThread"></div>
      <div class="member-chat-footer">
        <div class="member-chat-input-wrap">
          <textarea class="member-chat-input" id="dmInput" 
            rows="2" placeholder="Write a message..." maxlength="3000"></textarea>
          <button class="member-chat-send" id="dmSend" aria-label="Send message">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </div>
        <span class="member-chat-msg" id="dmMsg"></span>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
}


// ─── Auto-open Direct Message on Page Load ───────────────────────────────────
// Check if we should open a direct message modal after redirect from profile page
(function checkPendingDirectMessage() {
  console.log('[DM Auto-open] Initializing...');
  
  // Check URL hash for direct message (e.g., #dm=123)
  const hash = window.location.hash;
  const dmMatch = hash.match(/#dm=(\d+)/);
  
  if (dmMatch) {
    const conversationId = parseInt(dmMatch[1]);
    console.log('[DM Auto-open] Found conversation ID in URL hash:', conversationId);
    
    // Remove the hash
    history.replaceState(null, '', window.location.pathname + '#home');
    
    // Wait for DOM and app init
    window.addEventListener('DOMContentLoaded', () => {
      setTimeout(async () => {
        console.log('[DM Auto-open] Loading conversation from API...');
        try {
          const res = await apiFetch(`/messages/conversation/${conversationId}`);
          if (res.status === 'success' && res.data.other_member) {
            console.log('[DM Auto-open] Opening modal with conversation data');
            openDirectMessageModal(conversationId, res.data.other_member);
          } else {
            console.error('[DM Auto-open] Failed to load conversation:', res);
          }
        } catch (error) {
          console.error('[DM Auto-open] Error loading conversation:', error);
        }
      }, 800);
    });
    return;
  }
  
  // Fallback: Check sessionStorage
  window.addEventListener('DOMContentLoaded', () => {
    console.log('[DM Auto-open] DOM loaded, waiting 800ms for app init...');
    setTimeout(() => {
      console.log('[DM Auto-open] Checking sessionStorage...');
      const pendingMessage = sessionStorage.getItem('openDirectMessage');
      console.log('[DM Auto-open] pendingMessage:', pendingMessage);
      
      if (pendingMessage) {
        try {
          const data = JSON.parse(pendingMessage);
          console.log('[DM Auto-open] Parsed data:', data);
          sessionStorage.removeItem('openDirectMessage');
          console.log('[DM Auto-open] Removed from sessionStorage');
          
          // Open the direct message modal with the conversation data
          if (data.conversationId && data.otherMember) {
            console.log('[DM Auto-open] Opening modal...');
            openDirectMessageModal(data.conversationId, data.otherMember);
          } else {
            console.warn('[DM Auto-open] Invalid data structure:', data);
          }
        } catch (e) {
          console.error('[DM Auto-open] Failed to open pending direct message:', e);
        }
      } else {
        console.log('[DM Auto-open] No pending message found');
      }
    }, 800); // Wait for app initialization
  });
})();
