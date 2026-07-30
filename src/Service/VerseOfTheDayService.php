<?php
/**
 * VerseOfTheDayService
 * Single source of truth for the daily rotating verse.
 * Used by home.php (PHP render) and bible.php (injected into JS).
 */
declare(strict_types=1);

namespace Service;

class VerseOfTheDayService
{
    private static array $verses = [
        ["text" => "For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.", "ref" => "John 3:16"],
        ["text" => "I can do all this through him who gives me strength.", "ref" => "Philippians 4:13"],
        ["text" => "The Lord is my shepherd, I lack nothing.", "ref" => "Psalm 23:1"],
        ["text" => "Trust in the Lord with all your heart and lean not on your own understanding.", "ref" => "Proverbs 3:5"],
        ["text" => "Be strong and courageous. Do not be afraid; do not be discouraged, for the Lord your God will be with you wherever you go.", "ref" => "Joshua 1:9"],
        ["text" => "And we know that in all things God works for the good of those who love him, who have been called according to his purpose.", "ref" => "Romans 8:28"],
        ["text" => "The people walking in darkness have seen a great light; on those living in the land of deep darkness a light has dawned.", "ref" => "Isaiah 9:2"],
        ["text" => "Come to me, all you who are weary and burdened, and I will give you rest.", "ref" => "Matthew 11:28"],
        ["text" => "But those who hope in the Lord will renew their strength. They will soar on wings like eagles; they will run and not grow weary, they will walk and not be faint.", "ref" => "Isaiah 40:31"],
        ["text" => "Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God.", "ref" => "Philippians 4:6"],
        ["text" => "The Lord your God is with you, the Mighty Warrior who saves. He will take great delight in you; in his love he will no longer rebuke you, but will rejoice over you with singing.", "ref" => "Zephaniah 3:17"],
        ["text" => "This is the day the Lord has made; let us rejoice and be glad in it.", "ref" => "Psalm 118:24"],
        ["text" => "For I know the plans I have for you, declares the Lord, plans to prosper you and not to harm you, plans to give you hope and a future.", "ref" => "Jeremiah 29:11"],
        ["text" => "Cast all your anxiety on him because he cares for you.", "ref" => "1 Peter 5:7"],
        ["text" => "The Lord is my light and my salvation — whom shall I fear? The Lord is the stronghold of my life — of whom shall I be afraid?", "ref" => "Psalm 27:1"],
        ["text" => "Delight yourself in the Lord, and he will give you the desires of your heart.", "ref" => "Psalm 37:4"],
        ["text" => "Love the Lord your God with all your heart and with all your soul and with all your mind.", "ref" => "Matthew 22:37"],
        ["text" => "In the beginning was the Word, and the Word was with God, and the Word was God.", "ref" => "John 1:1"],
        ["text" => "The grass withers and the flowers fall, but the word of our God endures forever.", "ref" => "Isaiah 40:8"],
        ["text" => "Be still, and know that I am God.", "ref" => "Psalm 46:10"],
        ["text" => "No temptation has overtaken you except what is common to mankind. And God is faithful; he will not let you be tempted beyond what you can bear.", "ref" => "1 Corinthians 10:13"],
        ["text" => "For the word of God is alive and active. Sharper than any double-edged sword.", "ref" => "Hebrews 4:12"],
        ["text" => "Jesus answered, I am the way and the truth and the life. No one comes to the Father except through me.", "ref" => "John 14:6"],
        ["text" => "The Lord bless you and keep you; the Lord make his face shine on you and be gracious to you.", "ref" => "Numbers 6:24-25"],
        ["text" => "Blessed are the pure in heart, for they will see God.", "ref" => "Matthew 5:8"],
        ["text" => "For it is by grace you have been saved, through faith — and this is not from yourselves, it is the gift of God.", "ref" => "Ephesians 2:8"],
        ["text" => "Your word is a lamp for my feet, a light on my path.", "ref" => "Psalm 119:105"],
        ["text" => "Create in me a pure heart, O God, and renew a steadfast spirit within me.", "ref" => "Psalm 51:10"],
        ["text" => "But seek first his kingdom and his righteousness, and all these things will be given to you as well.", "ref" => "Matthew 6:33"],
        ["text" => "I am the vine; you are the branches. If you remain in me and I in you, you will bear much fruit.", "ref" => "John 15:5"],
        ["text" => "Now faith is confidence in what we hope for and assurance about what we do not see.", "ref" => "Hebrews 11:1"],
        ["text" => "Let your light shine before others, that they may see your good deeds and glorify your Father in heaven.", "ref" => "Matthew 5:16"],
        ["text" => "Peace I leave with you; my peace I give you. I do not give to you as the world gives.", "ref" => "John 14:27"],
        ["text" => "Give thanks to the Lord, for he is good; his love endures forever.", "ref" => "Psalm 107:1"],
        ["text" => "Rejoice in the Lord always. I will say it again: Rejoice!", "ref" => "Philippians 4:4"],
        ["text" => "The name of the Lord is a fortified tower; the righteous run to it and are safe.", "ref" => "Proverbs 18:10"],
        ["text" => "And my God will meet all your needs according to the riches of his glory in Christ Jesus.", "ref" => "Philippians 4:19"],
        ["text" => "For where two or three gather in my name, there am I with them.", "ref" => "Matthew 18:20"],
        ["text" => "Greater love has no one than this: to lay down one's life for one's friends.", "ref" => "John 15:13"],
        ["text" => "The Lord is close to the brokenhearted and saves those who are crushed in spirit.", "ref" => "Psalm 34:18"],
        ["text" => "He gives strength to the weary and increases the power of the weak.", "ref" => "Isaiah 40:29"],
        ["text" => "Do not conform to the pattern of this world, but be transformed by the renewing of your mind.", "ref" => "Romans 12:2"],
        ["text" => "God is our refuge and strength, an ever-present help in trouble.", "ref" => "Psalm 46:1"],
        ["text" => "The Lord is good, a refuge in times of trouble. He cares for those who trust in him.", "ref" => "Nahum 1:7"],
        ["text" => "Love is patient, love is kind. It does not envy, it does not boast, it is not proud.", "ref" => "1 Corinthians 13:4"],
        ["text" => "For nothing will be impossible with God.", "ref" => "Luke 1:37"],
        ["text" => "Ask and it will be given to you; seek and you will find; knock and the door will be opened to you.", "ref" => "Matthew 7:7"],
        ["text" => "The steadfast love of the Lord never ceases; his mercies never come to an end; they are new every morning.", "ref" => "Lamentations 3:22-23"],
        ["text" => "I have been crucified with Christ and I no longer live, but Christ lives in me.", "ref" => "Galatians 2:20"],
        ["text" => "Therefore, if anyone is in Christ, the new creation has come: the old has gone, the new is here!", "ref" => "2 Corinthians 5:17"],
        ["text" => "But the fruit of the Spirit is love, joy, peace, forbearance, kindness, goodness, faithfulness, gentleness and self-control.", "ref" => "Galatians 5:22-23"],
        ["text" => "I lift up my eyes to the mountains — where does my help come from? My help comes from the Lord, the Maker of heaven and earth.", "ref" => "Psalm 121:1-2"],
        ["text" => "For God has not given us a spirit of fear, but of power and of love and of a sound mind.", "ref" => "2 Timothy 1:7"],
        ["text" => "Blessed is the one who trusts in the Lord, whose confidence is in him.", "ref" => "Jeremiah 17:7"],
        ["text" => "He who began a good work in you will carry it on to completion until the day of Christ Jesus.", "ref" => "Philippians 1:6"],
        ["text" => "Let us not become weary in doing good, for at the proper time we will reap a harvest if we do not give up.", "ref" => "Galatians 6:9"],
        ["text" => "The Lord will fight for you; you need only to be still.", "ref" => "Exodus 14:14"],
        ["text" => "Taste and see that the Lord is good; blessed is the one who takes refuge in him.", "ref" => "Psalm 34:8"],
        ["text" => "My grace is sufficient for you, for my power is made perfect in weakness.", "ref" => "2 Corinthians 12:9"],
        ["text" => "Let the morning bring me word of your unfailing love, for I have put my trust in you.", "ref" => "Psalm 143:8"],
        ["text" => "With man this is impossible, but with God all things are possible.", "ref" => "Matthew 19:26"],
        ["text" => "I praise you because I am fearfully and wonderfully made; your works are wonderful, I know that full well.", "ref" => "Psalm 139:14"],
        ["text" => "Even though I walk through the darkest valley, I will fear no evil, for you are with me.", "ref" => "Psalm 23:4"],
        ["text" => "Whoever believes in me, as Scripture has said, rivers of living water will flow from within them.", "ref" => "John 7:38"],
        ["text" => "The Lord is my strength and my song; he has given me victory.", "ref" => "Exodus 15:2"],
        ["text" => "Jesus Christ is the same yesterday and today and forever.", "ref" => "Hebrews 13:8"],
    ];

    /**
     * Returns today's verse based on the day of the year.
     * Same index calculation every time, so home and bible always match.
     */
    public static function getToday(): array
    {
        $dayOfYear = (int) date('z'); // 0–364
        return self::$verses[$dayOfYear % count(self::$verses)];
    }

    /**
     * Returns today's verse as a JSON string for embedding in JS.
     */
    public static function getTodayJson(): string
    {
        return json_encode(self::getToday(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
