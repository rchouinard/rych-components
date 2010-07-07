<?php
/**
 * Ryan Chouinard's Components
 *
 * @category    Rych
 * @package     Rych_Gravatar
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @copyright   Copyright (c) 2009 Ryan Chouinard ({@link http://ryanchouinard.com})
 * @license     New BSD License
 * @version     $Id$
 */

/**
 * @category    Rych
 * @package     Rych_Gravatar
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 */
class Rych_Gravatar
{

    const DEFAULT_GRAVATAR = null;
    const DEFAULT_404 = '404';
    const DEFAULT_MYSTERY = 'mm';
    const DEFAULT_IDENTICON = 'identicon';
    const DEFAULT_MONSTERID = 'monsterid';
    const DEFAULT_WAVATAR = 'wavatar';

    const RATING_G = 'g';
    const RATING_PG = 'pg';
    const RATING_R = 'r';
    const RATING_X = 'x';

    /**
     * Default image type to be returned
     *
     * @var     string
     */
    protected $_default;

    /**
     * Maximum rating of the returned image
     *
     * @var     string
     */
    protected $_rating;

    /**
     * Single-dimension pixel size of requested image
     *
     * @var     integer
     */
    protected $_size;

    /**
     * Get the URI of the Gravatar image
     *
     * @param   string  $email      Email address to build Gravatar with
     * @param   array   $options    Array of options to set
     * @return  string              URI of the Gravatar image
     */
    static public function getUriStatic($email, array $options = array ())
    {
        $class = __CLASS__;
        $gravatar = new $class($options);
        return $gravatar->getUri($email);
    }

    /**
     * Class constructor.
     *
     * @param   array               Array of options to set
     * @return  void                Returns nothing; it's a constructor
     */
    public function __construct(array $options = array ())
    {
        $this->_default = self::DEFAULT_GRAVATAR;
        $this->setRating(self::RATING_G);
        $this->setSize(80);
        $this->_setOptions($options);
    }

    /**
     * Get the URI of the Gravatar image
     *
     * @param   string  $email      Email address to build Gravatar with
     * @return  string              URI of the Gravatar image
     */
    public function getUri($email)
    {
        return sprintf('http://www.gravatar.com/avatar/%s.jpg?%s',
            $this->_hash($email),
            http_build_query(array (
                'r' => $this->getRating(),
                's' => $this->getSize(),
                'd' => $this->getDefault()
            ))
        );
    }

    /**
     * Get the default image type to be returned
     *
     * @return  string              Default image type to be returned
     */
    public function getDefault()
    {
        return $this->_default;
    }

    /**
     * Set the default image type to be returned
     *
     * If the user does not have an avatar, this option controls the type of
     * image to return instead. May be any one of the following:
     *
     *   - URI:         Setting this option to a URI will cause Gravatar to use
     *                  the image at that location as the default avatar.
     *   - Gravatar:    Use the default big-G.
     *   - Mystery:     A simple, cartoon-style silhouetted outline of a person,
     *                  which does not vary by email hash.
     *   - Identicon:   A geometric pattern based on an email hash.
     *   - MonsterID:   A generated "monster" with different colors, faces, etc.
     *   - Wavatar:     Generated faces with differing features and backgrounds.
     *   - 404:         Do not load any image if none is associated with the
     *                  email hash. Instead, return an HTTP 404 response.
     *
     * @param   string  $value      Default image type to be returned
     * @return  Rych_Gravatar       Provides a fluent interface
     */
    public function setDefault($value)
    {
        switch (strtolower($value)) {
            case self::DEFAULT_WAVATAR:
                $value = self::DEFAULT_WAVATAR;
                break;
            case self::DEFAULT_MONSTERID:
                $value = self::DEFAULT_MONSTERID;
                break;
            case self::DEFAULT_IDENTICON:
                $value = self::DEFAULT_IDENTICON;
                break;
            case self::DEFAULT_MYSTERY:
                $value = self::DEFAULT_MYSTERY;
                break;
            case self::DEFAULT_404:
                $value = self::DEFAULT_404;
                break;
            case self::DEFAULT_GRAVATAR:
                $value = self::DEFAULT_GRAVATAR;
                break;
            default:
                break;
        }
        $this->_default = $value;
        return $this;
    }

    /**
     * Get the maximum rating of the returned image
     *
     *  Will be one of g, pg, r, or x.
     *
     * @return  string              Pixel size of the requested image
     */
    public function getRating()
    {
        return $this->_rating;
    }

    /**
     * Set the maximum rating of the returned image
     *
     * Images may be rated according to the following scale:
     *
     *   - G:   Suitable for display on all websites with any audience type.
     *   - PG:  May contain rude gestures, provocatively dressed individuals,
     *          the lesser swear words, or mild violence.
     *   - R:   May contain such things as harsh profanity, intense violence,
     *          nudity, or hard drug use.
     *   - X:   May contain hardcore sexual imagery or extremely disturbing
     *          violence.
     *
     * Setting this option to R will return either an R, PG, or G image, in that
     * order of preference.
     *
     * @param   string  $value      Maximum rating of image to be returned
     * @return  Rych_Gravatar       Provides a fluent interface
     */
    public function setRating($value)
    {
        switch (strtolower($value)) {
            case self::RATING_X:
                $value = self::RATING_X;
                break;
            case self::RATING_R:
                $value = self::RATING_R;
                break;
            case self::RATING_PG:
                $value = self::RATING_PG;
                break;
            case self::RATING_G:
            default:
                $value = self::RATING_G;
                break;
        }
        $this->_rating = $value;
        return $this;
    }

    /**
     * Get the single-dimension pixel size of requested image
     *
     * Will be an integer value between 1 and 512.
     *
     * @return  integer             Pixel size of the requested image
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * Set the single-dimension pixel size of requested image
     *
     * Gravatar images are square, so a value of 80 will result in an image size
     * of 80x80. The value must be between 1 and 512. Values outside of this
     * range will be adjusted to the closest outside value.
     *
     * @param   integer $value      Pixel size of the requested image
     * @return  Rych_Gravatar       Provides a fluent interface
     */
    public function setSize($value)
    {
        $value = (int) $value;
        $this->_size = ($value < 1) ? 1 : $value;
        $this->_size = ($value > 512) ? 512 : $value;
        return $this;
    }

    /**
     * Calculate has value of a string
     *
     * Creates a consistent hash of the provided string based on Gravatar's
     * consistent hashing guidelines. Attempts to detect if the provided string
     * is already a hash, avoiding double-hashing.
     *
     * @param   string  $string     String for which hash should be calculated
     * @return  string              Consistent MD5 hash of provided string
     */
    protected function _hash($string)
    {
        // Check if string is already a hash
        if (strlen($string) != 32 || preg_match('/([^0-9a-f])/i', $string)) {
            $string = md5(strtolower(trim($string)));
        }
        return $string;
    }

    /**
     * Set multiple options at once
     *
     * @param   array   $options    Array of options to set
     * @return  void                Returns nothing
     */
    protected function _setOptions(array $options) {
        $options = array_change_key_case($options, CASE_LOWER);
        foreach ($options as $option => $value) {
            switch ($option) {
                case 'default':
                case 'd':
                    $this->setDefault($value);
                    break;
                case 'rating':
                case 'r':
                    $this->setRating($value);
                    break;
                case 'size':
                case 's':
                    $this->setSize($value);
                    break;
            }
        }
    }

}