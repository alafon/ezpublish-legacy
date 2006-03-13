<?php
//
// Definition of eZDateTimeType class
//
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.5.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*!
  \class eZDateTimeType ezdatetimetype.php
  \ingroup eZDatatype
  \brief Stores a date and time value

*/

include_once( "kernel/classes/ezdatatype.php" );
include_once( "lib/ezlocale/classes/ezdatetime.php" );

define( 'EZ_DATATYPESTRING_DATETIME', 'ezdatetime' );
define( 'EZ_DATATYPESTRING_DATETIME_DEFAULT', 'data_int1' );
define( 'EZ_DATATYPESTRING_DATETIME_ADJUSTMENT_FIELD', 'data_text5' );
define( 'EZ_DATATYPESTRING_DATETIME_DEFAULT_EMTPY', 0 );
define( 'EZ_DATATYPESTRING_DATETIME_DEFAULT_CURRENT_DATE', 1 );
define( 'EZ_DATATYPESTRING_DATETIME_DEFAULT_ADJUSTMENT', 2 );


class eZDateTimeType extends eZDataType
{
    function eZDateTimeType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_DATETIME, ezi18n( 'kernel/classes/datatypes', "Datetime field", 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
    }

    /*!
     Private method only for use inside this class
    */
    function validateDateTimeHTTPInput( $day, $month, $year, $hour, $minute, &$contentObjectAttribute )
    {
        include_once( 'lib/ezutils/classes/ezdatetimevalidator.php' );

        $state = eZDateTimeValidator::validateDate( $day, $month, $year );
        if ( $state == EZ_INPUT_VALIDATOR_STATE_INVALID )
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                 'Date is not valid.' ) );
            return EZ_INPUT_VALIDATOR_STATE_INVALID;
        }

        $state = eZDateTimeValidator::validateTime( $hour, $minute );
        if ( $state == EZ_INPUT_VALIDATOR_STATE_INVALID )
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                 'Time is not valid.' ) );
            return EZ_INPUT_VALIDATOR_STATE_INVALID;
        }
        return $state;
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $year   =& $http->postVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) );
            $month  =& $http->postVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) );
            $day    =& $http->postVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) );
            $hour   =& $http->postVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) );
            $minute =& $http->postVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) );
            $classAttribute =& $contentObjectAttribute->contentClassAttribute();

            if ( $year == '' or
                 $month == '' or
                 $day == '' or
                 $hour == '' or
                 $minute == '' )
            {
                if ( !( $year == '' and
                        $month == '' and
                        $day == '' and
                        $hour == '' and
                        $minute == '') or
                     $contentObjectAttribute->validateIsRequired() )
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                         'Missing datetime input.' ) );
                    return EZ_INPUT_VALIDATOR_STATE_INVALID;
                }
                else
                    return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
            }
            else
            {
                return $this->validateDateTimeHTTPInput( $day, $month, $year, $hour, $minute, $contentObjectAttribute );
            }
        }
        else
            return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $year   =& $http->postVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) );
            $month  =& $http->postVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) );
            $day    =& $http->postVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) );
            $hour   =& $http->postVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) );
            $minute =& $http->postVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) );

            $dateTime = new eZDateTime();
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            if ( ( $year == '' and $month == ''and $day == '' and
                   $hour == '' and $minute == '' ) or
                 !checkdate( $month, $day, $year ) or $year < 1970 )
            {
                    $dateTime->setTimeStamp( 0 );
            }
            else
            {
                $dateTime->setMDYHMS( $month, $day, $year, $hour, $minute, 0 );
            }

            $contentObjectAttribute->setAttribute( 'data_int', $dateTime->timeStamp() );
            return true;
        }
        return false;
    }

    /*!
     Returns the content.
    */
    function &objectAttributeContent( &$contentObjectAttribute )
    {
        $dateTime = new eZDateTime();
        $stamp = $contentObjectAttribute->attribute( 'data_int' );
        $dateTime->setTimeStamp( $stamp );
        return $dateTime;
    }

    /*!
     \reimp
    */
    function isIndexable()
    {
        return true;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_int' );
    }

    /*!
     Set class attribute value for template version
    */
    function initializeClassAttribute( &$classAttribute )
    {
        if ( $classAttribute->attribute( EZ_DATATYPESTRING_DATETIME_DEFAULT ) == null )
            $classAttribute->setAttribute( EZ_DATATYPESTRING_DATETIME_DEFAULT, 0 );
        $classAttribute->store();
    }

    function &parseXML( $xmlText )
    {
        include_once( 'lib/ezxml/classes/ezxml.php' );
        $xml = new eZXML();
        $dom =& $xml->domTree( $xmlText );
        return $dom;
    }

    function &classAttributeContent( &$classAttribute )
    {
        $xmlText = $classAttribute->attribute( 'data_text5' );
        if ( trim( $xmlText ) == '' )
            return eZDateTimeType::defaultClassAttributeContent();
        $doc =& eZDateTimeType::parseXML( $xmlText );
        $root =& $doc->root();
        $type = $root->elementByName( 'year' );
        if ( $type )
        {
            $content['year'] = $type->attributeValue( 'value' );
        }
        $type = $root->elementByName( 'month' );
        if ( $type )
        {
            $content['month'] = $type->attributeValue( 'value' );
        }
        $type = $root->elementByName( 'day' );
        if ( $type )
        {
            $content['day'] = $type->attributeValue( 'value' );
        }
        $type = $root->elementByName( 'hour' );
        if ( $type )
        {
            $content['hour'] = $type->attributeValue( 'value' );
        }
        $type = $root->elementByName( 'minute' );
        if ( $type )
        {
            $content['minute'] = $type->attributeValue( 'value' );
        }
        return $content;
    }

    function &defaultClassAttributeContent()
    {
        return array( 'year' => '',
                      'month' => '',
                      'day' => '',
                      'hour' => '',
                      'minute' => '' );
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( &$contentObjectAttribute, $currentVersion, &$originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $dataInt = $originalContentObjectAttribute->attribute( "data_int" );
            $contentObjectAttribute->setAttribute( "data_int", $dataInt );
        }
        else
        {
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            $defaultType = $contentClassAttribute->attribute( EZ_DATATYPESTRING_DATETIME_DEFAULT );
            if ( $defaultType == EZ_DATATYPESTRING_DATETIME_DEFAULT_CURRENT_DATE )
            {
                $contentObjectAttribute->setAttribute( "data_int", mktime() );
            }
            else if ( $defaultType == EZ_DATATYPESTRING_DATETIME_DEFAULT_ADJUSTMENT )
            {
                $adjustments = eZDateTimeType::classAttributeContent( $contentClassAttribute );
                $value = new eZDateTime();
                $value->adjustDateTime( $adjustments['hour'], $adjustments['minute'], 0, $adjustments['month'], $adjustments['day'], $adjustments['year'] );
                $contentObjectAttribute->setAttribute( "data_int", $value->timeStamp() );
            }
        }
    }

    function fetchClassAttributeHTTPInput( &$http, $base, &$classAttribute )
    {
        $default = $base . "_ezdatetime_default_" . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $default ) )
        {
            $defaultValue = $http->postVariable( $default );
            $classAttribute->setAttribute( EZ_DATATYPESTRING_DATETIME_DEFAULT,  $defaultValue );
            if ( $defaultValue == EZ_DATATYPESTRING_DATETIME_DEFAULT_ADJUSTMENT )
            {
                $doc = new eZDOMDocument( 'DateTimeAdjustments' );
                $root =& $doc->createElementNode( 'adjustment' );
                $contentList = eZDateTimeType::contentObjectArrayXMLMap();
                foreach ( $contentList as $key => $value )
                {
                    $postValue = $http->postVariable( $base . '_ezdatetime_' . $value . '_' . $classAttribute->attribute( 'id' ) );
                    $elementType =& $doc->createElementNode( $key, array( 'value' => $postValue ) );
                    $root->appendChild( $elementType );
                }
                $doc->setRoot( $root );
                $docText =& $doc->toString();
                $classAttribute->setAttribute( EZ_DATATYPESTRING_DATETIME_ADJUSTMENT_FIELD , $docText );
            }
        }
        return true;
    }

    function contentObjectArrayXMLMap()
    {
        return array( 'year' => 'year',
                      'month' => 'month',
                      'day' => 'day',
                      'hour' => 'hour',
                      'minute' => 'minute' );
    }


    /*!
     Returns the date.
    */
    function title( &$contentObjectAttribute )
    {
        $locale =& eZLocale::instance();
        $retVal = $contentObjectAttribute->attribute( "data_int" ) == 0 ? '' : $locale->formatDateTime( $contentObjectAttribute->attribute( "data_int" ) );
        return $retVal;
    }

    function hasObjectAttributeContent( &$contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_int" ) != 0;
    }

    /*!
     \reimp
    */
    function &sortKey( &$contentObjectAttribute )
    {
        return (int)$contentObjectAttribute->attribute( 'data_int' );
    }

        /*!
     \reimp
    */
    function &sortKeyType()
    {
        return 'int';
    }

    /*!
     \reimp
    */
    function &serializeContentClassAttribute( &$classAttribute, &$attributeNode, &$attributeParametersNode )
    {
        $defaultValue = $classAttribute->attribute( EZ_DATATYPESTRING_DATETIME_DEFAULT );
        $adjustValue = $classAttribute->attribute( EZ_DATATYPESTRING_DATETIME_ADJUSTMENT_FIELD );
        switch ( $defaultValue )
        {
            case EZ_DATATYPESTRING_DATETIME_DEFAULT_EMTPY:
            {
                $attributeParametersNode->appendChild( eZDOMDocument::createElementNode( 'default-value',
                                                                                         array( 'type' => 'empty' ) ) );
            } break;
            case EZ_DATATYPESTRING_DATETIME_DEFAULT_CURRENT_DATE:
            {
                $attributeParametersNode->appendChild( eZDOMDocument::createElementNode( 'default-value',
                                                                                         array( 'type' => 'current-date' ) ) );
            } break;
        }
    }

    /*!
     \reimp
    */
    function &unserializeContentClassAttribute( &$classAttribute, &$attributeNode, &$attributeParametersNode )
    {
        $defaultNode =& $attributeParametersNode->elementByName( 'default-value' );
        $defaultValue = strtolower( $defaultNode->attributeValue( 'type' ) );
        switch ( $defaultValue )
        {
            case 'empty':
            {
                $classAttribute->setAttribute( EZ_DATATYPESTRING_DATE_DEFAULT, EZ_DATATYPESTRING_DATE_DEFAULT_EMTPY );
            } break;
            case 'current-date':
            {
                $classAttribute->setAttribute( EZ_DATATYPESTRING_DATE_DEFAULT, EZ_DATATYPESTRING_DATE_DEFAULT_CURRENT_DATE );
            } break;
        }
    }
}

eZDataType::register( EZ_DATATYPESTRING_DATETIME, "ezdatetimetype" );

?>
