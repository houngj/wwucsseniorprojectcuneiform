DROP SCHEMA IF EXISTS `cuneiform`;

CREATE SCHEMA `cuneiform`
	DEFAULT CHARACTER SET latin1;

USE `cuneiform`;

/* Create the dingo user, if it doesn't exist*/

GRANT ALL ON `cuneiform`.* TO 'dingo'@'%'  IDENTIFIED BY 'hungry!';

/* Create tables. */

CREATE TABLE `tablet`
(
	`tablet_id`
		INT
		PRIMARY KEY
		NOT NULL AUTO_INCREMENT,
	`name`
		VARCHAR(100)
		NOT NULL
		DEFAULT '',
	`lang`
		VARCHAR(16)
		NOT NULL
		DEFAULT 'sux',		-- Default: sumerian (sux)
	FULLTEXT KEY (`name`)
);

CREATE TABLE `tablet_object`
(
	`tablet_object_id`
		INT
		PRIMARY KEY
		NOT NULL
		AUTO_INCREMENT,
	`tablet_id`
		INT,
	`obj_name`
		VARCHAR(100)
		NOT NULL,

	FOREIGN KEY (`tablet_id`)
		REFERENCES `tablet` (`tablet_id`)
);

CREATE TABLE `text_section_type`
(
	`text_section_type_id`
		INT
		PRIMARY KEY
		NOT NULL,
	`name`
		VARCHAR(32)
		NOT NULL
);

INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES ( 1, 'Bottom');   -- @bottom
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES ( 2, 'Bulla');    -- @bulla
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES ( 3, 'Edge');     -- @edge
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES ( 4, 'Envelope'); -- @envelope
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES ( 5, 'Left');     -- @left
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES ( 6, 'Object');   -- @object
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES ( 7, 'Obverse');  -- @obverse
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES ( 8, 'Reverse');  -- @reverse
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES ( 9, 'Seal');     -- @seal
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES (10, 'Tablet');   -- @tablet
INSERT INTO `text_section_type` (`text_section_type_id`, `name`) VALUES (11, 'Top');      -- @top

CREATE TABLE `text_section`
(
	`text_section_id`
		INT
		PRIMARY KEY
		NOT NULL
		AUTO_INCREMENT,
	`tablet_object_id`
		INT
		NOT NULL,

/*
	There's a lot of garbage in the @ comments that indicate the source
	of the text:
		cat ur3_20140114_public.atf | grep ^@ | sort | uniq
	We shouldn't support them all, so for now we'll just allow `textsectiontype_id`
	to be NULL if it's from a source we don't care to implement yet.
*/

	`text_section_type_id`
		INT
		NULL,

/*
	There are some pretty long tablet texts in the Ur III source file, and in MySQL
	the longest VARCHAR field is something like 21805 bytes.  That's not enough
	so I've used a TEXT field here.  This isn't a great idea, since TEXT fields
	aren't memory resident and queries on it will hit the physical disk and slow
	us down significantly.
*/

	`section_text`
		TEXT
		NOT NULL
		DEFAULT '',

	FOREIGN KEY (`tablet_object_id`)
		REFERENCES `tablet_object` (`tablet_object_id`),
	FOREIGN KEY (`text_section_type_id`)
		REFERENCES `text_section_type` (`text_section_type_id`),
	FULLTEXT KEY (`section_text`)
);

CREATE TABLE `line`
(
	`line_id`
		INT
		PRIMARY KEY
		NOT NULL
		AUTO_INCREMENT,
	`text_section_id`
		INT
		NOT NULL,
	`text`
		NVARCHAR(270),			-- Longest line in ur3 source file is 270 chars.
	`translation`
		NVARCHAR(256)
		NULL
		DEFAULT '',
	`comment`
		NVARCHAR(270)
		NULL
		DEFAULT '',

	FOREIGN KEY (`text_section_id`)
		REFERENCES `text_section` (`text_section_id`),
	FULLTEXT KEY (`text`)
);

CREATE TABLE `canonical_month`
(
	`canonical_month_id`
		INT
		PRIMARY KEY
		NOT NULL
		AUTO_INCREMENT,
	`text`
		NVARCHAR(255)
		NOT NULL,
	`month_number`
		INT
		DEFAULT NULL,		-- There are months whose number we don't know.
	`polity`
		NVARCHAR(255)
		NOT NULL,
	`comment`
		NVARCHAR(255)
		DEFAULT NULL
);

INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('GAN2-masz', 1, 'Girsu');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('bara2-za3-gar', 1, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('sze-sag11-ku5', 1, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('sze-sag-ku5', 1, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('gu4-ra2-bi2-mu2-mu2', 2, 'Girsu');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('gu4-si-su', 2, 'Nippur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('sig4-geszi3-szub-ba-gar', 2, 'Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('masz-da3-gu7', 2, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ezem-{d}li9-si4', 3, 'Girsu');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('sig4-ga', 3, 'Nippur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('sze-kar-ra-gal2-la', 3, 'Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ses-da-gu7', 3, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('szesz-da-gu7', 3, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('nesag2', 4, 'Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('u5-bi2-gu7', 4, 'Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('munu4-gu7', 5, 'Girsu');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('NE-NE-gar', 5, 'Nippur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('RI-dal', 5, 'Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ki-sikil-{d}nin-a-zu', 5, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ezem-{d}dumu-zi', 6, 'Girsu');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('kin-{d}inanna', 6, 'Nippur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ezem-{d}nin-a-zu', 6, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('du6-ku3', 7, 'Nippur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`, `comment`)
	VALUES ('ezem-{d}amar-{d}suen', 7, 'Umma', 'from AS7 to SzS2');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('min-esz3', 7, 'Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('a2-ki-ti', 7, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ezem-{d}ba-ba6', 8, 'Girsu');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('apin-du8-a', 8, 'Nippur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('e2-iti6', 8, 'Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ezem-{d}szul-gi', 8, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('mu-szu-du7', 9, 'Girsu');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('GAN-GAN-e3', 9, 'Nippur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('szu-esz-sza', 9, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('amar-a-a-si', 10, 'Girsu');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('AB-e3', 10, 'Nippur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ezem-mah', 10, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ziz2-a', 11, 'Nippur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('pa4-u2-e', 11, 'Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ezem-an-na', 11, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`, `comment`)
	VALUES ('diri ezem-me-ki-gal2', 12, 'Drehem', 'SzS3');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('sze-il2-la', 12, 'Girsu');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('{d}dumu-zi', 12, 'Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('ezem-me-ki-gal2', 12, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`)
	VALUES ('diri', 13, 'Ur');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`, `comment`)
	VALUES ('ezem-{d}szu-{d}suen', NULL, 'Drehem', 'month 8 in SzS3, month 9 from SzS3 to IS8');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`, `comment`)
	VALUES ('szu-numun', NULL, 'Girsu, Nippur, Umma', 'month 4 in Girsu and Nippur, month 6 in Umma');
INSERT INTO `canonical_month` (`text`, `month_number`, `polity`, `comment`)
	VALUES ('UR', NULL, 'Girst, Umma', 'month 7 in Girsu, month 10 in Umma before Sz30');

CREATE TABLE `canonical_year`
(
	`canonical_year_id`
		INT
		PRIMARY KEY
		NOT NULL AUTO_INCREMENT,
	`text`
		NVARCHAR(255)
		NOT NULL,
	`abbreviation`
		NVARCHAR(32)
		NOT NULL
);

INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('ur-{d}nammu lugal', 'Ur-Nammu 1');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('ur-{d}nammu lugal-e sig-ta igi-nim-sze3 gir3 si bi2-sa2-a', 'Ur-Nammu 2');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('ur-{d}nammu ni3-si-sa2 kalam-ma mu-ni-gar', 'Ur-Nammu 3');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-{d}inanna unug{ki}-a dumu ur-{d}nammu lugal-a masz-e ba-pad3-da', 'Ur-Nammu 4');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('bad3 ur2-i{ki} ba-du3-a', 'Ur-Nammu 5');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('lugal-e nibru{ki}-ta nam-lugal szu ba-ti-a', 'Ur-Nammu 6');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('e2-{d}nanna ba-du3-a', 'Ur-Nammu 7');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-{d}nanna masz-e ba-pad3-da', 'Ur-Nammu 8');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('id2-a-{d}nin-tu ba-al', 'Ur-Nammu 9');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('nin-dingir-{d}iszkur masz-e pad3-da', 'Ur-Nammu 10');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('gu-ti-um{ki} ba-hul', 'Ur-Nammu 11');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('e2-{d}nin-sun2 ur2-i{ki}-a ba-du3-a', 'Ur-Nammu 12');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('e2-{d}en-lil2-la2 ba-du3-a', 'Ur-Nammu 13');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('id2-en-erin2-nun ba-ba-al-la', 'Ur-Nammu 14');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{gisz}gigir {d}nin-lil2 ba-dim2-ma', 'Ur-Nammu 15');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}lugal-ba-gara2 e2-a-na ku4-ra', 'Ur-Nammu 16');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}lugal-ba-gara2 e2-a ku4-ra us2-sa', 'Ur-Nammu 17');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('szul-gi lugal', 'Szulgi 1');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('usz e2-{d}nin-gublaga ki ba-a-gar', 'Szulgi 2');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('szul-gi lugal ur2i{ki}-ma-ke4 {gisz}gu-za za-gin3 {d}en-lil2-ra i-na-ku4-ra', 'Szulgi 3');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('usz e2-{d}nin-urta ki ba-a-gar', 'Szulgi 4');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('bad3 gal e2-an-na ba-du3-a', 'Szulgi 5');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('lugal-e gir3 nibru{ki} si bi2-sa2-a', 'Szulgi 6');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('lugal-e ur2i{ki}-ta nibru{ki}-sze3 szu in-nigin2', 'Szulgi 7');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('ma2 ma2-gur8 {d}nin-lil2-la2 ba-ab-du8', 'Szulgi 8');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('ma2 {d}nin-lil2-la2-ke4 us2-sa', 'Szulgi 9*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nanna kar-zi-da{ki} e2-a-ni ku4', 'Szulgi 9');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('e2-hur-sag lugal ba-du3', 'Szulgi 10');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}isztaran bad3-an{ki} der{ki} e2-a-na ba-ku4', 'Szulgi 11');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nu-musz-da ka-zal-lu{ki} e2-a-na ba-ku4', 'Szulgi 12');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('e2-hal-bi lugal ba-du3', 'Szulgi 13');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nanna nibru{ki} e2-a ba-ku4', 'Szulgi 14');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-nir-si2-an-na en-{d}nanna masz2-e i3-pad3', 'Szulgi 15');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{gisz}na2 {d}nin-lil2-la2 ba-dim2', 'Szulgi 16');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{gisz}na2 {d}nin-lil2-la2 us2-sa', 'Szulgi 17*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-nir-si2-an-na en-{d}nanna ba-hun-ga2', 'Szulgi 17');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('li2-wir-mi-ta2-szu dumu-munus lugal nam-nin mar-ha-szi{ki} ba-il2', 'Szulgi 18');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('ezenXku3{ki} bad3{ki} ki-be2 ba-ab-gi4', 'Szulgi 19');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nin-hur-sag e2-nu-tur e2-a-na ba-an-ku4', 'Szulgi 20a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('dumu ur2i{ki}-ma lu2 {gisz}gid2-sze3 ka ba-ab-keszda2', 'Szulgi 20b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nin-urta ensi2 gal {d}en-lil2-la2-ke4 esz-bar kin ba-an-du11-ga a-sza3 ni3-ka9 {d}en-lil2 {d}nin-lil2-ra si bi2-in-sa2-sa2-a', 'Szulgi 21a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nin-urta ensi2 gal {d}en-lil2-la2-ke4 e2-{d}en-lil2 {d}nin-lil2-la2-ke4 esz-bar kin ba-an-du11-ga {d}szul-gi lugal ur2i{ki}-ma-ke4 gan2 ni3-ka9 sza3 e2 {d}en-lil2 {d}nin-lil2-la2-ke4 si bi2-sa2-a', 'Szulgi 21b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('bad3-an{ki} der{ki} ba-hul', 'Szulgi 21c');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}nin-urta ensi2 gal {d}en-lil2-la2-ke4 e2-{d}en-lil2 {d}nin-lil2-la2-ke4 esz-bar kin ba-an-du11-ga {d}szul-gi lugal ur2i{ki}-ma-ke4 gan2 ni3-ka9 sza3 e2 {d}en-lil2 {d}nin-lil2-la2-ke4 si bi2-sa2-a', 'Szulgi 22a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa bad3-an{ki} / der{ki} ba-hul', 'Szulgi 22b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa ni3-ka9-ak al-la-ka mu us2-sa-bi', 'Szulgi 23*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szul-gi lugal-e a2 mah {d}en-lil2 sum-ma-ni...', 'Szulgi 23');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('kara2-har{ki} ba-hul', 'Szulgi 24');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('-sa kara2-har{ki} ba-hul', 'Szulgi 25*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('si-mu-ru-um{ki} ba-hul', 'Szulgi 25');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa si-mu-ru-um{ki} ba-hul', 'Szulgi 26*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('si-mu-ru-um{ki} a-ra2 2-kam-ma-asz ba-hul', 'Szulgi 26');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('szul-gi nita kalag-ga lugal an ub-da limmu2-ba-ke4 si-mu-ur4-um{ki} a-ra2 2-kam-asz mu-hul-a mu us2-sa-bi', 'Szulgi 27*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('ha-ar-szi{ki} ba-hul', 'Szulgi 27');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en nam-szita4 {d}szul-gi-ra-ke4 ba-gub-ba-sze3 szud3-sag en-{d}en-ki eridu{ki}-ga dumu szul-gi nita kalag-ga lugal ur2i{ki}-ma lugal an ub-da 4-ba-ke4 ba-a-hun', 'Szulgi 28a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-nam-szita4 {d}szul-gi-ra-ke4 ba-gub en-{d}en-ki eridu{ki}-ga dumu {d}szul-gi nita kalag-ga lugal ur2i{ki}-ma lugal an ub-da limmu2-ba-ka ba-a-hun', 'Szulgi 28b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa en nam-szita4 {d}szul-gi-ra-ke4 ba-gub-ba-sze3 szud3-sag en-{d}en-ki eridu{ki}-ga dumu szul-gi nita kalag-ga lugal ur2i{ki}-ma lugal an ub-da 4-ba-ke4 ba-a-hun', 'Szulgi 29');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('dumu-munus lugal ensi2 an-sza-an{ki}-ke4 ba-an-tuk', 'Szulgi 30a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('dumu-munus lugal ensi2 an-sza-an{ki}-ke4 ba-an-du', 'Szulgi 30b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa dumu-munus lugal ensi2 an-sza-an{ki}-ke4 ba-an-tuk', 'Szulgi 31*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('kara2-har{ki} a-ra2 2-kam-asz ba-hul', 'Szulgi 31');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('si-mu-ru-um{ki} a-ra2 3-kam-asz ba-hul', 'Szulgi 32');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa si-mu-ru-um{ki} a-ra2 3-kam-asz ba-hul', 'Szulgi 33*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('kara2-har{ki} a-ra2 3-kam-asz ba-hul', 'Szulgi 33');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa kara2-har{ki} a-ra2 3-kam-asz ba-hul', 'Szulgi 34*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa a-ra2 3-kam-asz si-mu-ru-um{ki} ba-hul mu us2-sa-bi', 'Szulgi 34**');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('an-sza-an{ki} ba-hul', 'Szulgi 34');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa an-sza-an{ki} ba-hul', 'Szulgi 35');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa an-sza-an{ki} ba-hul mu us2-sa-bi', 'Szulgi 36*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nanna ga-esz{ki} e2-ba-a ba-ku4', 'Szulgi 36');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nanna kar-zi-da{ki} a-ra2 2-kam-asz e2-a-na ba-an-ku4', 'Szulgi 36 (Drehem)');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nanna kar-zi-da{ki} e2-a-na ba-an-ku4', 'Szulgi 36 (Lagash)');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nanna kar-zi-da{ki} a-ra2 2-kam-ma-sze3 e2-a-na ba-an-ku4', 'Szulgi 36 (Nippur)');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nanna kar-zi-da{ki} a-ra2 2-kam e2-a-na ba-an-ku4', 'Szulgi 36 (Umma)');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nanna kar-zi-da{ki} e2-nun-na-sze3 agrun-na-sze3', 'Szulgi 36 (Ur)');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}nanna kar-zi-da{ki} a-ra2 2-kam e2-a-na ba-an-ku4', 'Szulgi 37*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}nanna u3 {d}szul-gi lugal-e bad3 ma-da mu-du3', 'Szulgi 37a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('bad3 ma-da ba-du3', 'Szulgi 37b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa bad3 ma-da ba-du3', 'Szulgi 38');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa bad3 ma-da ba-du3 mu us2-sa-bi', 'Szulgi 39*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szul-gi lugal ur2i{ki}-ma-ke4 lugal an ub-da 4-ba-ke4 e2-puzur4-isz-{d}da-gan{ki} e2-{d}szul-gi-ra mu-du3', 'Szulgi 39');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa e2-puzur4-isz-{d}da-gan{ki} ba-du3-a', 'Szulgi 40');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa e2-puzur4-isz-{d}da-gan{ki} ba-du3-a mu us2-sa-a-bi', 'Szulgi 41');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa e2-puzur4-isz-{d}da-gan{ki} ba-du3-a mu us2-sa-a-ba mu us2-sa-a-bi', 'Szulgi 42*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('lugal-e sza-asz-ru-um{ki} mu-hul', 'Szulgi 42');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa sza-asz-ru-um{ki} ba-hul', 'Szulgi 43*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-ubur-zi-an-na en-{d}nanna masz-e / masz2-e i3-pad3', 'Szulgi 43');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa en-{d}nanna masz-e / masz2-e i3-pad3', 'Szulgi 44*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('si-mu-ru-um{ki} u3 lu-lu-bu-um{ki} a-ra2 10-la2-1-kam-asz ba-hul', 'Szulgi 44');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('si-mu-ru-um{ki} u3 lu-lu-bum2{ki} a-ra2 10-la2-1-kam-asz ba-hul', 'Szulgi 44');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa si-mu-ru-um{ki} u3 lu-lu-bu-um{ki} a-ra2 9-kam-asz ba-hul', 'Szulgi 45*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szul-gi nita kalag-ga lugal ur2i{ki}-ma lugal an ub-da limmu2-ba-ke4 ur-bi2-lum{ki} ar-bi2-lum{ki} si-mu-ru-um{ki} lu-lu-bu{ki} u3 kara2-har{ki} 1-sze3 asz-sze3 sag-du-bi szu-bur2-a bi2-ra-a im-mi-ra', 'Szulgi 45a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('si-mu-ru-um{ki} lu-lu-bu{ki} a-ra2 9-kam ba-hul', 'Szulgi 45b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa ur-bi2-lum{ki} ba-hul', 'Szulgi 46*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szul-gi nita kalag-ga lugal ur2i{ki}-ma lugal an ub-da limmu2-ba-ke4 ki-masz{ki} hu-ur5-ti{ki} u3 ma-da-bi u4 1-a mu-hul', 'Szulgi 46');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa ki-masz{ki} ba-hul', 'Szulgi 47a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szul-gi nita kalag-ga lugal ur2i{ki}-ma lugal an ub-da limmu2-ba-ke4 4-ba-ke4 ki-masz{ki} hu-ur5-ti{ki} u3 ma-da-bi u4 1-a mu-hul-a mu us2-sa-bi', 'Szulgi 47b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa ki-masz{ki} ba-hul mu us2-sa-a-bi', 'Szulgi 48*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('ha-ar-szi{ki} ki-masz{ki} hu-ur5-ti{ki} u3 ma-da-bi u4 1-bi u4 1-a ba-hul', 'Szulgi 48a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('ki-masz{ki} a-ra2 2-kam ba-hul', 'Szulgi 48b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('2-kam ha-ar-szi{ki} ba-hul', 'Szulgi 48c');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa ha-ar-szi{ki} u3 ki-masz{ki} ba-hul', 'Amar-Sin 1*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}amar-{d}en.zu lugal-am3', 'Amar-Sin 1');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}amar-{d}en.zu lugal', 'Amar-Sin 2*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}amar-{d}en.zu lugal-e ur-bi2-lum{ki} mu-hul', 'Amar-Sin 2');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}amar-{d}en.zu lugal-e ur-bi2-lum{ki} mu-hul', 'Amar-Sin 3*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}amar-{d}en.zu lugal-e {d}gu-za mah {d}en-lil2-la2 sza3 hul2-la in-dim2', 'Amar-Sin 3');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}gu-za mah sza3 hul2-la {d}en-lil2-la2 ba-dim2', 'Amar-Sin 4*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-mah-gal-an-na en-{d}nanna ba-hun-ga2', 'Amar-Sin 4a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-mah-gal-an-na en-{d}nanna masz2-e i3-pad3', 'Amar-Sin 4b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-{d}nanna {d}amar-{d}en.zu-ra-ki-ag2-an-na masz2-e i3-pad3', 'Amar-Sin 4c');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa en-mah-gal-an-na en-{d}nanna ba-hun', 'Amar-Sin 5*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-unu6-gal-an-na / en-u3-nu-gal-an-na en-{d}inanna unug{ki}-ga ba-hun', 'Amar-Sin 5');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa en-unu6-gal-an-na en-{d}inanna unug{ki}-ga ba-hun', 'Amar-Sin 6*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}amar-{d}en.zu lugal-e sza-asz-ru-um{ki} a-ra2 2-kam u3 szu-ru-ud-hu-um{ki} mu-hul', 'Amar-Sin 6');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa sza-asz-ru-um{ki} ba-hul', 'Amar-Sin 7*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}amar-{d}en.zu lugal-e bi2-tum-ra-bi2-um{ki} i3-ab-ru{ki} ma-da ma-da-bi u3 hu-uh2-nu-ri{ki} mu-hul', 'Amar-Sin 7');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa hu-uh2-nu-ri{ki} ba-hul', 'Amar-Sin 8*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-nun-gal-an-na en-nun-e-{d}amar-{d}en.zu ki-ag2 en eridu{ki} ba-hun', 'Amar-Sin 8a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-nun-ne2-ki-ag2', 'Amar-Sin 8b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa en-nun-ne2-ki-ag2', 'Amar-Sin 9a*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa en eridu{ki} ba-hun-ga2', 'Amar-Sin 9b*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-{d}nanna-{d}amar-{d}en.zu-ki-ag2-ra en-{d}nanna ga-esz{ki} kar-zi-da{ki}-ka a-ra2 3-kam ba-hun', 'Amar-Sin 9');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa en-{d}nanna kar-zi-da{ki}-ka ba-hun', 'Szu-Suen 1*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szu-{d}en.zu lugal-am3', 'Szu-Suen 1');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}szu-{d}en.zu lugal', 'Szu-Suen 2*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 ma2 dara3-abzu {d}en-ki in-dim2 mu-du8', 'Szu-Suen 2');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa ma2 dara3-abzu {d}en-ki ba-ab-du8 ba-dim2', 'Szu-Suen 3*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 si-ma-num2{ki} mu-hul', 'Szu-Suen 3');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 si-ma-num2{ki} mu-hul', 'Szu-Suen 4*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 bad3 mar-tu mu-ri-iq ti-id-ni-im mu-du3', 'Szu-Suen 4');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 bad3 mar-tu mu-ri-iq ti-id-ni-im mu-du3', 'Szu-Suen 5');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 bad3 mar-tu mu-ri-iq ti-id-ni-im mu-du3 mu us2-sa-a-bi', 'Szu-Suen 6*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 na-ru2-a mah {d}en-lil2 {d}nin-lil2-ra mu-ne-du3', 'Szu-Suen 6');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}szu-{d}en.zu lugal-e na-ru2-a mah mu-du3', 'Szu-Suen 7*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 lugal an ub-da 4-ba ma-da za-ab-sza-li{ki} mu-hul', 'Szu-Suen 7');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa ma-da za-ab-sza-li{ki} ba-hul', 'Szu-Suen 8*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 ma2-gur8 mah {d}en-lil2 {d}nin-lil2-ra mu-ne-dim2', 'Szu-Suen 8');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 ma2-gur8 mah {d}en-lil2 {d}nin-lil2-ra mu-ne-dim2', 'Szu-Suen 9*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}szu-{d}en.zu lugal ur2i{ki}-ma-ke4 e2-{d}szara2 umma{ki}-ka mu-du3', 'Szu-Suen 9');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 e2-{d}szara2 umma{ki} mu-du3', 'Ibbi-Sin 1*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal', 'Ibbi-Sin 1');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}i-bi2-{d}en.zu lugal', 'Ibbi-Sin 2*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-{d}inanna unug{ki} masz-e i3-pad3', 'Ibbi-Sin 2');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa en-{d}inanna unug{ki}-ga masz-e i3-pad3', 'Ibbi-Sin 3*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 si-mu-ru-um{ki} mu-hul', 'Ibbi-Sin 3');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa si-mu-ru-um{ki} ba-hul', 'Ibbi-Sin 4*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-am-gal-an-na en-{d}inanna ba-hun', 'Ibbi-Sin 4');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('tu-ki-in-hatytyi-mi-ig-ri2-sza dumu-munus lugal ensi2 za-ab-sza-li{ki}-ke4 ba-an-tuk', 'Ibbi-Sin 5');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa dumu-munus lugal ensi2 za-ab-sza-li{ki} ba-an-tuk', 'Ibbi-Sin 6*');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 nibru{ki} ur2i{ki}-ma-ke4 bad3 gal-bi mu-du3', 'Ibbi-Sin 6');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa bad3 gal nibru{ki} ba-du3', 'Ibbi-Sin 7');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa bad3 gal ba-du3 us2-sa-bi', 'Ibbi-Sin 8');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 hu-uh2-nu-ri{ki} sag-kul ma-da an-sza-an{ki}-sze3 ... dugud ba-szi-in-gin ...-gim bi ...', 'Ibbi-Sin 9');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-nir-si3-an-na en-{d}inanna masz2-e in-pad3', 'Ibbi-Sin 10');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-nam-szita4 {d}i-bi2-{d}en.zu-sze3 szud3-sag en-{d}en-ki eridu{ki}-ga masz-e in-pad3', 'Ibbi-Sin 11a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('en-nam-szita4 en eridu{ki} ba-hun', 'Ibbi-Sin 11b');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 gu-za an {d}nanna-ra mu-na-dim2', 'Ibbi-Sin 12');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 gu-za an {d}nanna-ra mu-na-dim2', 'Ibbi-Sin 13');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 szuszan{ki} a-dam-dun{ki} a-wa-an{ki} u4-gim ka bi-in-gi4 u4 1-a mu-un-gur2 en-bi lu2-a mi-ni-in-dab5-ba-a', 'Ibbi-Sin 14');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ra {d}nanna-a sza3 ki-ag2-ga2-ni dalla mu-un-na-an-e3-a', 'Ibbi-Sin 15');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 {d}nanna-ar {d}nun-me-te-an-na mu-na-dim2', 'Ibbi-Sin 16');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ra mar-tu a2 im.u19- ul-ta uru{ki} nu zu gu2 im-ma-an-ga2-ar', 'Ibbi-Sin 17');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 {d}nin-lil2 u3 {d}inanna e2-szutum2 e2-gi-na-ab-tum ku3 mu-ne-du3', 'Ibbi-Sin 18');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('us2-sa {d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 {d}nin-lil2 u3 {d}inanna e2-szutum2 e2-gi-na-ab-tum ku3 mu-ne-du3', 'Ibbi-Sin 19');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 {d}en-lil2-le2 me-lam2-ma-ni kur-kur-ra bi2-in-dul4', 'Ibbi-Sin 20');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 {d}nin-igi-zi-bar-ra balag {d}inanna-ra mu-na-dim2', 'Ibbi-Sin 21');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 a-ma-ru ni3-du11-ga dingir-re-ne-ke4 zag an-ki im-suh3-suh3-a ur2i{ki} uruXud{ki} tab-ba bi2-in-gi-en', 'Ibbi-Sin 22');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ra ugu-dul5-bi dugud kur-be2 mu-na-e-ra', 'Ibbi-Sin 23');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}i-bi2-{d}en.zu lugal ur2i{ki}-ma-ke4 ... bi2-ra', 'Ibbi-Sin 24');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('{d}en-ki ga-sza e2-a-na ba-an-ku4', 'unknown a');
INSERT INTO `canonical_year` (`text`, `abbreviation`) VALUES ('e2-{d}ne3-unug ba-du3', 'unknown b');

CREATE TABLE `month_reference`
(
	`month_reference_id`
		INT
		PRIMARY KEY
		NOT NULL
		AUTO_INCREMENT,
	`text_section_id`
		INT
		NOT NULL,
	`canonical_month_id`
		INT
		NOT NULL,
	`text`
		NVARCHAR(270)
		NOT NULL,
	`confidence`
		DECIMAL(4, 3)		-- 0.000 ~ 1.000
		NULL
		DEFAULT 0,
	`reviewed`
		BOOL
		NOT NULL
		DEFAULT false,

	FOREIGN KEY (`text_section_id`)
		REFERENCES `text_section` (`text_section_id`),
	FOREIGN KEY (`canonical_month_id`)
		REFERENCES `canonical_month` (`canonical_month_id`)
);

CREATE TABLE `year_reference`
(
	`year_reference_id`
		INT
		PRIMARY KEY
		NOT NULL
		AUTO_INCREMENT,
	`text_section_id`
		INT
		NOT NULL,
	`canonical_year_id`
		INT
		NOT NULL,
	`text`
		NVARCHAR(270)
		NOT NULL,
	`confidence`
		DECIMAL(4, 3)		-- 0.000 ~ 1.000
		DEFAULT 0,
	`reviewed`
		BOOL
		NOT NULL
		DEFAULT false,

	FOREIGN KEY (`text_section_id`)
		REFERENCES `text_section` (`text_section_id`),
	FOREIGN KEY (`canonical_year_id`)
		REFERENCES `canonical_year` (`canonical_year_id`)
);

CREATE TABLE `name`
(
	`name_id`
		INT
		PRIMARY KEY
		NOT NULL
		AUTO_INCREMENT,
	`name_text`
		VARCHAR(100)
		NOT NULL
);

CREATE TABLE `name_reference`
(
	`name_reference_id`
		INT
		PRIMARY KEY
		NOT NULL
		AUTO_INCREMENT,
	`name_id`
		INT
		NOT NULL,
	`tablet_id`
		INT
		NOT NULL,
	FOREIGN KEY (`tablet_id`)
		REFERENCES `tablet` (`tablet_id`),
	FOREIGN KEY (`name_id`)
		REFERENCES `name` (`name_id`)
);

-- references to deities
-- codifying damage to tablets
-- transaction table
	-- types of loan
	-- roles (buyer, seller, scribe, witness, good)
-- personal names / titles / gender
		-- question: gender binary in Sumerian ?  third gender ?
-- location
