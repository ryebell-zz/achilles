<?php

class Readable_DateTime extends DateTime {

    public function __toString()
    {
        return $this->format('m-d-Y H:i:s');
  }
}

?>
