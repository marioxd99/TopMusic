<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Song;
use App\Form\SongType;
use App\Repository\SongRepository;
use Doctrine\Persistence\ManagerRegistry;
use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor;
use mysql_xdevapi\Exception;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class SongController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    #[Route('/show', name: 'show')]
    public function show(SongRepository $songRepository)
    {
        $songs = $songRepository->findAll();

        return $this->render('song/index.html.twig',[
            'songs' => $songs
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(Request $request, SluggerInterface $slugger)
    {
        $em = $this->doctrine->getManager();
        $song = new Song();

        $form = $this->createForm(SongType::class, $song);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $song = $form->getData();

            $song = $this->setImage($slugger, $form, $song);
            $em->persist($song);
            $em->flush();
            $this->addFlash('success', 'Song created successfully!');
            return $this->redirectToRoute('show');
        }

        return $this->render('song/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('update/{id}', name: 'update')]
    public function update(Request $request, $id, SluggerInterface $slugger)
    {
        $song = $this->doctrine->getRepository(Song::class)->find($id);
        $form = $this->createForm(SongType::class, $song);
        $form ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->doctrine->getManager();

            $song = $this->setImage($slugger, $form, $song);
            $em->persist($song);
            $em->flush();

            $this->addFlash('success', 'Song updated!');
            return $this->redirectToRoute('show');
        }

        return $this->render('song/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function setImage(SluggerInterface $slugger, $form, $song){
        $image = $form->get('image')->getData();
        $originalFileName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFileName = $slugger->slug($originalFileName);
        $newFileName = $safeFileName.'-'. uniqid().'.'.$image->guessExtension();

        $image->move(
            $this->getParameter('image_dir'),
            $newFileName
        );
        $song->setImage($newFileName);

        return $song;
    }

    #[Route('delete', name:'Delete', options: ['expose' => true])]
    public function delete(Request $request)
    {
        $id = $request->request->get('id');
        $song = $this->doctrine->getRepository(Song::class)->find($id);
        $em = $this->doctrine->getManager();
        $em->remove($song);
        $em->flush();

        return new Response('Done');
    }

    #[Route('search/{id}', name:'search')]
    public function search($id)
    {
        $song = $this->doctrine->getRepository(Song::class)->find($id);

        return new Response($song);
    }

    #[Route('showSong/{id}', name:'showSong')]
    public function showSong($id, Request $request)
    {
        $song = $this->doctrine->getRepository(Song::class)->find($id);
        $comments = $this->doctrine->getRepository(Comment::class)->findBySongId($song->getId());

        return $this->render('song/show.html.twig', [
            'song' => $song, 'comments' => $comments
        ]);
    }

    /**
     * @Route("likes", name="Likes", options={"expose"=true})
     */
    public function Likes(Request $request)
    {
        if($request->isXmlHttpRequest()){
            $em = $this->doctrine->getManager();
            $id = $request->request->get('id');
            $song = $em->getRepository(Song::class)->find($id);
            $likes = $song->getLikes() + 1;
            $song->setLikes($likes);

            $user = $this->getUser();
            $userLikes = $song->getLikeUser();
            $userLikes .= $user->getId().',';
            $song->setLikeUser($userLikes);
            $em->flush();

            return new JsonResponse(['likes'=>$likes]);
        }else{
            throw new Exception('Es un hack?');
        }
    }

    #[Route('searchSong', name: 'searchSong', options: ['expose' => true])]
    public function searchSong(Request $request)
    {
        if($request->isXmlHttpRequest()){
            $allSongs = $this->doctrine->getRepository(Song::class)->findAll();
            $idy = 0;
            foreach ($allSongs as $allSong) {
                $temp = array(
                    'id' => $allSong->getId()
                );
                $allSongs[$idy++] = $temp;
            }
            $em = $this->doctrine->getManager();

            $json = array();
            $title = $request->query->get('title');
            $songs = $em->getRepository(Song::class)->createQueryBuilder('s')
                ->where('s.title LIKE :title')
                ->setParameter('title', '%'.$title.'%')
                ->getQuery()->getResult();

            $idx = 0;
            foreach ($songs as $song){
                $temp = array(
                    'id'=> $song->getId()
                );
                $json[$idx++] = $temp;
            }

            return new JsonResponse(['song' => $json, 'allSongs' => $allSongs]);
        }
    }

    #[Route('commentSong', name: 'commentSong', options: ['expose' => true])]
    public function commentSong(Request $request)
    {
        $em = $this->doctrine->getManager();
        if($request->isXmlHttpRequest()){
            $comment = new Comment();
            $songId = $request->query->get('id');
            $content = $request->query->get('content');
            $song = $em->getRepository(Song::class)->find($songId);

            $user = $this->getUser()->getUserIdentifier();
            $comment->setAuthorName($user);
            $comment->setContent($content);
            $comment->setSong($song);
            $em->persist($comment);
            $em->flush();

            $comments = $em->getRepository(Comment::class)->findAll();

            return new JsonResponse(['commentId' => $comment->getId(),
                'commentContent' => $comment->getContent(),
                'commentAuthor'=>$comment->getAuthorName(),'commentsLength'=> count($comments)]);
        }
    }
}
