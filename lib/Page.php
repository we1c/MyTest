<?php

class Page{
	
	private $total;
	private $pagesize;
	private $showpage;
	private $page;
	private $pages;
	private $startNum;
	private $endNum;
	private $url;
	private $call;

	function __construct($total,$pagesize,$showpage,$page,$script='/index.php',$params=array(),$call = null){
		$this->total=$total;
		$this->pagesize=$pagesize;
		$this->showpage=$showpage;
		$this->page=$page;
		$this->pages=ceil($total/$pagesize);
		$this->call=$call;

		$this->_makeNum();
		$this->_makeUrl( $script,$params );
	}
	
	private function _makeNum(){

		$this->showpage= ($this->showpage>=$this->pages) ? $this->pages : $this->showpage;
		$division1= (($this->showpage%2==0) ? ($this->showpage/2+1) : (($this->showpage+1)/2));
		$division2  = ($this->pages-($this->showpage-$division1));
		if($this->page<=$division1){
			$this->startNum=1;
			$this->endNum  =$this->showpage;
		}else if($this->page>$division1 && $this->page<=$division2){
			$this->startNum=($this->page-($division1-1));
			$this->endNum  =($this->page+($this->showpage-$division1));	
		}else if($this->page>$division2){
			$this->startNum=$this->pages-($this->showpage-1);
			$this->endNum  =$this->pages;
		}
	}

	private function _makeUrl( $script,$params ){

		$this->url=$script;
		$test=0;
		$tmp = '';
		foreach($params as $k=>$v){
			if(is_int($k)){
				$this->url.="/".$v;
			}else{
				$test=1;
				$tmp .="&$k=$v";
			}
		}
		$tmp = ltrim($tmp,'&');
		
		if($test==0){
			$this->url.='?';	//index.php/push/index?
		}else{
			/*str_replace(mixed $search,mixed $replace,mixed $subject [,int &$count])
			$count=1;
			$this->url=str_replace('&','?',$this->url,$count);	//index.php/push/index?type=123;*/
			$this->url .= '?'.$tmp;
		}
		
		$this->url .= '&pages='.$this->pages;

	}
	
	public function showPage(){
		//$pagesize;$showpage;$page;$pages;
		$uri = $this->url;
		$front = $this->page - 1 ;
		$next = $this->page + 1 ;

		$res = "";

		$res .= "<div>{$this->page}页/{$this->pages}页</div>";

		$res .= "<a href='".$uri."&page=1' class='auto'>首页</a>";

		if( $this->page != 1 ){
			$res .= "<a href='".$uri."&page=$front' class='auto'>上一页</a>";
		}

		for( $i=$this->startNum;$i<=$this->endNum;$i++){
			if(  $i == $this->page ){
				$res .= "<span href='".$uri."&page=".$i."'>$i</span>";
			}else{
				$res .= "<a href='".$uri."&page=".$i."'>".$i."</a>";
			}
		}

		if( $this->page != $this->pages ){
			$res .="<a href='".$uri."&page=$next' class='auto'>下一页</a>";
		}

		$res .= "<a href='".$uri."&page=$this->pages' class='auto'>末页</a>";
		
		return $res;

	}

	/*public function showPage(){
		$res="";
		if($this->total==0){$this->pagesize=0;}
		$res.="共有{$this->total}条记录,每页显示{$this->pagesize}条记录,<br><br><a href='javascript:".$this->call."(\"".$this->url."&page=1\")'>[首页]</a>";

		for($i=$this->startNum;$i<=$this->endNum;$i++){
			if($i==$this->page){
				$res.="<span style='padding:2px 10px;width:10px;height:10px' href=".$this->url."&page='$i'>$i</span>";
			}else{
				$res.="<a id='page$i' style='padding:2px 10px;margin:0px 5px;border:1px solid #ccc;text-decoration:none;width:10px;height:10px' href='javascript:".$this->call."(\"".$this->url."&page=$i\")' onmouseover='overShow(this.id)' onmouseout='outShow(this.id)' >$i</a>";
			}
		}

		$res.="<a href='javascript:".$this->call."(\"".$this->url."&page={$this->pages}\")'>[末页]</a>";

		$res.="<script>function overShow(id){document.getElementById(id).style.border='1px solid blue'}function outShow(id){document.getElementById(id).style.border='1px solid #ccc'}</script>";

		return $res;
	}*/




}