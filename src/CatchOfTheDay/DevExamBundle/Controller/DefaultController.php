<?php

namespace CatchOfTheDay\DevExamBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use CatchOfTheDay\DevExamBundle\Model\TodoListItem;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use CatchOfTheDay\DevExamBundle\lib\Paginator;
use CatchOfTheDay\DevExamBundle\lib\ContainsAlphanumeric;

class DefaultController extends Controller
{
    /**
     * @Route("/", defaults={"page" = 1,"rpp" = 5,"status" ="all"} , name="page_home")
     * @Route("/page/{page}/{rpp}/", defaults={"status" ="all"})
     * @Route("/page/{page}/{rpp}/{status}", name="index")
     * @Route("/page/{status}", defaults={"page" = 1,"rpp" = 5}, name="page_status" )
     * @Template
     *
     * @return array
     */
    public function indexAction($page,$rpp,$status)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        uasort($items, function ($a, $b) {return $b->getCreated() <=> $a->getCreated();});
        $filteredArray = array_filter($items, function($obj) use ($status){
            if($status == 'all')
            return  !$obj->getComplete();

            else if($status == 'completed')
            return  $obj->getComplete();
        });

        $slicedArray = array_slice($filteredArray, ($page-1)*$rpp, $rpp);
        $paginator = new Paginator($page, count($filteredArray), $rpp,"/page");

        $pagelist = $paginator->getPagesList();

        if($status == 'all')
            return $this->render('CatchOfTheDayDevExamBundle:todo:index.html.twig', array('items' => $slicedArray, 'paginator' => $pagelist,'ind'=>($page-1)*$rpp));

        if($status == 'completed')
            return $this->render('CatchOfTheDayDevExamBundle:todo:completed.html.twig', array('items' => $slicedArray, 'paginator' => $pagelist,'ind'=>($page-1)*$rpp));
    }


    /**
     * @Route("/ajax" , name="page_home_ajax")
     * @Template
     *
     * @return array
     */
    public function indexAjaxAction()
    {
            return $this->render('CatchOfTheDayDevExamBundle:todo:ajax.html.twig');
    }


    /**
     * @Route("/add", name="add")
     * @Method("POST")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        $validator = Validation::createValidator();
        $violations = $validator->validate($request->get('todo-text'), array(
            new Length(array('min' => 5,'max' => 200)),
            new NotBlank(),new ContainsAlphanumeric(),
        ));

        if (0 !== count($violations)) {
            foreach ($violations as $violation) {
                $this->addFlash('error',$violation->getMessage());
            }
        }
        else{
            $todo =new TodoListItem();

            $items[] =  $todo->setText($request->get('todo-text'));
            $manager->write($items);
            $this->addFlash('notice','Todo Added');
        }

        return $this->redirect('/', 301);
    }

    /**
     * @Route("/items/{itemId}/mark-as-complete", name="mark_as_complete")
     *
     * @param Request $request
     * @param string $itemId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function markAsCompleteAction(Request $request, $itemId)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        foreach ($items as $item)
        {
            if($item->getId() == $itemId ){
               $item->setComplete(true);
               $this->addFlash('notice','Todo Updated');
            }
            $manager->write($items);
        }
        return $this->redirect('/', 301);
    }



    /**
     * @Route("/items/{itemId}/edit", name="edit_todo")
     */
    public function editAction(Request $request,$itemId)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        $todo = "";
        foreach ($items as $item)
        {
            if($item->getId() == $itemId ){
                $todo = $item;
            }
        }

        if ($request->isMethod('POST')) {
            $validator = Validation::createValidator();
            $violations = $validator->validate($request->get('todo-text'), array(
                new Length(array('min' => 5,'max' => 200)),
                new NotBlank(),new ContainsAlphanumeric(),
            ));

            if (0 !== count($violations)) {
                foreach ($violations as $violation) {
                    $this->addFlash('error',$violation->getMessage());
                }
                return $this->render('CatchOfTheDayDevExamBundle:todo:edit.html.twig',array('todo'=>$todo));
            }
            else {
                $todo->setText($request->get('todo-text'));
                $this->addFlash('notice','todo updated');
                $manager->write($items);
                return $this->render('CatchOfTheDayDevExamBundle:todo:edit.html.twig',array('todo'=>$todo));
            }
        }
        else {

            return $this->render('CatchOfTheDayDevExamBundle:todo:edit.html.twig',array('todo'=>$todo));
        }
    }

    /**
     * @Route("/items/{itemId}/delete", name="delete_todo")
     */
    public function detailsAction(Request $request,$itemId)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.manager.todo_list');
        $items = $manager->read();
        foreach ($items as $index => $item)
        {
            if($item->getId() == $itemId ){
                array_splice($items, $index, 1);
                $this->addFlash('notice','Todo Deleted');
                break;
            }
        }
        $manager->write($items);
        return $this->redirect('/', 301);
    }

    /**
     * @Route("/test", name="test_todo")
     */
    public function testAction(Request $request)
    {
        $manager = $this->get('catch_of_the_day_dev_exam.test');
        $manager->testMethod("passed data");
        var_dump($manager->testMethod("passed data")); die;
        return  array('items' => 'test data');
    }
}
