<?php

use PHPUnit\Framework\TestCase;
require_once dirname(__FILE__).'/../util/GDPR_Filter.php';

final class GDPR_Filter_Test extends TestCase {
    
    // YOUTUBE
    public function testDetectYoutubeIFrame() {
        $content = 'Foo<iframe title="Trailer Montikel18" width="584" height="329" src="https://www.youtube.com/embed/akIqgBxmaOA?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>bar';
        $found = GDPR_Filter::containsYoutubeIFrames($content);
        $this->assertEquals($found, 1);
    }
    public function testDetectYoutubeIFrame2() {
        $content = 'Foo<iframe title="Trailer Montikel18" width="584" height="329" src="https://www.foobar.com/embed/akIqgBxmaOA?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>bar';
        $found = GDPR_Filter::containsYoutubeIFrames($content);
        $this->assertEquals($found, 0);
    }
    
    public function testReplaceYoutubeIFrame() {
        $content = 'Fo<iframe title="Trailer Montikel18" width="584" height="329" src="https://www.youtube.com/embed/akIqgBxmaOA?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>ar';
        $newContent = GDPR_Filter::replaceYoutubeIFrames($content, 'ob');
        $this->assertEquals($newContent, 'Foobar');
        $this->assertNotEquals($content, 'Foobar');
    }

    // VIMEO
    public function testDetectVimeoIFrame() {
        $content = 'Foo<iframe title="Trailer Montikel18" width="584" height="329" src="https://www.vimeo.com/embed/akIqgBxmaOA?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>bar';
        $found = GDPR_Filter::containsVimeoIFrames($content);
        $this->assertEquals($found, 1);
    }
    public function testDetectVimeoIFrame2() {
        $content = 'Foo<iframe title="Trailer Montikel18" width="584" height="329" src="https://www.foobar.com/embed/akIqgBxmaOA?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>bar';
        $found = GDPR_Filter::containsVimeoIFrames($content);
        $this->assertEquals($found, 0);
    }
    
    public function testReplaceVimeoIFrame() {
        $content = 'Fo<iframe title="Trailer Montikel18" width="584" height="329" src="https://www.vimeo.com/embed/akIqgBxmaOA?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>ar';
        $newContent = GDPR_Filter::replaceVimeoIFrames($content, 'ob');
        $this->assertEquals($newContent, 'Foobar');
        $this->assertNotEquals($content, 'Foobar');
    }
    
    // GOOGLE MAPS
    public function testDetectGoogleMapsIFrame() {
        $content = 'Foo<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d2238.798606883169!2d9.578761185726355!3d47.24457160114467!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2sat!4v1528407500642" style="border:0" allowfullscreen="" width="600" height="450" frameborder="0"></iframe>bar';
        $found = GDPR_Filter::containsGoogleMapsIFrames($content);
        $this->assertEquals($found, 1);
    }
    public function testDetectGoogleMapsIFrame2() {
        $content = 'Foo<iframe src="https://www.foobar.com/maps/embed?pb=!1m14!1m12!1m3!1d2238.798606883169!2d9.578761185726355!3d47.24457160114467!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2sat!4v1528407500642" style="border:0" allowfullscreen="" width="600" height="450" frameborder="0"></iframe>bar';
        $found = GDPR_Filter::containsGoogleMapsIFrames($content);
        $this->assertEquals($found, 0);
    }
    public function testReplaceGoogleMapsIFrame() {
        $content = 'Fo<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d2238.798606883169!2d9.578761185726355!3d47.24457160114467!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2sat!4v1528407500642" style="border:0" allowfullscreen="" width="600" height="450" frameborder="0"></iframe>ar';
        $newContent = GDPR_Filter::replaceGoogleMapsIFrames($content, 'ob');
        $this->assertEquals($newContent, 'Foobar');
        $this->assertNotEquals($content, 'Foobar');
    }
}