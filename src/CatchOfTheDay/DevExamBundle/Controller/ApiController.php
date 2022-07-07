<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/10/19
 * Time: 8:23 PM
 */
namespace CatchOfTheDay\DevExamBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use CatchOfTheDay\DevExamBundle\Model\TodoListItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use CatchOfTheDay\DevExamBundle\lib\Paginator;
use CatchOfTheDay\DevExamBundle\lib\ContainsAlphanumeric;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApiController extends FOSRestController
{
    /**
     * @Rest\View
     * @Route("/", defaults={"page" = 1,"rpp" = 5,"status" ="all"} , name="api_page_home")
     * @Route("/page/{page}/{rpp}/", defaults={"status" ="all"})
     * @Route("/page/{page}/{rpp}/{status}", name="index")
     * @Route("/page/{status}", defaults={"page" = 1,"rpp" = 5}, name="api_page_status" )
     * @Method({"GET", "OPTIONS"})
     */
    public function getAllTodosAction($page,$rpp,$status)
    {
        return $this->getAllTodos($page,$rpp,$status);
    }

    private function getAllTodos($page,$rpp,$status){
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        uasort($items, function ($a, $b) {return $b->getCreated() <=> $a->getCreated();});
        $filteredArray = array_filter($items, function($obj) use ($status){
            if($status == 'all')
                return  true;

            else if($status == 'completed')
                return  $obj->getComplete();

            else if($status == 'pending')
                return  !$obj->getComplete();
        });

        $slicedArray = array_slice($filteredArray, ($page-1)*$rpp, $rpp);
        $paginator = new Paginator($page, count($filteredArray), $rpp,"/page");

        $pagelist = $paginator->getPagesList();

        return  array('items' => $slicedArray, 'paginator' => $pagelist,'ind'=>($page-1)*$rpp);
    }


    /**
     * @Rest\View
     * @Route("/add", name="api_root_post")
     * @Method({"POST"})
     * @ParamConverter("todo", converter="fos_rest.request_body")
     */
    public function addAction(TodoListItem $todo)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        $message = array('notice'=>'Error something went wrong!', 'type'=>'error');
        $validator = Validation::createValidator();
        $violations = $validator->validate($todo->getText(), array(
            new Length(array('min' => 5,'max' => 200)),
            new NotBlank(),
            new ContainsAlphanumeric(),
        ));
        $errors = [];
        if (0 !== count($violations)) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            $message['notice'] = implode('<br> ', $errors);
            return  array('message' =>$message);
        }
        else{
            $newTodo =new TodoListItem();
            $items[] =  $newTodo->setText($todo->getText());
            $message['notice'] = 'Todo has been added!';
            $message['type'] = 'success';
            $manager->write($items);
            return  array('message' =>$message,'todos' => $this->getAllTodos(1,5, 'all'));
        }

    }


    /**
     * @Rest\View
     * @Route("/items/{itemId}/edit/page/{page}/{rpp}/{status}", name="api_edit_todo")
     * @Method({"POST"})
     * @ParamConverter("todo", converter="fos_rest.request_body")
     */
    public function editAction(TodoListItem $todo,$itemId,$page,$rpp, $status)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        $validator = Validation::createValidator();
        $message = array('notice'=>'Error something went wrong!', 'type'=>'error');
        $violations = $validator->validate($todo->getText(), array(
            new Length(array('min' => 5,'max' => 200)),
            new NotBlank(),
            new ContainsAlphanumeric(),
        ));
        $errors = [];
        if (0 !== count($violations)) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            $message['notice'] = implode('<br> ', $errors);
            return  array('message' =>$message);
        }
        else{
            foreach ($items as &$item)
            {
                if($item->getId() == $itemId ){
                    $item->setText($todo->getText());
                    $message['notice'] = 'Todo has been updated!';
                    $message['type'] = 'success';
                }
            }
            $manager->write($items);
            return  array('message' =>$message,'todos' => $this->getAllTodos($page,$rpp, $status));
        }

    }


    /**
     * @Rest\View
     * @Route("/items/{itemId}/mark-as-complete/page/{page}/{rpp}/{status}", name="api_completed_todo")
     * @Method({"GET"})
     */
    public function markAsCompleteAction($itemId,$page,$rpp,$status)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        $message = array('notice'=>'Error something went wrong!', 'type'=>'error');
            foreach ($items as &$item)
            {
                if($item->getId() == $itemId ){
                    if($item->getComplete())
                        $message['notice'] = 'Todo has been  comleted !';
                    else
                        $message['notice'] = 'Todo has been  pending !';
                    $message['type'] = 'success';
                    $item->setComplete(!$item->getComplete());
                    break;
                }
            }
            $manager->write($items);
            return  array('message' =>$message,'todos' => $this->getAllTodos($page,$rpp, $status));
    }


    /**
     * @Rest\View
     * @Route("/items/{itemId}/delete/page/{page}/{rpp}/{status}", name="api_delete_todo")
     * @Method({"DELETE"})
     */
    public function detailsAction($itemId,$page,$rpp,$status)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        $message = array('notice'=>'Error something went wrong!', 'type'=>'error');
        foreach ($items as $index => $item)
        {
            if($item->getId() == $itemId ){
                array_splice($items, $index, 1);
                $message['notice'] = 'Todo has been deleted!';
                $message['type'] = 'success';
                break;
            }

        }
        $manager->write($items);
        return  array('message' =>$message,'todos' => $this->getAllTodos($page,$rpp, $status));
    }
}
