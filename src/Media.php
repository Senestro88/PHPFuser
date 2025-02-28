<?php

namespace PHPFuser;

/**
 * @author Senestro
 */
class Media {
    // PRIVATE VARIABLE

    // PUBLIC VARIABLES

    // PUBLIC METHODS

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }

    /**
     * Validates if the provided file extension is a valid video extension.
     *
     * @param string $extension The file extension to check (e.g., 'mp4').
     * @return bool Returns true if the extension is valid, false otherwise.
     */
    public static function ivValidVideoByExtension(string $extension): bool {
        // Retrieve the list of video file extensions and their MIME types.
        $extensions = self::getVideoExtensions();
        // Check if the provided extension is in the list of valid extensions.
        return isset($extensions[$extension]);
    }

    /**
     * Validates if the provided MIME type is a valid video MIME type.
     *
     * @param string $mimeType The MIME type to check (e.g., 'video/mp4').
     * @return bool Returns true if the MIME type is valid, false otherwise.
     */
    public static function ivValidVideoByMimeType(string $mimeType): bool {
        // Retrieve the list of video file extensions and their corresponding MIME types.
        $extensions = self::getVideoExtensions();
        // Check if the provided MIME type exists in the list of valid MIME types.
        return in_array($mimeType, $extensions);
    }

    /**
     * Validates if the provided filename has a valid video file extension.
     *
     * This function retrieves the file extension from the filename and checks
     * if it is a valid video extension by calling the `ivValidVideoByExtension` method.
     *
     * @param string $filename The filename to check (e.g., 'movie.mp4').
     * @return bool Returns true if the filename has a valid video extension, false otherwise.
     */
    public static function ivValidVideoByFilename(string $filename): bool {
        // Retrieve the file extension from the filename.
        $extension = File::getExtension($filename);
        // Check if the extension is valid for video files.
        return self::ivValidVideoByExtension($extension);
    }


    /**
     * Validates if the provided audio file extension is valid.
     *
     * @param string $extension The file extension to check (e.g., 'mp3').
     * @return bool Returns true if the extension is valid, false otherwise.
     */
    public static function ivValidAudioByExtension(string $extension): bool {
        // Retrieve the list of audio file extensions and their MIME types.
        $extensions = self::getAudioExtensions();
        // Check if the provided extension is in the list of valid audio extensions.
        return isset($extensions[$extension]);
    }

    /**
     * Validates if the provided MIME type is a valid audio MIME type.
     *
     * @param string $mimeType The MIME type to check (e.g., 'audio/mp3').
     * @return bool Returns true if the MIME type is valid, false otherwise.
     */
    public static function ivValidAudioByMimeType(string $mimeType): bool {
        // Retrieve the list of audio file extensions and their corresponding MIME types.
        $extensions = self::getAudioExtensions();
        // Check if the provided MIME type exists in the list of valid MIME types.
        return in_array($mimeType, $extensions);
    }

    /**
     * Validates if the provided filename has a valid audio file extension.
     *
     * This function retrieves the file extension from the filename and checks
     * if it is a valid audio extension by calling the `ivValidAudioByExtension` method.
     *
     * @param string $filename The filename to check (e.g., 'song.mp3').
     * @return bool Returns true if the filename has a valid audio extension, false otherwise.
     */
    public static function ivValidAudioByFilename(string $filename): bool {
        // Retrieve the file extension from the filename.
        $extension = File::getExtension($filename);
        // Check if the extension is valid for audio files.
        return self::ivValidAudioByExtension($extension);
    }

    /**
     * Retrieves a list of video file extensions along with their corresponding MIME types.
     *
     * @return array An associative array where the keys are video file extensions 
     *               and the values are their corresponding MIME types.
     */
    public static function getVideoExtensions(): array {
        return array(
            "mp4" => "video/mp4",
            "avi" => "video/x-msvideo",
            "mov" => "video/quicktime",
            "wmv" => "video/x-ms-wmv",
            "flv" => "video/x-flv",
            "mkv" => "video/x-matroska",
            "webm" => "video/webm",
            "3gp" => "video/3gpp",
            "3g2" => "video/3gpp2",
            "m4v" => "video/x-m4v",
            "mpg" => "video/mpeg",
            "mpeg" => "video/mpeg",
            "mpv" => "video/mpv",
            "m2v" => "video/mpeg",
            "mxf" => "application/mxf",
            "ogv" => "video/ogg",
            "mts" => "video/avchd",
            "ts" => "video/mp2t",
            "f4v" => "video/x-f4v",
            "f4p" => "video/mp4",
            "f4a" => "audio/mp4",
            "f4b" => "audio/mp4",
            "asf" => "video/x-ms-asf",
            "vob" => "video/dvd",
            "rm" => "application/vnd.rn-realmedia",
            "rmvb" => "application/vnd.rn-realmedia-vbr",
            "divx" => "video/x-msvideo",
            "xvid" => "video/x-xvid",
            "yuv" => "video/x-raw-yuv",
            "dv" => "video/x-dv",
            "amv" => "video/x-amv",
            "avchd" => "video/avchd",
            "bik" => "video/vnd.radgametools.bink",
            "drc" => "video/drc",
            "ivf" => "video/x-ivf",
            "m2p" => "video/mpeg",
            "m2ts" => "video/mp2t",
            "mpe" => "video/mpeg",
            "mp2" => "video/mpeg",
            "mp2v" => "video/mpeg",
            "mjp" => "video/mjpeg",
            "mjpg" => "video/x-motion-jpeg",
            "nut" => "video/x-nut",
            "qt" => "video/quicktime",
            "trp" => "video/mp2t",
            "tsv" => "video/mp2t",
            "skm" => "video/skm",
            "3dm" => "video/x-3dm",
            "3dmf" => "video/x-3dmf",
            "3dv" => "video/x-3dv",
            "3ivx" => "video/x-3ivx",
            "asx" => "video/x-ms-asf",
            "avc" => "video/avc",
            "bdm" => "application/vnd.ms-3d.bdm",
            "bdmv" => "video/mp2t",
            "clpi" => "application/vnd.ms-3d.clpi",
            "cpk" => "video/x-cpk",
            "dif" => "video/dv",
            "dirac" => "video/dirac",
            "dpg" => "video/vnd.dpgraph",
            "dvx" => "video/x-dvx",
            "evo" => "video/x-evo",
            "fli" => "video/x-fli",
            "flc" => "video/x-flic",
            "gxf" => "application/gxf",
            "h264" => "video/h264",
            "h265" => "video/h265",
            "hevc" => "video/hevc",
            "jpm" => "video/jpm",
            "mj2" => "video/mj2",
            "mje" => "video/mjpeg",
            "mlv" => "video/x-magic-lantern-raw",
            "mod" => "video/mpeg",
            "mp1" => "video/mpeg",
            "mp4v" => "video/mp4",
            "mpeg1" => "video/mpeg",
            "mpeg2" => "video/mpeg",
            "mpeg4" => "video/mp4",
            "mpl" => "video/mpeg",
            "mpls" => "application/vnd.ms-3d.mpls",
            "nsv" => "video/x-nsv",
            "ogx" => "application/ogg",
            "ps" => "video/mpeg",
            "pva" => "video/x-pva",
            "roq" => "video/x-roq",
            "scm" => "video/x-scm",
            "svi" => "video/x-svi",
            "tivo" => "video/x-tivo",
            "vdat" => "video/x-vdat",
            "veg" => "application/veg",
            "vp6" => "video/x-vp6",
            "vp7" => "video/x-vp7",
            "vp8" => "video/x-vp8",
            "vp9" => "video/x-vp9",
            "vro" => "video/x-vro",
            "wpl" => "application/vnd.ms-wpl",
            "xesc" => "video/mp4"
        );
    }

    /**
     * Retrieves a list of audio file extensions along with their corresponding MIME types.
     *
     * @return array An associative array where the keys are audio file extensions 
     *               and the values are their corresponding MIME types.
     */
    public static function getAudioExtensions(): array {
        return array(
            "mp3" => "audio/mpeg",
            "wav" => "audio/wav",
            "aac" => "audio/aac",
            "flac" => "audio/flac",
            "ogg" => "audio/ogg",
            "m4a" => "audio/mp4",
            "wma" => "audio/x-ms-wma",
            "alac" => "audio/alac",
            "aiff" => "audio/x-aiff",
            "aif" => "audio/x-aiff",
            "aifc" => "audio/x-aiff",
            "opus" => "audio/opus",
            "amr" => "audio/amr",
            "mka" => "audio/x-matroska",
            "ra" => "audio/x-pn-realaudio",
            "ram" => "audio/x-pn-realaudio",
            "caf" => "audio/x-caf",
            "snd" => "audio/basic",
            "au" => "audio/basic",
            "mid" => "audio/midi",
            "midi" => "audio/midi",
            "rmi" => "audio/midi",
            "kar" => "audio/midi",
            "m3u" => "audio/x-mpegurl",
            "pls" => "audio/x-scpls",
            "xspf" => "application/xspf+xml",
            "m3u8" => "application/vnd.apple.mpegurl",
            "mpga" => "audio/mpeg",
            "ac3" => "audio/ac3",
            "eac3" => "audio/eac3",
            "dts" => "audio/vnd.dts",
            "dtshd" => "audio/vnd.dts.hd",
            "it" => "audio/x-it",
            "mod" => "audio/x-mod",
            "s3m" => "audio/x-s3m",
            "xm" => "audio/x-xm",
            "sf2" => "audio/x-soundfont",
            "sfz" => "audio/x-sfz",
            "voc" => "audio/x-voc",
            "wavpack" => "audio/x-wavpack",
            "wv" => "audio/x-wavpack",
            "oga" => "audio/ogg",
            "spx" => "audio/ogg",
            "lvp" => "audio/vnd.lucent.voice",
            "sid" => "audio/prs.sid",
            "cda" => "application/x-cdf",
            "atrac" => "audio/vnd.sony.atrac",
            "rtttl" => "audio/midi",
            "smaf" => "application/vnd.smaf",
            "adx" => "audio/adx",
            "ape" => "audio/ape",
            "bwf" => "audio/bwf",
            "cpr" => "audio/x-cubase-project",
            "dff" => "audio/dsd",
            "dsd" => "audio/dsd",
            "dsf" => "audio/dsf",
            "ins" => "audio/x-ins",
            "m4b" => "audio/mp4",
            "m4p" => "audio/mp4",
            "mpc" => "audio/x-musepack",
            "ots" => "audio/ots",
            "sng" => "audio/x-sng",
            "tak" => "audio/x-tak",
            "tta" => "audio/x-tta",
            "tta1" => "audio/x-tta1",
            "vqf" => "audio/x-vqf",
            "xmf" => "audio/mobile-xmf",
            "zab" => "audio/x-zab",
            "cmf" => "audio/x-cmf",
            "hmp" => "audio/x-hmp",
            "imf" => "audio/x-imf",
            "m15" => "audio/x-mod",
            "nna" => "audio/x-nna",
            "nst" => "audio/x-nst",
            "okt" => "audio/x-okt",
            "psm" => "audio/x-psm",
            "ptm" => "audio/x-ptm",
            "s3z" => "audio/x-s3z",
            "ult" => "audio/x-ult",
            "umx" => "audio/x-umx",
            "wow" => "audio/x-wow",
            "669" => "audio/x-669",
            "abc" => "audio/x-abc",
            "apl" => "audio/x-apl",
            "avs" => "audio/x-avs",
            "dls" => "audio/x-dls",
            "fsc" => "audio/x-fsc",
            "fzb" => "audio/x-fzb",
            "gbs" => "audio/x-gbs",
            "gym" => "audio/x-gym",
            "hes" => "audio/x-hes",
            "kss" => "audio/x-kss",
            "mlv" => "audio/x-mlv",
            "mmp" => "audio/x-mmp",
            "mp_" => "audio/x-mp_",
            "nsf" => "audio/x-nsf"
        );
    }

    // PRIVATE METHODS

}
