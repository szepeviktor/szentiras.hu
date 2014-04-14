<?php
use SzentirasHu\Controllers\Display\TextDisplayController;
use SzentirasHu\Lib\Reference\ChapterRange;
use SzentirasHu\Lib\Reference\ChapterRef;

/**

 */

class TextDisplayControllerTest extends TestCase {

    public function testCollectChapterIds() {
        $controller = new TextDisplayController();
        $range = new ChapterRange();
        $range->chapterRef = new ChapterRef(2);
        $range->untilChapterRef = new ChapterRef(4);
        $ids = $controller->collectChapterIds($range);
        $this->assertContains(2, $ids);
        $this->assertContains(3, $ids);
        $this->assertContains(4, $ids);
        $this->assertNotContains(1, $ids);
        $this->assertNotContains(5, $ids);
    }

} 