<?php
/**
 * biblebooks.php: Tables of names and abbreviations of Bible books.
 *
 * @package Sacred Wordpress
 */

$BIBLEBOOKS = array();

$BIBLEBOOKS['BOOKS_OT'] = array('Genesis', 'Exodus', 'Leviticus',
    'Numbers', 'Deuteronomy', 'Joshua',  'Judges', 'Ruth',
    '1 Samuel', '2 Samuel', '1 Kings', '2 Kings',
    '1 Chronicles', '2 Chronicles', 'Ezra', 'Nehemiah',
    'Tobit', 'Judith', 'Esther', '1 Maccabees', '2 Maccabees',
    'Job', 'Psalms',
    # Orthodox have Prayer of Manasseh here
    'Proverbs', 'Ecclesiastes',
    'Song of Songs', 'Wisdom', 'Sirach', 'Isaiah',
    'Jeremiah', 'Lamentations', 'Baruch',
    # Orthodox have Letter of Jeremiah as standalone book here
    'Ezekiel', 'Daniel', 'Hosea', 'Joel', 'Amos', 'Obadiah', 'Jonah',
    'Micah', 'Nahum', 'Habakkuk', 'Zephaniah', 'Haggai', 'Zechariah',
    'Malachi');

$BIBLEBOOKS['BOOKS_NT'] = array('Matthew', 'Mark', 'Luke', 'John',
    'Acts', 'Romans', '1 Corinthians', '2 Corinthians', 'Galatians',
    'Ephesians', 'Philippians', 'Colossians',
    '1 Thessalonians', '2 Thessalonians', '1 Timothy', '2 Timothy',
    'Titus', 'Philemon', 'Hebrews', 'James', '1 Peter', '2 Peter',
    '1 John', '2 John', '3 John', 'Jude', 'Revelation');

# Concerning Ezra, Nehemiah, and 1-4 Esdras
#   * Most modern English Bibles, Protestant, Catholic, and Orthodox,
#     include the canonical books of Ezra and Nehemiah as separate books
#     in the main canon.
#   * In Latin Vulgate and Douay-Rheims, Ezra is called 1 Esdras and
#     Nehemiah is called 2 Esdras.
#   * In Septuagint, 1 Esdras is a different book than either of these,
#     a preface to the canonical Ezra and Nehemiah.
#     This is still called 1 Esdras in Orthodox Bibles and in ecumenical editions.
#     Called 2 Esdras in Slavonic Bibles.
#     Included in appendix of Vulgate as 3 Esdras.
#     Called Esdras A in Lexham English Septuagint (Logos Bible Software).
#   * In Septuagint and Orthodox Bibles, 2 Esdras is the modern
#     Ezra and Nehemiah combined.
#   * 2 Esdras in ecumenical editions is a separate apocalyptic book,
#     the "Apocalayse of Ezra."
#     Called 4 Esdras in Vulgate appendix, 3 Esdras in Slavonic Bibles.
#     Was not included in Septuagint at all.
#     Actually made up of three separate documents, "5 Esdras" (chapters 1-2),
#     "4 Esdras" (chapters 3-14), and "6 Esdras" (chapters 15-16).
#     See intro to 2 Esdras in New Oxford Annotated Bible.

# The Prayer of Azariah and the Song of the Three Jews/Three Holy Children,
#     Susanna (Sus) and Bel and the Dragon are additions to Daniel found in
#     Septuagint and accepted as canonical by Orthodox and Catholic.
# Letter of Jeremiah included in Catholic Bibles as Baruch 6, in
#     Orthodox Bibles as standalone book.
# @todo Need to write some special routines to account for all of this.

# Prayer of Manasseh is in Orthodox Bibles and included in appendix of Vulgate.
# 3 Maccabees is in Orthodox Bibles, also Septuagint.
# 4 Maccabees is in appendix of Orthodox Bibles, also Septuagint.
# Orthodox also include Psalm 151, Odes from Septuagint.

# Some copies of Septuagint also includes Psalms of Solomon (pseudepigraphical),
#     which is not included in any scriptural canon.

$BIBLEBOOKS['ABBREVS'] = array(
    'Genesis'          => ['Gen', 'Gn', 'Ge'],
    'Exodus'           => ['Exod', 'Ex', 'Exo'],
    'Leviticus'        => ['Lev', 'Lv', 'Le'],
    'Numbers'          => ['Num', 'Nm', 'Nu', 'Nb'],
    'Deuteronomy'      => ['Deut', 'Dt'],
    'Joshua'           => ['Josh', 'Jo', 'Jos'],
    'Judges'           => ['Judg', 'Jgs', 'Jdg', 'Jg'],
    'Ruth'             => ['Ru', 'Rth'],
    '1 Samuel'         => ['1 Sam', '1 Sm', '1 Sa'],
    '2 Samuel'         => ['2 Sam', '2 Sm', '2 Sa'],
    '1 Kings'          => ['1 Kgs', '1 Ki'],
    '2 Kings'          => ['2 Kgs', '2 Ki'],
    '1 Chronicles'     => ['1 Chron', '1 Chr', '1 Ch', '1 Par'],
    '2 Chronicles'     => ['2 Chron', '2 Chr', '2 Ch', '2 Par'],
    'Ezra'             => ['Ezr'],
    'Nehemiah'         => ['Neh', 'Ne'],
    'Tobit'            => ['Tob', 'Tb'],
    'Judith'           => ['Jdt'],
    'Esther'           => ['Es', 'Est', 'Esth'],
    '1 Maccabees'      => ['1 Macc', '1 Mc'],
    '2 Maccabees'      => ['2 Macc', '2 Mc'],
    'Job'              => ['Job', 'Jb'],
    'Psalms'           => ['Ps', 'Pss', 'Psalm', 'Psa'],
    'Proverbs'         => ['Prov', 'Prv', 'Pr'],
    'Ecclesiastes'     => ['Eccles', 'Eccl', 'Ec', 'Qoh'],
    'Song of Songs'    => ['Song of Sol', 'Song', 'Sg', 'Cant', 'Canticle', 'Canticles'],
    'Wisdom'           => ['Ws', 'Wis', 'Wisd', 'Wisd of Sol'],
    'Sirach'           => ['Sir', 'Ecclus'],
    'Isaiah'           => ['Isa', 'Is'],
    'Jeremiah'         => ['Jer', 'Je'],
    'Lamentations'     => ['Lam', 'La'],
    'Baruch'           => ['Bar'],
    'Ezekiel'          => ['Ezek', 'Ez', 'Eze', 'Ezk'],
    'Daniel'           => ['Dan', 'Dn', 'Da'],
    'Hosea'            => ['Hos', 'Os', 'Ho'],
    'Joel'             => ['Joel', 'Jl', 'Joe'],
    'Amos'             => ['Am'],
    'Obadiah'          => ['Obad', 'Ob', 'Abd'],
    'Jonah'            => ['Jon'],
    'Micah'            => ['Mic', 'Mi'],
    'Nahum'            => ['Nah', 'Na'],
    'Habakkuk'         => ['Hab', 'Hb'],
    'Zephaniah'        => ['Zeph', 'Zep', 'Soph'],
    'Haggai'           => ['Hag', 'Hg', 'Agg'],
    'Zechariah'        => ['Zech', 'Zec', 'Zac', 'Zach'],
    'Malachi'          => ['Mal'],
    'Matthew'          => ['Matt', 'Mt'],
    'Mark'             => ['Mk'],
    'Luke'             => ['Lk', 'Luk'],
    'John'             => ['Jn', 'Jhn'],
    'Acts'             => ['Ac'],
    'Romans'           => ['Rom', 'Ro'],
    '1 Corinthians'    => ['1 Cor', '1 Co'],
    '2 Corinthians'    => ['2 Cor', '2 Co'],
    'Galatians'        => ['Gal', 'Ga'],
    'Ephesians'        => ['Eph', 'Ephes'],
    'Philippians'      => ['Phil', 'Php'],
    'Colossians'       => ['Col'],
    '1 Thessalonians'  => ['1 Thess', '1 Thes', '1 Th'],
    '2 Thessalonians'  => ['2 Thess', '2 Thes', '2 Th'],
    '1 Timothy'        => ['1 Tim', '1 Tm', '1 Ti'],
    '2 Timothy'        => ['2 Tim', '2 Tm', '2 Ti'],
    'Titus'            => ['Ti', 'Tit'],
    'Philemon'         => ['Philem', 'Phlm', 'Phm'],
    'Hebrews'          => ['Heb'],
    'James'            => ['Jas', 'Jm'],
    '1 Peter'          => ['1 Pet', '1 Pt', '1 Pe'],
    '2 Peter'          => ['2 Pet', '2 Pt', '2 Pe'],
    '1 John'           => ['1 Jn', '1 Jo'],
    '2 John'           => ['2 Jn', '2 Jo'],
    '3 John'           => ['3 Jn', '3 Jo'],
    'Revelation'       => ['Rev', 'Rv', 'Re', 'Apoc'],
);

$BIBLEBOOKS['ALT_NAMES'] = array(
    'Song of Solomon'  => 'Song of Songs',
    'Apocalypse'       => 'Revelation',
    'Josue'            => 'Joshua',
    'Iesous'           => 'Joshua',
    '3 Kings'          => '1 Kings',
    '4 Kings'          => '2 Kings',
    '3 Kingdoms'       => '1 Kings',
    '4 Kingdoms'       => '2 Kings',
    '1 Paralipomenon'  => '1 Chronicles',
    '2 Paralipomenon'  => '2 Chronicles',
    #'1 Esdras'         => 'Ezra',      # In Latin Vulgate these referred to
    #'2 Esdras'         => 'Nehemiah',  # Ezr and Neh, but this is no longer common
    'Nehemias'         => 'Nehemiah',
    'Tobias'           => 'Tobit',
    'Qoholeth'         => 'Ecclesiastes',
    'Canticle of Canticles' => 'Song of Songs',
    'Ecclesiasticus'   => 'Sirach',
    'Isaias'           => 'Isaiah',
    'Jeremias'         => 'Jeremiah',
    'Ezechiel'         => 'Ezekiel',
    'Osee'             => 'Hosea',
    'Abdias'           => 'Obadiah',
    'Jonas'            => 'Jonah',
    'Micheas'          => 'Micah',
    'Habacuc'          => 'Habakkuk',
    'Sophonias'        => 'Zephaniah',
    'Aggeus'           => 'Haggai',
    'Zacharias'        => 'Zechariah',
    'Zachariah'        => 'Zechariah',
    'Malachias'        => 'Malachi',
    '1 Machabees'      => '1 Maccabees',
    '2 Machabees'      => '2 Maccabees',
    'Acts of the Apostles' => 'Acts',
);

/* These are the standard abbreviations that, as of 2014/08/24, the Bibles.org API uses.
 * They aren't currently being used for anything here. */
/* $BIBLEBOOKS['CANONICAL_ABBREVS'] = array(
    'Genesis'          => 'Gen',
    'Exodus'           => 'Exod',
    'Leviticus'        => 'Lev',
    'Numbers'          => 'Num',
    'Deuteronomy'      => 'Deut',
    'Joshua'           => 'Josh',
    'Judges'           => 'Judg',
    'Ruth'             => 'Ruth',
    '1 Samuel'         => '1Sam',
    '2 Samuel'         => '2Sam',
    '1 Kings'          => '1Kgs',
    '2 Kings'          => '2Kgs',
    '1 Chronicles'     => '1Chr',
    '2 Chronicles'     => '2Chr',
    'Ezra'             => 'Ezra',
    'Nehemiah'         => 'Neh',
    'Tobit'            => 'Tob',
    'Judith'           => 'Jdt',
    'Esther'           => 'Esth',
    '1 Maccabees'      => '1Macc',
    '2 Maccabees'      => '2Macc',
    'Job'              => 'Job',
    'Psalms'           => 'Ps',
    'Proverbs'         => 'Prov',
    'Ecclesiastes'     => 'Eccl',
    'Song of Songs'    => 'Song',
    'Wisdom'           => 'Wis',
    'Sirach'           => 'Sir',
    'Isaiah'           => 'Isa',
    'Jeremiah'         => 'Jer',
    'Lamentations'     => 'Lam',
    'Baruch'           => 'Bar',
    'Ezekiel'          => 'Ezek',
    'Daniel'           => 'Dan',
    'Hosea'            => 'Hos',
    'Joel'             => 'Joel',
    'Amos'             => 'Amos',
    'Obadiah'          => 'Obad',
    'Jonah'            => 'Jonah',
    'Micah'            => 'Mic',
    'Nahum'            => 'Nah',
    'Habakkuk'         => 'Hab',
    'Zephaniah'        => 'Zeph',
    'Haggai'           => 'Hag',
    'Zechariah'        => 'Zech',
    'Malachi'          => 'Mal',
    'Matthew'          => 'Matt',
    'Mark'             => 'Mark',
    'Luke'             => 'Luke',
    'John'             => 'John',
    'Acts'             => 'Acts',
    'Romans'           => 'Rom',
    '1 Corinthians'    => '1Cor',
    '2 Corinthians'    => '2Cor',
    'Galatians'        => 'Gal',
    'Ephesians'        => 'Eph',
    'Philippians'      => 'Phil',
    'Colossians'       => 'Col',
    '1 Thessalonians'  => '1Thess',
    '2 Thessalonians'  => '2Thess',
    '1 Timothy'        => '1Tim',
    '2 Timothy'        => '2Tim',
    'Titus'            => 'Titus',
    'Philemon'         => 'Phlm',
    'Hebrews'          => 'Heb',
    'James'            => 'Jas',
    '1 Peter'          => '1Pet',
    '2 Peter'          => '2Pet',
    '1 John'           => '1John',
    '2 John'           => '2John',
    '3 John'           => '3John',
    'Jude'             => 'Jude',
    'Revelation'       => 'Rev',
); */

/**
 * How many chapters are in each biblical book, for use, first of all,
 * in determining which books are a single chapter. I also thought it
 * could be useful in some cases (possibly along with [CHAPTER_VERSES]
 * below) in resolving ambiguous references between different books which
 * sometimes share the same name -- e.g. By "1 Kings 25:1," did the
 * reference actually mean 1 Kings, or 1 Samuel (called 1 Kings in the
 * Vulgate and other translations proceeding from the Septuagint, the
 * books called 1 and 2 Kings in modern editions being called 3 and 4 Kings).
 * It happens that the modern 1 Kings has only 22 chapters, therefore
 * 1 Kings 25:1 probably refers to 1 Samuel 25:1.
 */
$BIBLEBOOKS['CHAPTERS'] = array(
    'Genesis'          => 50,
    'Exodus'           => 40,
    'Leviticus'        => 27,
    'Numbers'          => 36,
    'Deuteronomy'      => 34,
    'Joshua'           => 24,
    'Judges'           => 21,
    'Ruth'             => 4,
    '1 Samuel'         => 31,
    '2 Samuel'         => 24,
    '1 Kings'          => 22,
    '2 Kings'          => 25,
    '1 Chronicles'     => 29,
    '2 Chronicles'     => 36,
    'Ezra'             => 10,
    'Nehemiah'         => 13,
    'Tobit'            => 14,
    'Judith'           => 16,
    'Esther'           => 10, # 10 in Greek
    '1 Maccabees'      => 16,
    '2 Maccabees'      => 15,
    'Job'              => 42,
    'Psalms'           => 150,
    'Proverbs'         => 31,
    'Ecclesiastes'     => 12,
    'Song of Songs'    => 8,
    'Wisdom'           => 19,
    'Sirach'           => 51,
    'Isaiah'           => 66,
    'Jeremiah'         => 52,
    'Lamentations'     => 5,
    'Baruch'           => 5, # Letter of Jeremiah is Baruch 6 in Catholic Bibles
    'Ezekiel'          => 48,
    'Daniel'           => 12,
    'Hosea'            => 14,
    'Joel'             => 3,
    'Amos'             => 9,
    'Obadiah'          => 1,
    'Jonah'            => 4,
    'Micah'            => 7,
    'Nahum'            => 3,
    'Habakkuk'         => 3,
    'Zephaniah'        => 3,
    'Haggai'           => 2,
    'Zechariah'        => 14,
    'Malachi'          => 4,
    '1 Esdras'         => 9,  # Septuagint 1 Esdras, Vulgate 3 Esdras
    '2 Esdras'         => 16, # Vulgate 4 Esdras
    'Prayer of Manasseh' => 1,
    'Matthew'          => 28,
    'Mark'             => 16,
    'Luke'             => 24,
    'John'             => 21,
    'Acts'             => 28,
    'Romans'           => 16,
    '1 Corinthians'    => 16,
    '2 Corinthians'    => 13,
    'Galatians'        => 6,
    'Ephesians'        => 6,
    'Philippians'      => 4,
    'Colossians'       => 4,
    '1 Thessalonians'  => 5,
    '2 Thessalonians'  => 3,
    '1 Timothy'        => 6,
    '2 Timothy'        => 4,
    'Titus'            => 3,
    'Philemon'         => 1,
    'Hebrews'          => 13,
    'James'            => 5,
    '1 Peter'          => 5,
    '2 Peter'          => 3,
    '1 John'           => 5,
    '2 John'           => 1,
    '3 John'           => 1,
    'Jude'             => 1,
    'Revelation'       => 22,
);

/**
 * The number of verses in each chapter of each book, according to the ESV.
 * This seems to be fairly consistent across modern versions. This can used,
 * like [CHAPTERS], to resolve ambiguous references.
 * Also it is used to guess how many verses we are looking for when the
 * user requests a whole chapter, since the Bibles.org API returns whole
 * chapter requests (e.g. "1 Corinthians 13") in the form of
 * "1 Corinthians 13:1-13".
 */
$BIBLEBOOKS['CHAPTER_VERSES'] = array(
    'Genesis' => [31, 25, 24, 26, 32, 22, 24, 22, 29, 32, 32, 20, 18, 24, 21, 16, 27, 33, 38, 18, 34, 24, 20, 67, 34, 35, 46, 22, 35, 43, 55, 32, 20, 31, 29, 43, 36, 30, 23, 23, 57, 38, 34, 34, 28, 34, 31, 22, 33, 26],
    'Exodus' => [22, 25, 22, 31, 23, 30, 25, 32, 35, 29, 10, 51, 22, 31, 27, 36, 16, 27, 25, 26, 36, 31, 33, 18, 40, 37, 21, 43, 46, 38, 18, 35, 23, 35, 35, 38, 29, 31, 43, 38],
    'Leviticus' => [17, 16, 17, 35, 19, 30, 38, 36, 24, 20, 47, 8, 59, 57, 33, 34, 16, 30, 37, 27, 24, 33, 44, 23, 55, 46, 34],
    'Numbers' => [54, 34, 51, 49, 31, 27, 89, 26, 23, 36, 35, 16, 33, 45, 41, 50, 13, 32, 22, 29, 35, 41, 30, 25, 18, 65, 23, 31, 40, 16, 54, 42, 56, 29, 34, 13],
    'Deuteronomy' => [46, 37, 29, 49, 33, 25, 26, 20, 29, 22, 32, 32, 18, 29, 23, 22, 20, 22, 21, 20, 23, 30, 25, 22, 19, 19, 26, 68, 29, 20, 30, 52, 29, 12],
    'Joshua' => [18, 24, 17, 24, 15, 27, 26, 35, 27, 43, 23, 24, 33, 15, 63, 10, 18, 28, 51, 9, 45, 34, 16, 33],
    'Judges' => [36, 23, 31, 24, 31, 40, 25, 35, 57, 18, 40, 15, 25, 20, 20, 31, 13, 31, 30, 48, 25],
    'Ruth' => [22, 23, 18, 22],
    '1 Samuel' => [28, 36, 21, 22, 12, 21, 17, 22, 27, 27, 15, 25, 23, 52, 35, 23, 58, 30, 24, 42, 15, 23, 29, 22, 44, 25, 12, 25, 11, 31, 13],
    '2 Samuel' => [27, 32, 39, 12, 25, 23, 29, 18, 13, 19, 27, 31, 39, 33, 37, 23, 29, 33, 43, 26, 22, 51, 39, 25],
    '1 Kings' => [53, 46, 28, 34, 18, 38, 51, 66, 28, 29, 43, 33, 34, 31, 34, 34, 24, 46, 21, 43, 29, 53],
    '2 Kings' => [18, 25, 27, 44, 27, 33, 20, 29, 37, 36, 21, 21, 25, 29, 38, 20, 41, 37, 37, 21, 26, 20, 37, 20, 30],
    '1 Chronicles' => [54, 55, 24, 43, 26, 81, 40, 40, 44, 14, 47, 40, 14, 17, 29, 43, 27, 17, 19, 8, 30, 19, 32, 31, 31, 32, 34, 21, 30],
    '2 Chronicles' => [17, 18, 17, 22, 14, 42, 22, 18, 31, 19, 23, 16, 22, 15, 19, 14, 19, 34, 11, 37, 20, 12, 21, 27, 28, 23, 9, 27, 36, 27, 21, 33, 25, 33, 27, 23],
    'Ezra' => [11, 70, 13, 24, 17, 22, 28, 36, 15, 44],
    'Nehemiah' => [11, 20, 32, 23, 19, 19, 73, 18, 38, 39, 36, 47, 31],
    'Tobit' => [22, 14, 17, 21, 22, 17, 18, 21, 6, 12, 19, 22, 18, 15], # from KJVA
    'Judith' => [16, 28, 10, 15, 24, 21, 32, 36, 14, 23, 23, 20, 20, 19, 13, 25], # from KJVA
    'Esther' => [22, 23, 15, 17, 14, 14, 10, 17, 32, 3],
    '1 Maccabees' => [64, 70, 60, 61, 68, 63, 50, 32, 73, 89, 74, 53, 53, 49, 41, 24], # from KJVA
    '2 Maccabees' => [36, 32, 40, 50, 27, 31, 42, 36, 29, 38, 38, 45, 26, 46, 39], # from KJVA
    'Job' => [22, 13, 26, 21, 27, 30, 21, 22, 35, 22, 20, 25, 28, 22, 35, 22, 16, 21, 29, 29, 34, 30, 17, 25, 6, 14, 23, 28, 25, 31, 40, 22, 33, 37, 16, 33, 24, 41, 30, 24, 34, 17],
    'Psalms' => [6, 12, 8, 8, 12, 10, 17, 9, 20, 18, 7, 8, 6, 7, 5, 11, 15, 50, 14, 9, 13, 31, 6, 10, 22, 12, 14, 9, 11, 12, 24, 11, 22, 22, 28, 12, 40, 22, 13, 17, 13, 11, 5, 26, 17, 11, 9, 14, 20, 23, 19, 9, 6, 7, 23, 13, 11, 11, 17, 12, 8, 12, 11, 10, 13, 20, 7, 35, 36, 5, 24, 20, 28, 23, 10, 12, 20, 72, 13, 19, 16, 8, 18, 12, 13, 17, 7, 18, 52, 17, 16, 15, 5, 23, 11, 13, 12, 9, 9, 5, 8, 28, 22, 35, 45, 48, 43, 13, 31, 7, 10, 10, 9, 8, 18, 19, 2, 29, 176, 7, 8, 9, 4, 8, 5, 6, 5, 6, 8, 8, 3, 18, 3, 3, 21, 26, 9, 8, 24, 13, 10, 7, 12, 15, 21, 10, 20, 14, 9, 6],
    'Proverbs' => [33, 22, 35, 27, 23, 35, 27, 36, 18, 32, 31, 28, 25, 35, 33, 33, 28, 24, 29, 30, 31, 29, 35, 34, 28, 28, 27, 28, 27, 33, 31],
    'Ecclesiastes' => [18, 26, 22, 16, 20, 12, 29, 17, 18, 20, 10, 14],
    'Song of Songs' => [17, 17, 11, 16, 16, 13, 13, 14],
    'Wisdom' => [16, 24, 19, 20, 23, 25, 30, 21, 18, 21, 26, 27, 19, 31, 19, 29, 21, 25, 22], # from KJVA
    'Sirach' => [30, 18, 31, 31, 15, 37, 36, 19, 18, 31, 34, 18, 26, 27, 20, 30, 32, 33, 30, 32, 28, 27, 28, 34, 26, 29, 30, 26, 28, 25, 31, 24, 31, 26, 20, 26, 31, 34, 35, 30, 24, 25, 33, 23, 26, 20, 25, 25, 16, 29, 30], # from KJVA
    'Isaiah' => [31, 22, 26, 6, 30, 13, 25, 22, 21, 34, 16, 6, 22, 32, 9, 14, 14, 7, 25, 6, 17, 25, 18, 23, 12, 21, 13, 29, 24, 33, 9, 20, 24, 17, 10, 22, 38, 22, 8, 31, 29, 25, 28, 28, 25, 13, 15, 22, 26, 11, 23, 15, 12, 17, 13, 12, 21, 14, 21, 22, 11, 12, 19, 12, 25, 24],
    'Jeremiah' => [19, 37, 25, 31, 31, 30, 34, 22, 26, 25, 23, 17, 27, 22, 21, 21, 27, 23, 15, 18, 14, 30, 40, 10, 38, 24, 22, 17, 32, 24, 40, 44, 26, 22, 19, 32, 21, 28, 18, 16, 18, 22, 13, 30, 5, 28, 7, 47, 39, 46, 64, 34],
    'Lamentations' => [22, 22, 66, 22, 22],
    'Baruch' => [22, 35, 37, 37, 9, 73], # from KJVA
    'Ezekiel' => [28, 10, 27, 17, 17, 14, 27, 18, 11, 22, 25, 28, 23, 23, 8, 63, 24, 32, 14, 49, 32, 31, 49, 27, 17, 21, 36, 26, 21, 26, 18, 32, 33, 31, 15, 38, 28, 23, 29, 49, 26, 20, 27, 31, 25, 24, 23, 35],
    'Daniel' => [21, 49, 30, 37, 31, 28, 28, 27, 27, 21, 45, 13],
    'Hosea' => [11, 23, 5, 19, 15, 11, 16, 14, 17, 15, 12, 14, 16, 9],
    'Joel' => [20, 32, 21],
    'Amos' => [15, 16, 15, 13, 27, 14, 17, 14, 15],
    'Obadiah' => [21],
    'Jonah' => [17, 10, 10, 11],
    'Micah' => [16, 13, 12, 13, 15, 16, 20],
    'Nahum' => [15, 13, 19],
    'Habakkuk' => [17, 20, 19],
    'Zephaniah' => [18, 15, 20],
    'Haggai' => [15, 23],
    'Zechariah' => [21, 13, 10, 14, 11, 15, 14, 23, 17, 12, 17, 14, 9, 21],
    'Malachi' => [14, 17, 18, 6],
    'Matthew' => [25, 23, 17, 25, 48, 34, 29, 34, 38, 42, 30, 50, 58, 36, 39, 28, 27, 35, 30, 34, 46, 46, 39, 51, 46, 75, 66, 20],
    'Mark' => [45, 28, 35, 41, 43, 56, 37, 38, 50, 52, 33, 44, 37, 72, 47, 20],
    'Luke' => [80, 52, 38, 44, 39, 49, 50, 56, 62, 42, 54, 59, 35, 35, 32, 31, 37, 43, 48, 47, 38, 71, 56, 53],
    'John' => [51, 25, 36, 54, 47, 71, 53, 59, 41, 42, 57, 50, 38, 31, 27, 33, 26, 40, 42, 31, 25],
    'Acts' => [26, 47, 26, 37, 42, 15, 60, 40, 43, 48, 30, 25, 52, 28, 41, 40, 34, 28, 41, 38, 40, 30, 35, 27, 27, 32, 44, 31],
    'Romans' => [32, 29, 31, 25, 21, 23, 25, 39, 33, 21, 36, 21, 14, 23, 33, 27],
    '1 Corinthians' => [31, 16, 23, 21, 13, 20, 40, 13, 27, 33, 34, 31, 13, 40, 58, 24],
    '2 Corinthians' => [24, 17, 18, 18, 21, 18, 16, 24, 15, 18, 33, 21, 14],
    'Galatians' => [24, 21, 29, 31, 26, 18],
    'Ephesians' => [23, 22, 21, 32, 33, 24],
    'Philippians' => [30, 30, 21, 23],
    'Colossians' => [29, 23, 25, 18],
    '1 Thessalonians' => [10, 20, 13, 18, 28],
    '2 Thessalonians' => [12, 17, 18],
    '1 Timothy' => [20, 15, 16, 16, 25, 21],
    '2 Timothy' => [18, 26, 17, 22],
    'Titus' => [16, 15, 15],
    'Philemon' => [25],
    'Hebrews' => [14, 18, 19, 16, 14, 20, 28, 13, 28, 39, 40, 29, 25],
    'James' => [27, 26, 18, 17, 20],
    '1 Peter' => [25, 25, 22, 19, 14],
    '2 Peter' => [21, 22, 18],
    '1 John' => [10, 29, 24, 21, 21],
    '2 John' => [13],
    '3 John' => [15],
    'Jude' => [25],
    'Revelation' => [20, 29, 22, 11, 14, 17, 17, 13, 21, 11, 19, 17, 18, 20, 8, 21, 18, 24, 21, 15, 27, 21]
);

/**
 * What books are deuterocanonical (and therefore not in most Protestant
 * editions of Scripture)?
 */
$BIBLEBOOKS['DEUTERO'] = array(
    'Tobit'            => 1,
    'Judith'           => 1,
    '1 Maccabees'      => 1,
    '2 Maccabees'      => 1,
    'Wisdom'           => 1,
    'Sirach'           => 1,
    'Baruch'           => 1,

    '1 Esdras'         => 1,
    '2 Esdras'         => 1,
    'Prayer of Manasseh' => 1,
);

class BibleBooks {

    /**
     * Functions to map this all to a large [BOOK] array, so that any
     * permutation of a scriptural reference will match to something
     * in the one array
     */
    static private function map_books(array $books) {
        global $BIBLEBOOKS;
        foreach ($books as $book) {
            $BIBLEBOOKS['BOOKS'][$book] = $book;
        }
    }

    static private function map_alt(array $books) {
        global $BIBLEBOOKS;
        foreach ($books as $alt => $book) {
            $BIBLEBOOKS['BOOKS'][$alt] = $book;
        }
    }

    static private function map_abbrevs(array $all_abbrevs)  {
        global $BIBLEBOOKS;
        foreach ($all_abbrevs as $book => $abbrevs) {
            foreach ($abbrevs as $abbrev) {
                $BIBLEBOOKS['BOOKS'][$abbrev] = $book;
            }
        }
    }

    /**
     * Function to trigger all of the above.
     */
    static public function prepare_book_maps() {
        global $BIBLEBOOKS;
        $BIBLEBOOOKS['BOOKS'] = array();
        self::map_books($BIBLEBOOKS['BOOKS_OT']);
        self::map_books($BIBLEBOOKS['BOOKS_NT']);
        self::map_alt($BIBLEBOOKS['ALT_NAMES']);
        self::map_abbrevs($BIBLEBOOKS['ABBREVS']);
    }
}
