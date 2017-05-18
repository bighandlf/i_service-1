<?php
/**
 * 集团新闻转换器类
 */
class Convertor_GroupNews extends Convertor_Base {

	public function __construct() {
		parent::__construct ();
	}

	/**
	 * 新闻列表
	 * 
	 * @param array $newsList
	 *        	新闻列表
	 * @param array $tagList
	 *        	新闻标签
	 * @param int $newsCount
	 *        	新闻总数
	 * @param array $param
	 *        	扩展参数
	 * @return array
	 */
	public function getNewsListConvertor($newsList, $tagList, $newsCount, $param) {
		$tagListNew = array ();
		foreach ( $tagList as $tag ) {
			$tagListNew [$tag ['id']] = $this->handlerMultiLang ( 'title', $tag );
		}
		$data = array ('list' => array () );
		foreach ( $newsList as $news ) {
			$newTemp = array ();
			$newTemp ['id'] = $news ['id'];
			$newTemp ['title'] = $this->handlerMultiLang ( 'title', $news );
			$newTemp ['article'] = Enum_Img::getPathByKeyAndType ( $this->handlerMultiLang ( 'article', $news ) );
            $newTemp ['pdf'] = $news['pdf'] ? Enum_Img::getPathByKeyAndType($news['pdf']) : '';
            $newTemp ['video'] = $news['video'] ? Enum_Img::getPathByKeyAndType($news['video']) : '';
			$newTemp ['tagId'] = $news ['tagid'];
			$newTemp ['tagName'] = $tagListNew [$newTemp ['tagId']];
			$newTemp ['createtime'] = $news ['createtime'];
			$newTemp ['updatetime'] = $news ['updatetime'];
			$data ['list'] [] = $newTemp;
		}
		$data ['total'] = $newsCount;
		$data ['page'] = $param ['page'];
		$data ['limit'] = $param ['limit'];
		$data ['nextPage'] = Util_Tools::getNextPage ( $data ['page'], $data ['limit'], $data ['total'] );
		return $data;
	}

	/**
	 * 后台新闻列表
	 * 
	 * @param array $newsList
	 *        	新闻列表
	 * @param array $tagList
	 *        	新闻标签
	 * @param int $newsCount
	 *        	新闻总数
	 * @param array $param
	 *        	扩展参数
	 * @return array
	 */
	public function getAdminNewsListConvertor($newsList, $tagList, $newsCount, $param) {
		$tagListNew = array ();
		foreach ( $tagList as $tag ) {
			$tagListNew [$tag ['id']] ['titleLang1'] = $tag ['title_lang1'];
			$tagListNew [$tag ['id']] ['titleLang2'] = $tag ['title_lang2'];
			$tagListNew [$tag ['id']] ['titleLang3'] = $tag ['title_lang3'];
		}
		$data = array ('list' => array () );
		foreach ( $newsList as $news ) {
			$newTemp = array ();
			$newTemp ['id'] = $news ['id'];
			$newTemp ['title_lang1'] = $news ['title_lang1'];
			$newTemp ['title_lang2'] = $news ['title_lang2'];
			$newTemp ['title_lang3'] = $news ['title_lang3'];
			$newTemp ['article_lang1'] = $news ['article_lang1'];
			$newTemp ['article_lang2'] = $news ['article_lang2'];
			$newTemp ['article_lang3'] = $news ['article_lang3'];
			$newTemp ['status'] = $news ['status'];
			$newTemp ['tagId'] = $news ['tagid'];
			$newTemp ['tagName_lang1'] = $tagListNew [$newTemp ['tagId']] ['titleLang1'];
			$newTemp ['tagName_lang2'] = $tagListNew [$newTemp ['tagId']] ['titleLang2'];
			$newTemp ['tagName_lang3'] = $tagListNew [$newTemp ['tagId']] ['titleLang3'];
            $newTemp ['sort'] = $news ['sort'];
            $newTemp ['pdf'] = $news ['pdf'];
            $newTemp ['video'] = $news ['video'];
			$newTemp ['createTime'] = $news ['createtime'];
			$newTemp ['updateTime'] = $news ['updatetime'];
			$data ['list'] [] = $newTemp;
		}
		$data ['total'] = $newsCount;
		$data ['page'] = $param ['page'];
		$data ['limit'] = $param ['limit'];
		$data ['nextPage'] = Util_Tools::getNextPage ( $data ['page'], $data ['limit'], $data ['total'] );
		return $data;
	}

	/**
	 * 新闻详情
	 * 
	 * @param array $list
	 *        	新闻详情
	 * @return array
	 */
	public function getNewsDetailConvertor($list) {
		$data = array ();
		$data ['id'] = $list ['id'];
		$data ['title'] = $this->handlerMultiLang ( 'title', $list );
		$data ['article'] = Enum_Img::getPathByKeyAndType ( $this->handlerMultiLang ( 'article', $list ) );
		$data ['createTime'] = $list ['createtime'];
		$data ['updateTime'] = $list ['updatetime'];
		$data ['status'] = $list ['status'];
		$data ['tagId'] = $list ['tagid'];
		return $data;
	}

	/**
	 * 后台新闻详情
	 * 
	 * @param array $list
	 *        	后台新闻详情
	 * @return array
	 */
	public function getAdminNewsDetailConvertor($list) {
		$data = array ();
		$data ['id'] = $list ['id'];
		$data ['title_lang1'] = $list ['title_lang1'];
		$data ['title_lang2'] = $list ['title_lang2'];
		$data ['title_lang3'] = $list ['title_lang3'];
		$data ['article_lang1'] = $list ['article_lang1'];
		$data ['article_lang2'] = $list ['article_lang2'];
		$data ['article_lang3'] = $list ['article_lang3'];
		$data ['tagId'] = $list ['tagid'];
		$data ['status'] = $list ['status'];
		$data ['createTime'] = $list ['createtime'];
		$data ['updateTime'] = $list ['updatetime'];
		return $data;
	}
}