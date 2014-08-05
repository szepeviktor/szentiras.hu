<?php
/**

 */

namespace SzentirasHu\Lib\Search;

use SzentirasHu\Lib\VerseContainer;
use SzentirasHu\Models\Entities\Verse;
use SzentirasHu\Models\Repositories\TranslationRepository;
use SzentirasHu\Models\Repositories\VerseRepository;

class SearchService {


    /**
     * @var SearcherFactory
     */
    private $searcherFactory;
    /**
     * @var \SzentirasHu\Models\Repositories\VerseRepository
     */
    private $verseRepository;
    /**
     * @var \SzentirasHu\Models\Repositories\TranslationRepository
     */
    private $translationRepository;

    function __construct(SearcherFactory $searcherFactory, VerseRepository $verseRepository, TranslationRepository $translationRepository)
    {
        $this->searcherFactory = $searcherFactory;
        $this->verseRepository = $verseRepository;
        $this->translationRepository = $translationRepository;
    }

    function getSuggestionsFor($term)
    {
        $searchParams = new FullTextSearchParams;
        $searchParams->text = $term;
        $searchParams->limit = 10;
        $searchParams->groupByVerse = true;
        $searchParams->synonyms = true;
        $sphinxSearcher = $this->searcherFactory->createSearcherFor($searchParams);
        $sphinxResults = $sphinxSearcher->get();
        if ($sphinxResults) {
            $verses = $this->verseRepository->getVersesInOrder($sphinxResults->verseIds);
            $texts = [];
            foreach ($verses as $key => $verse) {
                $parsedVerse = $this->getParsedVerse($verse);
                if ($parsedVerse) {
                    $texts[$key] = $parsedVerse;
                }
            }
            $excerpts = $sphinxSearcher->getExcerpts($texts);
            $textKeys = array_keys($texts);
            if ($excerpts) {
                foreach ($excerpts as $i => $excerpt) {
                    $verse = $verses[$textKeys[$i]];
                    $linkLabel = "{$verse->book->abbrev} {$verse->chapter},{$verse->numv}";
                    $result[] = [
                        'cat' => 'verse',
                        'label' => $excerpt,
                        'link' => "/{$verse->translation->abbrev}/{$linkLabel}",
                        'linkLabel' => $linkLabel
                    ];
                }
            }
            return $result;
        }
    }

    private function getParsedVerse(Verse $verse)
    {
        $verseContainer = new VerseContainer($verse->book);
        $verseContainer->addVerse($verse);
        $parsedVerses = $verseContainer->getParsedVerses();
        if ($parsedVerses[0]->getHeadingText()) {
            return $parsedVerses[0]->getHeadingText();
        } else {
            return $parsedVerses[0]->text;
        }
    }

    public function getDetailedResults($searchParams)
    {
        $searcher = $this->searcherFactory->createSearcherFor($searchParams);
        $results = $searcher->get();
        if ($results) {
            return $this->handleFullTextResults($results, $searchParams);
        } else {
            return null;
        }
    }

    private function handleFullTextResults($sphinxResults, FullTextSearchParams $params)
    {
        $sortedVerses = $this->verseRepository->getVersesInOrder($sphinxResults->verseIds);
        $verseContainers = $this->groupVersesByBook($sortedVerses, $params->translationId);
        $results = [];
        $chapterCount = 0;
        $verseCount = 0;
        foreach ($verseContainers as $verseContainer) {
            $result = [];
            $result['book'] = $verseContainer->book;
            $result['translation'] = $this->translationRepository->getById($verseContainer->book->translation_id);
            $parsedVerses = $verseContainer->getParsedVerses();
            $result['chapters'] = [];
            foreach ($parsedVerses as $verse) {
                $verseData = [];
                $verseData['chapter'] = $verse->chapter;
                $verseData['numv'] = $verse->numv;
                $verseData['text'] = '';
                if ($verse->headings) {
                    foreach ($verse->headings as $heading) {
                        $verseData['text'] .= $heading . ' ';
                    }
                }
                if ($verse->text) {
                    $verseData['text'] .= preg_replace('/<[^>]*>/', ' ', $verse->text);
                }
                $result['chapters'][$verse->chapter][] = $verseData;
                $result['verses'][] = $verseData;
                $verseCount++;
            }
            $chapterCount += count($result['chapters']);
            if (array_key_exists('verses', $result)) {
                $results[] = $result;
            }
        }
        return ['results' => $results, 'hitCount' => $params->groupByVerse ?  $verseCount : $chapterCount];
    }

    private function groupVersesByBook($sortedVerses, $translationId)
    {
        $verseContainers = [];
        foreach ($sortedVerses as $verse) {
            $book = $verse->book;
            $key = !$translationId ?
                $book->translation_id . '/' . $book->abbrev :
                $book->abbrev;
            if (!array_key_exists($key, $verseContainers)) {
                $verseContainers[$key] = new VerseContainer($book);
            }
            $verseContainer = $verseContainers[$key];
            $verseContainer->addVerse($verse);
        }
        return $verseContainers;
    }

    public function getSimpleResults($params)
    {
        $searcher = $this->searcherFactory->createSearcherFor($params);
        return $searcher->get();
    }

}