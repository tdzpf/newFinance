<?php
class IndexController extends Tw_Controller
{
    public function indexAction()
    {
        echo 'Index@', time();
    }

    public function testAction()
    {
        $this->response->alert('Te"st');
        echo 'Test@', time();
    }

    public function getAction()
    {
        $model = $this->model('Index');
	$list = $model->get();
	$this->json($list,'var_name');
    }
    public function addAction()
    {
        $model = $this->model('Index');
	$uin = $this->request->get('uin');
	$devid = $this->request->get('devid');
	$bret = $model->add($uin);
	$this->json($bret);    
}
    
    public function delAction()
    {
        $model = $this->model('Index');
	$uin = $this->request->get('uin');
	$bret = $model->del($uin);
	$this->json($bret);    
	
}
	public function smartyAction() {

	$smarty = $this->smarty('smarty');
	$smarty->assign('hello', "test");
	$smarty->display('hello.tpl');
}
    public function setCacheAction(){
	$key = $this->request->get('key');
	$value = $this->request->get('value');
	$model = $this->model('Index');
	 $bRet = $model->setCache($key,$value);
	var_dump($bRet);
    }

    public function getCacheAction(){
	$key = $this->request->get('key');
	$model = $this->model('Index');
	$res = $model->getCache($key);	
	var_dump($res);
    }


    public function viewAction()
    {
        //$this->view = Cola_View::Standard(array('basePath' => 'views'));
        $id = $this->request->param('id');
        $this->view->id = $id;
        $this->view->name = '伟大领袖毛主席无限正确!';
        $this->response->charset();
        $this->display();
    }


    public function showAction()
    {
        var_dump($this->get('c'));
    }

}
?>
