<?php
  /*
    Serialized Replace
    Replace all occurrences in variable with replacement.
    Variable can be serialized any times (even 0). Variable's
    components can be also serialized any times (keys and
    values in array). Support of regular expression replace
    and normal string replace. Objects aren't supported.

    Copyright (C) 2014 Tomáš Beluský <tomasbelusky@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
  */

  if(!class_exists('SerializedReplace')) {
    class SerializedReplace {
      private $actual;        // actual index in $counts array
      private $counts;        // count of serializing for each component
      private $unserialized;  // unserialized version
      private $pattern;       // actual pattern
      private $replacement;   // actual replacement
      private $function;      // callback function

      /**
       * Constructor: unserialize given variable
       *  @param var: variable
       */
      public function __construct($var) {
        $this->actual = 0;
        $this->counts = array();
        $this->unserialized = $this->arrayUnserialize($var);
      }

      /**
       * Unserialize variable as array and save count of serializing
       *  @param mixed: variable to unserialized
       *  @return unserialized version of variable
       */
      private function arrayUnserialize($mixed) {
        $this->actual++;
        $this->counts[$this->actual] = 0;
        $mixed = $this->unserialize($mixed);

        if(is_array($mixed)) {
          foreach($mixed as $key => $value) { // unserialize also keys and values
            $newkey = $this->arrayUnserialize($key);
            unset($mixed[$key]);
            $mixed[$newkey] = $this->arrayUnserialize($value);
          }
        }

        return $mixed;
      }

      /**
       * Unserialize variable
       *  @param var: variable to unserialize
       *  @return unserialized version of variable
       */
      private function unserialize($var) {
        $ustr = @unserialize($var);

        if($ustr !== false) {
          $this->counts[$this->actual]++;
          return $this->unserialize($ustr);
        }
        else {
          return $var;
        }
      }

      /**
       * Serialize variable as array
       *  @param mixed: variable to serialize
       *  @return serialized version of variable
       */
      private function arraySerialize($mixed) {
        if(is_array($mixed)) {
          $bckp = $this->actual;

          foreach($mixed as $key => $value) { // serialize also keys and values
            $this->actual++;
            $newkey = $this->arraySerialize($key);
            unset($mixed[$key]);
            $this->actual++;
            $mixed[$newkey] = $this->arraySerialize($value);
          }

          $this->actual = $bckp;
        }

        $ret = $this->serialize($mixed);
        return $ret;
      }

      /**
       * Serialize variable appropriate counts
       *  @param var: variable to serialize
       *  @return serialized version of variable
       */
      private function serialize($var) {
        $serialized = $var;

        for($i = 0; $i < $this->counts[$this->actual]; $i++) {
          $serialized = serialize($serialized);
        }

        return $serialized;
      }

      /**
       * Replace pattern in given variable
       *  @param var: subject for replacing function
       *  @return variable with replaced pattern occurrences
       */
      private function doReplace($var) {
        if(is_array($var)) {
          foreach($var as $oldkey => $value) { // do replace even in keys and values
            $newkey = $this->doReplace($oldkey);
            unset($var[$oldkey]);
            $var[$newkey] = $this->doReplace($value);
          }
        }
        else { // do actual replace
          $var = call_user_func($this->function, $this->pattern, $this->replacement, $var);
        }

        return $var;
      }

      /**
       * Initialize class variables and then do replace
       *  @param pattern: pattern to be replaced
       *  @param replacement: replacement of pattern
       *  @param preg: use preg_match or str_replace
       */
      public function replace($pattern, $replacement, $preg=true) {
        $this->pattern = $pattern;
        $this->replacement = $replacement;
        $this->function = $preg ? 'preg_replace' : 'str_replace';
        $this->unserialized = $this->doReplace($this->unserialized);
      }

      /**
       * Get replaced and serialized variable
       *  @return serialized variable
       */
      public function get() {
        $this->actual = 1;
        return $this->arraySerialize($this->unserialized);
      }
    }
  }
?>
