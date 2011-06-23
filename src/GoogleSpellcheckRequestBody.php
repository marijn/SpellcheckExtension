<?php

  use SimpleXMLElement;

  class GoogleSpellcheckRequestBody
  {

    private $_text;
    private $_textAlreadyClipped;
    private $_ignoreDuplicates;
    private $_ignoreDigits;
    private $_ignoreAllCaps;

    public function __construct ($arg_text, $arg_textAlreadyClipped = FALSE, $arg_ignoreDuplicates = FALSE, $arg_ignoreDigits = TRUE, $arg_ignoreAllCaps = TRUE)
    {
      $this->_text               = $arg_text;
      $this->_textAlreadyClipped = $arg_textAlreadyClipped;
      $this->_ignoreDuplicates   = $arg_ignoreDuplicates;
      $this->_ignoreDigits       = $arg_ignoreDigits;
      $this->_ignoreAllCaps      = $arg_ignoreAllCaps;
    }

    public function toSimpleXMLElement ()
    {
      $element = new SimpleXMLElement("<spellrequest />");

      $element->addAttribute("textalreadyclipped", (integer) $this->_textAlreadyClipped);
      $element->addAttribute("ignoredups", (integer) $this->_ignoreDuplicates);
      $element->addAttribute("ignoredigits", (integer) $this->_ignoreDigits);
      $element->addAttribute("ignoreallcaps", (integer) $this->_ignoreAllCaps);
      $element->addChild("text", $this->_text);

      return $element;
    }

    public function __toString ()
    {
      return $this->toSimpleXMLElement()->asXML();
    }

  }
