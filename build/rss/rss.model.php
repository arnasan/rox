<?php
/** 
 * RSS model
 * 
 * @package rss
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class RssModel extends RoxModelBase
{
	
	protected $posts;

	public function getPosts() {
		return $this->posts;
	}
	
	/**
	 * All forum posts
	 * bw/htdocs/rss
	 */	
	public function getForumFeed()
	{
		$this->posts = $this->bulkLookup(
            "
SELECT
    *
FROM
    forums_posts    AS p,
    forums_threads  AS t
WHERE
    p.threadid = t.threadid
ORDER BY
    p.create_time DESC 
LIMIT 15
            "
        );
        
        // echo 'eselmilch<br><pre>'; print_r($this->posts); echo '</pre>';
        
        if ($this->posts == null) return false;
		return true;
	}	    
	
	/**
	 * Specific thread, e.g.
	 * rss/thread/2
	 * rss/thread/test
	 */
	public function getThreadFeed($thread)
	{
        if (!is_numeric($thread)) {
        	$condition = "t.title = '".$thread."' ";
        }
        else {
        	$condition = "p.threadId = ".$thread." ";
        }
        
		$query = (
            "
SELECT
    *
FROM
    forums_posts    AS p,
    forums_threads  AS t
WHERE
    $condition   AND
    p.threadId = t.threadid
ORDER BY
    p.create_time DESC 
LIMIT 15
            "
        );
        $this->posts = $this->bulkLookup($query);
        if ($this->posts == null) return false;
		return true;
	}	    
	
	 
	
	public function getBlogFeed() {
			$query =" 
SELECT
    *
FROM 
    blog AS b,
    blog_data AS bd
WHERE
    b.blog_id = bd.blog_id
ORDER BY
    bd.edited DESC
LIMIT 0, 30
";			
		
		$this->posts = $this->bulkLookup($query);
		//echo "<pre>model posts:";
		//print_r($this->posts);
		
		if ($this->posts == null) return false;
		return true;		
	}
	
	public function getBlogFeedByAuthor($author) {

		$query =" 
SELECT *
FROM `blog` as b, blog_data as bd, user as u
WHERE u.handle = '".$author."' 
AND u.id = b.user_id_foreign
AND b.blog_id = bd.blog_id
ORDER BY bd.edited DESC
";			
		$this->posts = $this->bulkLookup($query);
		if ($this->posts == null) return false;
		return true;		
	}
	

	public function getBlogFeedByTag($tag) {
	
		$condition = "";
		if (is_numeric($tag)) {
			$condition .= "bt.blog_tag_id = $tag";
		}
		else {
			$condition .= "bt.name = '".$tag."'";
		}
		

			$query =" 
SELECT *
FROM `blog` as b, `blog_data` as bd, `blog_to_tag` as btt, `blog_tags` as bt
WHERE $condition
AND b.blog_id = bd.blog_id
AND bd.blog_id = btt.blog_id_foreign
AND btt.blog_tag_id_foreign = bt.blog_tag_id
ORDER BY bd.edited DESC
";			
	
	
		$this->posts = $this->bulkLookup($query);
		if ($this->posts == null) return false;
		return true;
	} 
	 
	 
	/**
	 * Specific tag 
	 * rss/tag
	 * rss/tag/2
	 * rss/tag/Milk
	 */
	public function getTagFeed($tagname)
	{
	    if (is_numeric($tagname)) {
            $query =
                "
SELECT
    forums_posts.*,
    forums_threads.title,
    forums_tags.tagid     AS tagid,
    forums_tags.tag       AS tagname
FROM
    forums_posts,
    forums_threads,
    tags_threads,
    forums_tags
WHERE
    forums_tags.tagid     = '$tagname'          AND
    forums_tags.tagid     = tags_threads.IdTag  AND
    tags_threads.IdThread = forums_threads.id   AND
    forums_threads.id     = forums_posts.threadid
ORDER BY
    forums_posts.create_time DESC 
LIMIT 0,30
                "
            ;
	    } else if (empty($tagname)) {
	        return false;
	    } else {
            // tagname as string
            // TODO: evtl we don't need all of these tables?
	        $query =
                "
SELECT
    forums_posts.*,
    forums_threads.title,
    forums_tags.tagid    AS tagid,
    forums_tags.tag      AS tagname
FROM
    forums_posts,
    forums_threads,
    tags_threads,
    forums_tags 
WHERE
    forums_tags.tag = '$tagname'               AND
    forums_tags.tagid = tags_threads.IdTag     AND
    tags_threads.IdThread = forums_threads.id  AND
    forums_threads.id = forums_posts.threadid
ORDER BY
    forums_posts.create_time DESC 
                "
	        ;
	    }
	    $feed = '';
		$i = 1;
		$this->posts = $this->bulkLookup($query);
		if ($this->posts == null) return false;
		return true;
	}	
}
?>