<?php
/**
 * Parser cache specific expiry check.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Parser
 */

/**
 * Parser cache specific expiry check.
 *
 * @ingroup Parser
 */
class CacheTime {
	/** @var array|bool ParserOptions which have been taken into account to
	 * produce output or false if not available.
	 */
	public $mUsedOptions;

	/** @var string Compatibility check */
	protected $mVersion = Parser::VERSION;

	/** @var string Time when this object was generated, or -1 for uncacheable. Used in ParserCache. */
	protected $mCacheTime = '';

	/**
	 * @var int Seconds after which the object should expire, use 0 for uncachable.
	 *   Used in ParserCache.
	 */
	protected $mCacheExpiry = null;

	/** @var bool Boolean variable indicating if the input contained variables like {{CURRENTDAY}} */
	protected $mContainsOldMagic;

	/** @var int Revision ID that was parsed */
	protected $mCacheRevisionId = null;

	/**
	 * @return string TS_MW timestamp
	 */
	function getCacheTime() {
		return wfTimestamp( TS_MW, $this->mCacheTime );
	}

	/**
	 * @return bool
	 */
	function containsOldMagic() {
		return $this->mContainsOldMagic;
	}

	/**
	 * @param bool $com
	 * @return bool
	 */
	function setContainsOldMagic( $com ) {
		return wfSetVar( $this->mContainsOldMagic, $com );
	}

	/**
	 * setCacheTime() sets the timestamp expressing when the page has been rendered.
	 * This does not control expiry, see updateCacheExpiry() for that!
	 * @param string $t
	 * @return string
	 */
	function setCacheTime( $t ) {
		return wfSetVar( $this->mCacheTime, $t );
	}

	/**
	 * @since 1.23
	 * @return int|null Revision id, if any was set
	 */
	function getCacheRevisionId() {
		return $this->mCacheRevisionId;
	}

	/**
	 * @since 1.23
	 * @param int $id Revision id
	 */
	function setCacheRevisionId( $id ) {
		$this->mCacheRevisionId = $id;
	}

	/**
	 * Sets the number of seconds after which this object should expire.
	 * This value is used with the ParserCache.
	 * If called with a value greater than the value provided at any previous call,
	 * the new call has no effect. The value returned by getCacheExpiry is smaller
	 * or equal to the smallest number that was provided as an argument to
	 * updateCacheExpiry().
	 *
	 * @param int $seconds
	 */
	function updateCacheExpiry( $seconds ) {
		$seconds = (int)$seconds;

		if ( $this->mCacheExpiry === null || $this->mCacheExpiry > $seconds ) {
			$this->mCacheExpiry = $seconds;
		}

		// hack: set old-style marker for uncacheable entries.
		if ( $this->mCacheExpiry !== null && $this->mCacheExpiry <= 0 ) {
			$this->mCacheTime = -1;
		}
	}

	/**
	 * Returns the number of seconds after which this object should expire.
	 * This method is used by ParserCache to determine how long the ParserOutput can be cached.
	 * The timestamp of expiry can be calculated by adding getCacheExpiry() to getCacheTime().
	 * The value returned by getCacheExpiry is smaller or equal to the smallest number
	 * that was provided to a call of updateCacheExpiry(), and smaller or equal to the
	 * value of $wgParserCacheExpireTime.
	 * @return int|mixed|null
	 */
	function getCacheExpiry() {
		global $wgParserCacheExpireTime;

		if ( $this->mCacheTime < 0 ) {
			return 0;
		} // old-style marker for "not cachable"

		$expire = $this->mCacheExpiry;

		if ( $expire === null ) {
			$expire = $wgParserCacheExpireTime;
		} else {
			$expire = min( $expire, $wgParserCacheExpireTime );
		}

		if ( $this->containsOldMagic() ) { //compatibility hack
			$expire = min( $expire, 3600 ); # 1 hour
		}

		if ( $expire <= 0 ) {
			return 0; // not cachable
		} else {
			return $expire;
		}
	}

	/**
	 * @return bool
	 */
	function isCacheable() {
		return $this->getCacheExpiry() > 0;
	}

	/**
	 * Return true if this cached output object predates the global or
	 * per-article cache invalidation timestamps, or if it comes from
	 * an incompatible older version.
	 *
	 * @param string $touched The affected article's last touched timestamp
	 * @return bool
	 */
	public function expired( $touched ) {
		global $wgCacheEpoch;

		return !$this->isCacheable() // parser says it's uncacheable
			|| $this->getCacheTime() < $touched
			|| $this->getCacheTime() <= $wgCacheEpoch
			|| $this->getCacheTime() <
				wfTimestamp( TS_MW, time() - $this->getCacheExpiry() ) // expiry period has passed
			|| !isset( $this->mVersion )
			|| version_compare( $this->mVersion, Parser::VERSION, "lt" );
	}

	/**
	 * Return true if this cached output object is for a different revision of
	 * the page.
	 *
	 * @todo We always return false if $this->getCacheRevisionId() is null;
	 * this prevents invalidating the whole parser cache when this change is
	 * deployed. Someday that should probably be changed.
	 *
	 * @since 1.23
	 * @param int $id The affected article's current revision id
	 * @return bool
	 */
	public function isDifferentRevision( $id ) {
		$cached = $this->getCacheRevisionId();
		return $cached !== null && $id !== $cached;
	}
}
