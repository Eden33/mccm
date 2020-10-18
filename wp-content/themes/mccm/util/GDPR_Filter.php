<?php

/**
 * Respect the "General Data Protection Regulation" of the EU (https://gdpr.eu/cookies/).
 * 
 * @author edi
 */
class GDPR_Filter {
    
    private const PATTERN_YOUTUBE       = '/<iframe.*src=\"(.*)youtube(.*)\".*><\/iframe>/isU';
    private const PATTERN_VIMEO         = '/<iframe.*src=\"(.*)vimeo(.*)\".*><\/iframe>/isU';
    private const PATTERN_GOOGLE_MAPS   = '/<iframe.*src=\"(.*)google(.*)maps(.*)\".*><\/iframe>/isU';
    
    private const GET_YOUTUBE_ACCEPTED      = 'gdprYoutubeAccepted';
    private const GET_VIMEO_ACCEPTED        = 'gdprVimeoAccepted';
    private const GET_GOOGLE_MAPS_ACCEPTED  = 'gdprGoogleMapsAccepted';
    

    public static function filterContent($content) {
        if(self::containsYoutubeIFrames($content) && !isset($_GET[self::GET_YOUTUBE_ACCEPTED])) {            
            $content = self::replaceYoutubeIFrames($content, 
                self::generateIFrameBlockerHtml("Aufgrund datenschutzrechtlicher Bestimmungen wird das Video erst auf unserer Seite eingebettet, nachdem Sie auf 'Video laden' geklickt haben. 
                         Mit dem Laden des Videos akzeptieren Sie die Datenschutzerkl&auml;rung von YouTube."
                        , "https://policies.google.com/privacy"
                        , "Video Laden"
                        , self::GET_YOUTUBE_ACCEPTED));
        }
        if(self::containsVimeoIFrames($content) && !isset($_GET[self::GET_VIMEO_ACCEPTED])) {
            $content = self::replaceVimeoIFrames($content, 
                self::generateIFrameBlockerHtml("Aufgrund datenschutzrechtlicher Bestimmungen wird das Video erst auf unserer Seite eingebettet, nachdem Sie auf 'Video laden' geklickt haben.
                         Mit dem Laden des Videos akzeptieren Sie die Datenschutzerkl&auml;rung von Vimeo."
                        , "https://vimeo.com/privacy"
                        , "Video Laden"
                        , self::GET_VIMEO_ACCEPTED));
        }
        if(self::containsGoogleMapsIFrames($content) && !isset($_GET[self::GET_GOOGLE_MAPS_ACCEPTED])) {
            $content = self::replaceGoogleMapsIFrames($content,
                self::generateIFrameBlockerHtml("Aufgrund datenschutzrechtlicher Bestimmungen wird die Karte erst auf unserer Seite eingebettet, nachdem Sie auf 'Karte laden' geklickt haben.
                         Mit dem Laden der Karte akzeptieren Sie die Datenschutzerkl&auml;rung von Google."
                    , "https://policies.google.com/privacy"
                    , "Karte Laden"
                    , self::GET_GOOGLE_MAPS_ACCEPTED));
        }
        return $content;
    }
    
    public static function containsYoutubeIFrames($content) {
        return preg_match(self::PATTERN_YOUTUBE, $content);
    }
    
    public static function replaceYoutubeIFrames($content, $replacement) {
        return preg_replace(self::PATTERN_YOUTUBE, $replacement, $content);
    }

    public static function containsVimeoIFrames($content) {
        return preg_match(self::PATTERN_VIMEO, $content);
    }
    
    public static function replaceVimeoIFrames($content, $replacement) {
        return preg_replace(self::PATTERN_VIMEO, $replacement, $content);
    }

    public static function containsGoogleMapsIFrames($content) {
        return preg_match(self::PATTERN_GOOGLE_MAPS, $content);
    }
    
    public static function replaceGoogleMapsIFrames($content, $replacement) {
        return preg_replace(self::PATTERN_GOOGLE_MAPS, $replacement, $content);
    }
    private static function generateIFrameBlockerHtml($acceptanceText, $privacyUrl, $acceptBtnLabel, $gdprAcceptedFlag) {
        $html = 
            '<div class="gdpr-video-blocker">
            	<p>'.$acceptanceText .'</br><a href="'.$privacyUrl .'" target="_blank" rel="nofollow">Mehr erfahren<a></p>
                <p><a href="'.get_the_permalink().'?'.$gdprAcceptedFlag.'=true">'.$acceptBtnLabel.'</a></p>
            </div>';
        return $html;
    }
}
?>