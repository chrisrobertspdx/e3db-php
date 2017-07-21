<?php
/**
 * Tozny E3DB
 *
 * LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package    Tozny\E3DB
 * @copyright  Copyright (c) 2017 Tozny, LLC (https://tozny.com)
 * @license    MIT License
 */

namespace Tozny\E3DB\Types;

/**
 * Describe the meta information attributed to a specific encrypted record.
 *
 * @property-read string    $record_id     Unique ID of the record, or `null` if not yet written
 * @property-read \DateTime $created       When this record was created, or `null` if unavailable.
 * @property-read \DateTime $last_modified When this record last changed, or `null` if unavailable.
 * @property-read string    $version       Opaque version identifier created by the server on changes.
 *
 * @package Tozny\E3DB\Types
 */
class Meta implements \JsonSerializable, JsonUnserializable
{
    /**
     * @var string Unique ID of the record, or `null` if not yet written
     */
    protected $_record_id = null;

    /**
     * @var \DateTime When this record was created, or `null` if unavailable.
     */
    protected $_created = null;

    /**
     * @var \DateTime When this record last changed, or `null` if unavailable.
     */
    protected $_last_modified = null;

    /**
     * @var string Opaque version identifier created by the server on changes.
     */
    protected $_version = null;

    /**
     * @var string Unique ID of the client who wrote the record
     */
    public $writer_id;

    /**
     * @var string Unique ID of the record subject
     */
    public $user_id;

    /**
     * @var string Free-form description of the record content type
     */
    public $type;

    /**
     * @var array Map of String->String values describing the record's plaintext meta
     */
    public $plain = null;

    public function __construct( string $writer_id, string $user_id, string $type, array $plain = null )
    {
        $this->_record_id = null;
        $this->writer_id = $writer_id;
        $this->user_id = $user_id;
        $this->type = $type;
        $this->plain = $plain;
        $this->_created = null;
        $this->_last_modified = null;
        $this->_version = null;
    }

    /**
     * Magic getter to retrieve read-only properties.
     *
     * @param string $name Property name to retrieve
     *
     * @return mixed
     */
    public function __get( string $name )
    {
        $key = "_{$name}";
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        trigger_error( "Undefined property: Meta::{$name}", E_USER_NOTICE );
        return null;
    }

    /**
     * Serialize the object to JSON
     */
    function jsonSerialize(): array
    {
        return [
            'record_id'     => $this->_record_id,
            'writer_id'     => $this->writer_id,
            'user_id'       => $this->user_id,
            'type'          => $this->type,
            'plain'         => $this->plain,
            'created'       => self::jsonSerializeDate( $this->_created ),
            'last_modified' => self::jsonSerializeDate( $this->_last_modified ),
            'version'       => $this->_version,
        ];
    }

    /**
     * Specify how data should be unserialized from JSON and marshaled into
     * an object representation.
     *
     * @param string $json Raw JSON string to be decoded
     *
     * @return Meta
     *
     * @throws \Exception
     */
    static function decode( string $json ): Meta
    {
        $data = \json_decode( $json, true );

        if ( null === $data ) {
            throw new \Exception( 'Error decoding Meta JSON' );
        }

        return self::decodeArray( $data );
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * @param array $parsed
     *
     * @return Meta
     */
    static function decodeArray( array $parsed ): Meta
    {
        $meta = new Meta(
            $parsed['writer_id'],
            $parsed['user_id'],
            $parsed['type'],
            $parsed['plain']
        );
        $meta->_record_id = $parsed['record_id'];
        $meta->_created = new \DateTime( $parsed['created'] );
        $meta->_last_modified = new \DateTime( $parsed['last_modified'] );
        $meta->_version = $parsed['version'];

        return $meta;
    }

    /**
     * Helper function to coalesce null DateTime values for JSON serialization.
     *
     * @param \DateTime|null $date
     *
     * @return mixed
     */
    protected static function jsonSerializeDate( $date )
    {
        if ( $date !== null ) {
            return $date->format( \DateTime::ISO8601 );
        }

        return null;
    }
}