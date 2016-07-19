Kitten Coven Project
====================

We are a bunch of warlocks Kittens, so beware our power!

List of Great Masters:

* Merkury
* Rhysbeingsocial
=======
# Requirements
* PHP v5.6
* Composer
* GuzzleHTTP ^6.2

#### To run the project, first, execute `composer install` to get all the vendor bundles

What Da Fuck is this!?!?!
=========================
This awesome CLI Application is a **Wikipedia API** consumer.
Using the API to parse pages, I've created a small command, with a 
subset of helper functions to parse properly the Wikipedia __infobox__
and the extract, to store their content into json files. The regexps
right now work with a lot of different pages, but because the wikitext
you can find some of them that aren't being parsed.

You can feed the parser with whatever you feel more comfortable, 
page names or the right-side URI fragment (/wiki/Page) (actually this 
is more a thing that I will add in the next days via command parameter)
o with a file with a nice header (you only need to seat the first line
with a name you want to use, that line won't be taken in account.

Also because the <del>fucking</del> complexity of the wikitext's format, 
I've just applied a rule of thumb to have regular JSONs:

__All__ the information that is part of collapsable list (or similar)
and is not following the pattern {{ blah blah list|... }} it goes
directly to the extra field and there you can process after.

Data Examples (We Love it!)
===========================

The result JSON is like this:

{
    "extract": "Angry Birds Star Wars is a puzzle video game, a crossover between the Star Wars franchise and the Angry Birds series of video games, launched on November 8, 2012, first for Windows, iOS and Android devices, later also to Mac and BlackBerry. The game is the sixth Angry Birds game in the series. The characters are copyrighted from George Lucas's double-trilogy. On July 18, 2013, Rovio announced that Angry Birds Star Wars will be heading for the PlayStation 3, PlayStation Vita, Xbox 360, Wii, Wii U and the Nintendo 3DS on October 29, 2013 in conjunction with Activision. As of August 2013, the game has been downloaded over 100 million times on its various platforms. The game is a launch title for the PlayStation 4 and Xbox One.\nOn July 15, 2013, Rovio announced a sequel, entitled Angry Birds Star Wars II. It is based on the Star Wars prequel trilogy and the television show Star Wars Rebels. Angry Birds Star Wars II was released on September 18, 2013.\n",
    "infoBox": {
        "title": "''Angry Birds Star Wars''",
        "image": "Angry Birds Star Wars.png",
        "caption": "The app icon",
        "developer": "[[Rovio Entertainment]][[Exient Entertainment]] (PS3\/PS4\/PS Vita\/X360\/Xbox One\/Wii\/Wii U\/3DS)",
        "publisher": "[[Rovio Entertainment]] (in conjunction with [[LucasArts]]), [[Activision]] (PS3\/PS4\/Vita\/X360\/Xbox One\/Wii\/Wii U\/3DS)",
        "distributor": "",
        "producer": "",
        "designer": "",
        "composer": "",
        "series": "''[[Angry Birds]]''",
        "engine": "[[Box2D]]",
        "released": " November 8, 2012 ",
        "genre": "[[Puzzle video game|Puzzle]]",
        "modes": "",
        "platforms": "[[Android (operating system)|Android]], [[iOS (Apple)|iOS]], [[BlackBerry 10]],{{cite web |title",
        "media": ""
    }
}

And basically this is it.

If you want to use, feel free, also if you want to give me a yatch, 
tons of gold, or a soda can because you are really, really thankful 
with this tool, contact me throu GitHub
