<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/10/19
 * Time: 1:04 PM
 */
namespace CatchOfTheDay\DevExamBundle\lib;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsAlphanumeric extends Constraint
{
    public $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters or numbers and spaces.';
}