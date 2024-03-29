<?php


namespace App\Table;

use App\Model\Post;
use App\PaginatedQuery;

final class PostTable extends Table
{

    protected $table = "post";
    protected $class = Post::class;

    public function createPost(Post $post)
    {
        $id = $this->create(
            [
                'name' => $post->getName(),
                'slug' => $post->getSlug(),
                'content' => $post->getContent(),
                'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
        $post->setId($id);

    }

    public function updatePost(Post $post)
    {
        $this->update([
            'id' => $post->getID(),
            'name' => $post->getName(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s')
        ], $post->getID());

    }

    public function attachCategories(int $id, array $categories)
    {
        $this->pdo->exec('DELETE FROM post_category WHERE post_id = ' . $id);
        $query = $this->pdo->prepare('INSERT INTO post_category SET post_id = ?, category_id = ?');
        foreach ($categories as $category) {
            $query->execute([$id, $category]);
        }
    }

    public function findPaginated()
    {
        $paginatedQuery = new PaginatedQuery(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC",
            "SELECT COUNT(id) FROM {$this->table}",
            $this->pdo
        );
        $posts = $paginatedQuery->getItems(Post::class);
        (new CategoryTable($this->pdo))->hydratePosts($posts);
        return [$posts, $paginatedQuery];
    }

    public function findPaginatedForCategory($categoryID)
    {
        $paginatedQuery = new PaginatedQuery("
                        SELECT p.* 
                        FROM {$this->table} p
                        JOIN post_category pc ON pc.post_id = p.id
                        WHERE pc.category_id = {$categoryID} 
                        ORDER BY created_at DESC",
            "SELECT COUNT(category_id) FROM post_category WHERE category_id =  {$categoryID}"
        );
        $posts = $paginatedQuery->getItems(Post::class);
        (new CategoryTable($this->pdo))->hydratePosts($posts);
        return [$posts, $paginatedQuery];
    }


}