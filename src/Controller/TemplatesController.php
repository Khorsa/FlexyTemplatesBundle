<?php

namespace flexycms\FlexyTemplatesBundle\Controller;

use flexycms\FlexyCacheBundle\Service\CacheService;
use flexycms\FlexyTemplatesBundle\Service\TemplatesService;
use flexycms\BreadcrumbsBundle\Utils\Breadcrumbs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use flexycms\FlexyAdminFrameBundle\Controller\AdminBaseController;

class TemplatesController extends AdminBaseController
{
    private $cacheService;
    private $templatesService;

    public function __construct(CacheService $cacheService, TemplatesService $templatesService)
    {
        $this->cacheService = $cacheService;
        $this->templatesService = $templatesService;
    }

    /**
     * @Route("/admin/templates", name="admin_templates")
     */
    public function list()
    {
        $forRender = parent::renderDefault();
        $forRender['title'] = "Шаблоны";

        $breadcrumbs = new Breadcrumbs();
        $breadcrumbs->prepend($this->generateUrl("admin_templates"), 'Шаблоны сайта');
        $breadcrumbs->prepend($this->generateUrl("admin_home"), 'Главная');
        $forRender['breadcrumbs'] = $breadcrumbs;

        $forRender['templates'] = $this->templatesService->getList();

        $forRender['ajax'] = $this->generateUrl("admin_templates_json");

        return $this->render('@FlexyTemplates/list.html.twig', $forRender);
    }


    /**
     * @Route("/admin/templates.json", name="admin_templates_json")
     */
    public function listJSON(Request $request)
    {
        $sort = $request->get("sort");


        $templates = $this->templatesService->getList();

        $draw = $request->get("draw");
        $recordsTotal = count($templates);
        $recordsFiltered = count($templates);

        $data = array();
        foreach($templates as $template)
        {
            $date = new \DateTime();
            $date->setTimestamp($template["mdate"]);

            $item = array();
            $item[] = '<a href="' . $this->generateUrl("admin_templates_edit", ['name' => $template['name']]) . '" class="btn btn-sm btn-primary"><i class="far fa-edit"></i></a>';
            $item[] = $template["name"];
            $item[] =$template["mode"];
            $item[] =$date->format("d.m.Y H:i:s");
            $item[] =$template["size"];

            $data[] = $item;
        }

        return $this->json([
            "data" => $data,
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,

        ]);
    }






    /**
     * @Route("/admin/templates/edit", name="admin_templates_edit")
     */
    public function edit(Request $request)
    {
        $name = $request->get("name");

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Редактирование «'.$name.'»';

        $breadcrumbs = new Breadcrumbs();
        $breadcrumbs->prepend($this->generateUrl("admin_templates"), 'Редактирование «'.$name.'»');
        $breadcrumbs->prepend($this->generateUrl("admin_templates"), 'Шаблоны сайта');
        $breadcrumbs->prepend($this->generateUrl("admin_home"), 'Главная');
        $forRender['breadcrumbs'] = $breadcrumbs;

        $template = $this->templatesService->getOne($name);

        if ($template === null)
        {
            $this->addFlash("danger", "Такого шаблона нет");
            return $this->redirectToRoute('admin_templates');
        }

        $forRender['template'] = $template;

        return $this->render('@FlexyTemplates/edit.html.twig', $forRender);
    }

    /**
     * @Route("/admin/templates/save", name="admin_templates_save")
     */
    public function save(Request $request )
    {
        $name = $request->get("name");
        $content = $request->get("content");

        //Сохраняем
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . $name, $content);

        //Чистим продакшн кэш
        $this->cacheService->clear();

        $this->addFlash("success", "Шаблон сохранён");

        if ($request->get("button") == 'apply') {
            return $this->redirectToRoute("admin_templates_edit", ['name' => $name]);
        }
        return $this->redirectToRoute('admin_templates');
    }
}