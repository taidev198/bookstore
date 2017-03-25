<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Category;

use App\Author;

use App\Company;

use App\Book;
use App\Comment;
use Carbon\Carbon;

use  Illuminate\Pagination\Paginator;

use App\Slide;

use App\http\Controllers\CategoryController;

class HomeController extends Controller
{
    protected $categories;
    protected $categoriesChild;
    protected $authorsChild;
    protected $companyChild;
    protected $categoryAll;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        $this->categories=Category::getCategoriesByParentID(0);//lấy danh mục theo paarent id truyền vào
        $this->categoryAll=Category::getAll();//lấy toàn bộ danh mục
        foreach ($this->categories as $value) {
            $this->categoriesChild[$value->id]=Category::getCategoriesByParentID($value->id);
            $listCate=CategoryController::getAllIdCategories($this->categoryAll,$value->id);
            $this->authorsChild[$value->id]=Author::getTopAuthorInCategories($listCate);
            $this->companyChild[$value->id]=Company::getTopCompanyInCategories($listCate);
        }
    }


    public function index()
    {
        $slides=Slide::get();
        foreach ($this->categories as $value) {
            $listCate=CategoryController::getAllIdCategories($this->categoryAll,$value->id);
            $booksChild[$value->id]=Book::getAllBookByCategoryId($listCate);
        }
        return view('front-end.home',[
                        'categories'        =>$this->categories,
                        'categoriesChild'   =>$this->categoriesChild,
                        'companyChild'      =>$this->companyChild,
                        'authorsChild'      =>$this->authorsChild,
                        'booksChild'        =>$booksChild,
                        'slides'            =>$slides,
                    ]);
    }


    public function viewCategory($categoryId=0)
    {
        $categorySelect= Category::find($categoryId);
        if ($categorySelect==null)
            return ;
        $booksChild=array();
        $categoriesChildSelect=Category::getCategoriesByParentID($categorySelect->id);

        $listCate=CategoryController::getAllIdCategories(Category::getAll(),$categorySelect->id);
        if (count($categoriesChildSelect)>0)
            $booksChild=Book::getAllBookByCategoryId($listCate);
        else 
            $booksChild=Book::getAllBookByCategoryId(array($categorySelect->id));
        return view('front-end.category',[
                        'categories'            =>$this->categories,
                        'categoriesChild'       =>$this->categoriesChild,
                        'companyChild'          =>$this->companyChild,
                        'authorsChild'          =>$this->authorsChild,
                        'categorySelect'        =>$categorySelect,
                        'categoriesChildSelect' =>$categoriesChildSelect,
                        'booksChild'            =>$booksChild,
                    ]);
    }


    public function viewBook($bookId=0)
    {   
        $book= Book::getBookByBookId($bookId);
        $comments= Comment::getCommentByBookId($bookId);
        $dt     = Carbon::now();
        Carbon::setLocale('vi');
        foreach ($comments as $comment) {
            $comment->strTime=$dt->diffForHumans($comment->updated_at);
        }
        if ($book==null)
            return;
        return view('front-end.book',[
                        'categories'            =>$this->categories,
                        'categoriesChild'       =>$this->categoriesChild,
                        'companyChild'          =>$this->companyChild,
                        'authorsChild'          =>$this->authorsChild,
                        'book'                  =>$book,
                        'comments'              =>$comments,
                    ]);
    }


    public function viewAuthor($authorId=0)
    {
        $authorInfo= Author::find($authorId);
        if ($authorInfo!=null){
            $books= Book::getAllBookByAuthorId($authorId); 
            return view('front-end.author',[
                            'categories'            =>$this->categories,
                            'categoriesChild'       =>$this->categoriesChild,
                            'companyChild'          =>$this->companyChild,
                            'authorsChild'          =>$this->authorsChild,
                            'authorInfo'            =>$authorInfo,
                            'books'                 =>$books,
                        ]);
        }
        else{
            $authors= Author::getAllAuthor();
            return view('front-end.all-author',[
                            'categories'            =>$this->categories,
                            'categoriesChild'       =>$this->categoriesChild,
                            'companyChild'          =>$this->companyChild,
                            'authorsChild'          =>$this->authorsChild,
                            'authors'               =>$authors,
                        ]);
        }
    }
    public function viewCompany($companyId=0)
    {
        $companyInfo= Company::find($companyId);
        if ($companyInfo!=null){
            $books= Book::getAllBookByCompanyId($companyId); 
            return view('front-end.company',[
                            'categories'            =>$this->categories,
                            'categoriesChild'       =>$this->categoriesChild,
                            'companyChild'          =>$this->companyChild,
                            'authorsChild'          =>$this->authorsChild,
                            'companyInfo'           =>$companyInfo,
                            'books'                 =>$books,
                        ]);
        }
       
        else{
            $companies= Company::getAllCompany(); 
            // dd($companies);
            return view('front-end.all-company',[
                            'categories'            =>$this->categories,
                            'categoriesChild'       =>$this->categoriesChild,
                            'companyChild'          =>$this->companyChild,
                            'authorsChild'          =>$this->authorsChild,
                            'companies'             =>$companies,
                        ]);
        }
    }
    
}