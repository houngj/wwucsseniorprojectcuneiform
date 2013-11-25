#!/usr/bin/python

import codecs
import unicodedata

def repl(s):
    r = [s, True, None]

    # Desirable transliteration characters.

    if ('A' <= s <= 'Z') \
        or ('a' <= s <= 'z') or ('-' == s) or ('\n' == s) \
        or ('0' <= s <= '9') or ('{' == s) or ('}' == s) \
        or ('.' == s) or ('+' == s):
        pass

    # Transliteration noise we are not interested in.

    elif ('?' == s) or ('#' == s) or ('!' == s) or ('_' == s) \
        or ('[' == s) or (']' == s):
        r[0] = ''

    # Other noise, such as cut-and-paste, Unicode conversion artifacts,
    # typos, unhelpful translator notes, and Microsoft Office-ification
    # noise, we want to know about; we'll have to correct this PN by hand.

    else:
        r = [s, False, 'Garbage detected.']

    return r;

REPLACEMENTS = {

    # Common combined forms in Sumerian transliterations.  There are
    # few enough of them that rather than denormalizing them, we'll just
    # replace them with the ASCII transliterated equivalents.

    # Note that some of them are sloppy -- phonetically, S-dot and S-caron
    # are distinct sounds coming from increasingly distinct languages, but
    # for the Ur III era, we are not interested in differentiating them.

    # Note: CIRCUMFLEX comes up a lot, and I'm not sure what to do with
    # it since I've never seen it in a transliteration; there's only the
    # acute vowels being marked as 2, the greve vowels (which do not appear
    # in the names.txt file, as they appear to already have been marked as
    # 3).  Circumflexes don't appear in transliteration, so my guess is
    # that they're used by phonologists marking vowel lengths/tones/values
    # or something of no immediate concern to us.  Not having any better
    # idea, I busted the circumflexes down to the zero mark.  That's
    # almost certainly not right.

    'LATIN CAPITAL LETTER S WITH CARON':     'SZ',
    'LATIN CAPITAL LETTER S WITH DOT BELOW': 'SZ',
    'LATIN CAPITAL LETTER T WITH DOT BELOW': 'T' ,
    'LATIN SMALL LETTER A WITH CIRCUMFLEX':  'a' ,
    'LATIN SMALL LETTER I WITH ACUTE':       'i2',
    'LATIN SMALL LETTER I WITH CIRCUMFLEX':  'i' ,
    'LATIN SMALL LETTER S WITH CARON':       'sz',
    'LATIN SMALL LETTER S WITH DOT BELOW':   'sz',
    'LATIN SMALL LETTER T WITH DOT BELOW':   't' ,
    'LATIN SMALL LETTER U WITH ACUTE':       'u2',
    'LATIN SMALL LETTER U WITH CIRCUMFLEX':  'u',

    # I presume this is an error; I've never seen a cedilla in
    # a Sumerian transliteration.

    'CEDILLA': ''
    }

def decode(name):
    return REPLACEMENTS[name]

filename_in = 'names.txt'
filename_out = 'names_clean.txt'
filename_err = 'names_clean_err.txt'

with codecs.open(filename_in, encoding='utf-8') as fin:
    with open(filename_out, 'w') as fout:
        with codecs.open(filename_err, encoding='utf-8', mode='w') as ferr:

            ascii = ''
            err = False
            ln = 1
            badnames = 0
            goodnames = 0

            line = fin.readline()

            while line:

                for c in line:
                    d = unicodedata.decomposition(c)
                    if d:

                        # Character is a unicode composition.

                        ascii = ascii + decode(unicodedata.name(c))

                    else:

                        # Character is not a unicode decomposition.

                        (replacement, handled, reason) = repl(c)

                        if handled:

                            # We want to keep this char.

                            ascii = ascii + replacement

                        else:

                            # This is bad noise that indicates that
                            # there's something other than just a PN
                            # on this line.

                            if not err:
                                ferr.write(str(ln) + ': ' + line)
                            err = True
                            ferr.write('\tNot handled: ')
                            ferr.write('\t' + c)
                            ferr.write('\t' + str(hex(ord(c))))
                            ferr.write('\t' + reason)
                            ferr.write('\n')

                if not err:
                    fout.write(ascii)
                    goodnames = goodnames + 1
                else:
                    fout.write('\n')
                    badnames = badnames + 1

                ascii = ''
                err = False
                ln = ln + 1            
                line = fin.readline()

            ferr.write('\n')
            ferr.write(
                '{0} of {1} names must be corrected by hand.'.format(
                    badnames,
                    (badnames + goodnames)))
