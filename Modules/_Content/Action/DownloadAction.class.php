<?php
class DownloadAction extends AdminAction {
    function _initialize() {
        parent::_initialize();
        $this->dDownloads = D("Download");
        $this->dTerms = D("Term");
    }
    public function index(){
        if($_SESSION['ADMIN_ID']==1){
            $select_data = R('Term/getChild',array(true,0,1));
        }else{
            $select_data = $this->gettermlist();
            $where['term_id'] = array('in',$_SESSION['ADMIN_ROLE']);
        }
        $data = $this->dDownloads->where($where)->limit(100)->select();
        $this->assign("select_data",$select_data);
        $this->assign('data',$data);
    	$this->display();
    }
    public function gettermlist(){
        $where['id'] = array('in',$_SESSION['ADMIN_ROLE']);
        $where['type'] = 4;
        $data= D("Term")->where($where)->select();
        $this->assign('data',$data);
        $content = $this->fetch('Download:get_termlist');
        return $content;
    }
    public function listorder(){
        $status = $this->dDownloads->listorders($_POST['listorders']);
        if($status!==false){
            $this->success("排序成功！");
        }else{
            $this->error("排序失败！");
        }
    }
    public function add(){
        $select_data = R('Term/getChild',array(true,0,1));
        $this->assign("select_data",$select_data);
        $this->display();
    }
    public function getChild(){
        $parent_id   = I('post.parent_id',"0","int");
        $select_data = R('Term/getChild',array(true, $parent_id, 1));
        $this->ajaxReturn($select_data);
    }
    public function postAdd(){
        $_POST['type'] = 4;
        if(IS_POST){
            $_POST['auther'] = $_SESSION['ADMIN_NAME'];
            $this->dDownloads->create();
            $data = $this->dDownloads->add();
            if ($data) {
                $this->success("添加成功！",U('_Content/Download/index'));
            } else {
                $this->error("添加失败！",U('_Content/Download/add'));
            }
        }
    }
    public function getList($pagesize = 10){
        $term_id = I("request.term_id","0","int");
        if($term_id != 0){
            $rs = $this->dDownloads->getPage(array("pagesize"=>$pagesize,"term_id"=>$term_id));
            $page = $rs->pageShow;
            $getList = $rs->getList();

            $this->assign("data",$getList);
            $content = $this->fetch();

            $ajaxData["data"] = $content;
            $ajaxData["page"] = $page;

            $this->ajaxReturn($ajaxData);
        }else {
            return "";
        }
    }
    public function edit(){
        $id             = I('get.id',"0","int");
        $info           = $this->dDownloads->find($id);
        $depthArray     = D("Term")->read($info['term_id'])->depth()->depth;


        foreach (array_reverse($depthArray) as $value) {
            $_POST["parent_id"] = $value;
            $_POST["term_select"] = 1;
            $content = R('Term/getChild',array(true));
            $select_content .= $content;
        }
        $depthArray = array_reverse($depthArray);
        $depthArray[] = $info['term_id'];
        $this->assign("select_data",$select_content);
        $this->assign("parents",json_encode($depthArray));
        $this->assign('info',$info);
        $this->display();
    }
    public function postEdit(){
        $a = $this->dDownloads->create();
        $rs = $this->dDownloads->save();
        if ($rs) {
            $this->success("保存成功！",U('_Content/Download/index'));
        } else {
            $this->error("保存失败！",U('_Content/Download/add'));
        }
    }
    public function postDelete(){
        $id   = I('post.id',"0","int");
        $content = $this->dDownloads->where(array("id"=>$id))->delete() ? "删除成功" : "删除失败";
        $this->ajaxReturn($content);
    }
}
